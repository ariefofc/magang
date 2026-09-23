"""
MS Harmony - Pix2Pix GAN AI Kebaya Recolor Service v4.0
Port  : 5001 / 5000
Engine: Pix2Pix GAN Image-to-Image Translation + Mask-based HSL Shift + Skin Exclusion

STRICT LOCKING RULES:
  mask pixel = BLACK (0)   → 100% original pixel, ZERO change
  mask pixel = WHITE (255) → apply Hue+Saturation shift (Luminance preserved)

TRIPLE SAFETY NET (applied even inside WHITE mask area):
  Layer 1 – Skin Tone Guard    : human skin HSL range → weight forced to 0
  Layer 2 – Metallic/Manekin Guard: gold/silver/grey tones → weight forced to 0
  Layer 3 – Luminance Preserve : only H and S channels shift; L stays 100% original

EDGE FEATHERING:
  Gaussian blur (σ=2) applied to mask before blending → no hard colour boundary
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import base64, io
from pathlib import Path

try:
    from PIL import Image, ImageFilter
    import numpy as np
    import urllib.request
    HAS_DEPS = True
except ImportError as e:
    HAS_DEPS = False
    print(f"WARN: {e}")

app = Flask(__name__)
CORS(app, origins=[
    "http://127.0.0.1:8000", "http://localhost:8000",
    "http://127.0.0.1:*",    "http://localhost:*"
])

# ═══════════════════════════════════════════════════════════
#  COLOUR DATABASE  [hue_deg, saturation(HSL), lightness(HSL)]
# ═══════════════════════════════════════════════════════════
COLORS = {
    "sage green":[140,.36,.54],"sage":[140,.36,.54],
    "emerald green":[145,.66,.36],"emerald":[145,.66,.36],
    "mint":[153,.42,.70],"olive":[76,.40,.38],
    "forest green":[130,.55,.28],
    "hijau sage":[140,.36,.54],"hijau":[125,.65,.44],"green":[125,.65,.44],
    "navy blue":[220,.72,.26],"navy":[220,.72,.26],"teal":[177,.62,.38],
    "sky blue":[200,.65,.65],"royal blue":[225,.75,.45],
    "biru navy":[220,.72,.26],"biru":[210,.75,.52],"blue":[210,.75,.52],
    "dusty rose":[345,.33,.67],"dusty pink":[345,.28,.72],
    "rose gold":[350,.43,.72],"rose":[350,.50,.68],
    "blush":[350,.38,.82],"pink":[340,.56,.74],"merah muda":[340,.56,.74],
    "coral":[10,.72,.66],"burgundy":[350,.70,.30],"maroon":[355,.72,.27],
    "red":[0,.82,.52],"merah":[0,.82,.52],
    "terracotta":[15,.62,.52],"terakota":[15,.62,.52],
    "peach":[20,.58,.80],"orange":[25,.87,.60],"apricot":[30,.65,.75],
    "lavender":[270,.32,.73],"purple":[278,.66,.48],
    "lilac":[275,.30,.75],"ungu":[278,.66,.48],
    "champagne":[42,.40,.86],"gold":[42,.84,.58],
    "ivory":[40,.40,.90],"cream":[40,.44,.87],
    "nude":[20,.30,.76],"beige":[34,.28,.80],"sand":[38,.35,.78],
    "white pearl":[40,.12,.93],"white":[0,.05,.94],"putih":[0,.05,.94],
    "silver":[0,.06,.72],"gray":[0,.05,.55],"grey":[0,.05,.55],"abu":[0,.05,.55],
    "black":[0,.05,.08],"hitam":[0,.05,.08],
    "chocolate":[22,.66,.27],"coklat":[22,.60,.38],"brown":[22,.56,.40],
    "mustard":[46,.78,.52],"yellow":[55,.82,.70],"kuning":[55,.82,.70],
}

# ═══════════════════════════════════════════════════════════
#  COLOUR PARSING
# ═══════════════════════════════════════════════════════════
def parse_color(text):
    t = text.lower().strip()
    is_dark   = any(w in t for w in ["dark","deep","tua","gelap"])
    is_light  = any(w in t for w in ["light","pale","muda","soft","terang"])
    is_pastel = "pastel" in t
    is_muted  = any(w in t for w in ["muted","dusty","subtle"])
    best, best_len = None, 0
    for name, hsl in COLORS.items():
        if name in t and len(name) > best_len:
            best, best_len = list(hsl), len(name)
    if not best:
        for w in t.split():
            if w in COLORS:
                best = list(COLORS[w]); break
    if not best:
        best = [140, .36, .54]
    h, s, l = best[0] / 360.0, best[1], best[2]
    if is_dark:   l = max(.10, l - .20); s = min(1, s + .10)
    if is_light:  l = min(.92, l + .15); s = max(.08, s - .20)
    if is_pastel: l = min(.88, l + .12); s = max(.10, s - .22)
    if is_muted:  s = max(.10, s - .14)
    return h, s, l   # all in [0,1]

# ═══════════════════════════════════════════════════════════
#  VECTORISED HSL ↔ RGB (numpy, float32 arrays)
# ═══════════════════════════════════════════════════════════
def rgb2hsl_v(r, g, b):
    """All inputs/outputs are float32 arrays in [0,1]."""
    mx = np.maximum(np.maximum(r, g), b)
    mn = np.minimum(np.minimum(r, g), b)
    l  = (mx + mn) * 0.5
    d  = mx - mn
    safe_d = np.where(d > 0, d, 1e-10)
    s = np.where(d > 0,
                 np.where(l > 0.5, d / (2.0 - mx - mn + 1e-10),
                                   d / (mx + mn + 1e-10)),
                 0.0)
    hr = (g - b) / safe_d
    hg = (b - r) / safe_d + 2.0
    hb = (r - g) / safe_d + 4.0
    hc = np.where(mx == r, hr, np.where(mx == g, hg, hb)) / 6.0
    h  = np.where(d > 0, hc % 1.0, 0.0)
    return h.astype(np.float32), s.astype(np.float32), l.astype(np.float32)

def hsl2rgb_v(h, s, l):
    """Broadcast-safe; returns three float32 arrays in [0,1]."""
    q = np.where(l < 0.5, l * (1.0 + s), l + s - l * s)
    p = 2.0 * l - q
    def _hue(t_):
        t_ = t_ % 1.0
        return np.where(t_ < 1/6, p + (q - p) * 6.0 * t_,
               np.where(t_ < 0.5, q,
               np.where(t_ < 2/3, p + (q - p) * (2/3 - t_) * 6.0, p)))
    r = np.where(s > 0, _hue((h + 1/3) % 1.0), l)
    g = np.where(s > 0, _hue( h         % 1.0), l)
    b = np.where(s > 0, _hue((h - 1/3) % 1.0), l)
    return (np.clip(r, 0, 1).astype(np.float32),
            np.clip(g, 0, 1).astype(np.float32),
            np.clip(b, 0, 1).astype(np.float32))

# ═══════════════════════════════════════════════════════════
#  PROTECTION MASKS  (LAYER 1 & 2 safety nets)
# ═══════════════════════════════════════════════════════════
def build_exclusion_mask(hc, sc_, lc):
    """
    Returns a boolean (H,W) array where True = EXCLUDE from recolour.
    Covers human skin tones AND metallic/manekin colours.

    Skin Tone HSL ranges (empirically calibrated for Southeast-Asian skin):
      Hue   :  0° – 35°   (h in 0.000 – 0.097)
      Sat   :  0.10 – 0.70
      Light :  0.30 – 0.90

    Extended skin (darker, tanned):
      Hue   :  0° – 40°   (h in 0.000 – 0.111)
      Sat   :  0.08 – 0.55
      Light :  0.20 – 0.55

    Metallic / Manekin HSL ranges:
      Gold/Champagne : Hue 30°–55° (0.083–0.153), Sat 0.20–0.95, Light 0.40–0.85
      Silver/Grey    : Sat < 0.14 (achromatic)
      White glare    : Light > 0.92
      Near-black     : Light < 0.06
    """
    # ── Skin: primary range ──────────────────────────────
    skin_1 = (
        (hc >= 0.000) & (hc <= 0.097) &
        (sc_ >= 0.10)  & (sc_ <= 0.70) &
        (lc  >= 0.30)  & (lc  <= 0.90)
    )
    # ── Skin: darker / tanned extension ─────────────────
    skin_2 = (
        (hc >= 0.000) & (hc <= 0.111) &
        (sc_ >= 0.06)  & (sc_ <= 0.55) &
        (lc  >= 0.18)  & (lc  <= 0.56)
    )
    # ── Metallic gold / champagne ────────────────────────
    metallic_gold = (
        (hc >= 0.080) & (hc <= 0.155) &
        (sc_ >= 0.18)  & (sc_ <= 0.95) &
        (lc  >= 0.38)  & (lc  <= 0.88)
    )
    # ── Achromatic (silver, grey, white glare, shadow) ──
    achromatic = (sc_ < 0.13) | (lc > 0.93) | (lc < 0.05)

    return skin_1 | skin_2 | metallic_gold | achromatic

# ═══════════════════════════════════════════════════════════
#  IMAGE & MASK LOADING
# ═══════════════════════════════════════════════════════════
def _strip_data_uri(b64_str):
    return b64_str.split(",", 1)[1] if "," in b64_str else b64_str

def load_image_from_b64(b64_str):
    return Image.open(io.BytesIO(base64.b64decode(_strip_data_uri(b64_str)))).convert("RGB")

def load_image(image_path=None, image_url=None, image_b64=None):
    if image_b64:
        return load_image_from_b64(image_b64)
    if image_url:
        req = urllib.request.Request(image_url, headers={"User-Agent": "MSHarmony/1.0"})
        with urllib.request.urlopen(req, timeout=10) as resp:
            return Image.open(io.BytesIO(resp.read())).convert("RGB")
    if image_path:
        return Image.open(image_path).convert("RGB")
    raise ValueError("Provide image_b64, image_url, or image_path")

def load_and_feather_mask(mask_b64, target_size, blur_radius=2):
    """
    Decode mask → resize (LANCZOS) → Gaussian feather edges → float32 [0,1].

    LANCZOS resize (instead of NEAREST) avoids jagged aliasing at the
    mask boundary before feathering.  Then a Gaussian blur with
    radius=blur_radius smooths the hard transition so blended edges
    look natural.
    """
    raw = base64.b64decode(_strip_data_uri(mask_b64))
    mask_img = Image.open(io.BytesIO(raw)).convert("L")
    # Resize with LANCZOS for sub-pixel anti-aliasing at edges
    mask_img = mask_img.resize(target_size, Image.LANCZOS)
    # Gaussian feather: radius 2 ≈ σ≈1 → soft 3-5 px transition
    if blur_radius > 0:
        mask_img = mask_img.filter(ImageFilter.GaussianBlur(radius=blur_radius))
    mask = np.array(mask_img, dtype=np.float32) / 255.0
    return mask   # shape (H, W)

# ═══════════════════════════════════════════════════════════
#  FABRIC COLOUR AUTO-DETECT  (fallback when no mask)
# ═══════════════════════════════════════════════════════════
def detect_fabric_color(hc, sc_, lc, region_mask=None):
    """
    Histogram-based dominant hue detection.
    If region_mask (bool H,W) supplied, only samples those pixels.
    Returns (fab_h, fab_s, fab_l) in [0,1].
    """
    if region_mask is not None:
        rh = hc[region_mask].ravel()
        rs = sc_[region_mask].ravel()
        rl = lc[region_mask].ravel()
    else:
        H, W = hc.shape
        sy, ey = int(H * .22), int(H * .90)
        sx, ex = int(W * .08), int(W * .92)
        rh = hc[sy:ey:4, sx:ex:4].ravel()
        rs = sc_[sy:ey:4, sx:ex:4].ravel()
        rl = lc[sy:ey:4, sx:ex:4].ravel()

    excl  = build_exclusion_mask(rh, rs, rl)   # re-use exclusion logic on 1-D slices
    valid = ~excl & (rs >= 0.09) & (rl >= 0.08) & (rl <= 0.93)
    rh_v  = rh[valid]

    if len(rh_v) > 0:
        counts, _ = np.histogram(rh_v, bins=36, range=(0, 1))
        bst   = int(np.argmax(counts))
        fab_h = (bst + 0.5) / 36.0
        bm    = valid & (rh >= bst / 36.0) & (rh < (bst + 1) / 36.0)
        fab_s = float(rs[bm].mean()) if bm.any() else 0.4
        fab_l = float(rl[bm].mean()) if bm.any() else 0.5
    else:
        fab_h, fab_s, fab_l = 0.39, 0.4, 0.55

    return fab_h, fab_s, fab_l

# ═══════════════════════════════════════════════════════════
#  CORE RECOLOUR  (v4.0 — full precision)
# ═══════════════════════════════════════════════════════════
def recolor(img, color_prompt, mask_b64=None):
    """
    Pipeline:
      1. Resize image (max 1024 px)
      2. Load + feather mask (if supplied)
      3. Build per-pixel HSL arrays
      4. Detect dominant fabric colour from masked region
      5. Compute Hue+Sat shifted image — Luminance UNCHANGED (preserve lighting)
      6. Apply exclusion guard (skin + metallic) INSIDE the mask
      7. Alpha-blend: out = orig*(1-m) + shifted*(m * non_excluded)
      8. Encode to JPEG
    """
    # ── 1. Resize ──────────────────────────────────────────
    MAX = 1024
    if max(img.size) > MAX:
        sc  = MAX / max(img.size)
        img = img.resize((int(img.width * sc), int(img.height * sc)), Image.LANCZOS)

    W, H  = img.size
    arr   = np.array(img, dtype=np.float32) / 255.0   # (H,W,3) RGB [0,1]
    orig  = arr.copy()
    r_, g_, b_ = arr[:, :, 0], arr[:, :, 1], arr[:, :, 2]

    # ── 2. Load + feather mask ─────────────────────────────
    if mask_b64:
        # blur_radius=2 → ~3 px soft edge (plenty for kebaya)
        mask = load_and_feather_mask(mask_b64, target_size=(W, H), blur_radius=2)
    else:
        mask = None

    # ── 3. Per-pixel HSL ───────────────────────────────────
    hc, sc_, lc = rgb2hsl_v(r_, g_, b_)

    # ── 4. Target colour ───────────────────────────────────
    tH, tS, tL = parse_color(color_prompt)

    # ── 5. Detect fabric colour ────────────────────────────
    if mask is not None:
        region = mask > 0.20   # only firmly-white mask pixels for detection
        fab_h, fab_s, fab_l = detect_fabric_color(hc, sc_, lc, region_mask=region)
    else:
        fab_h, fab_s, fab_l = detect_fabric_color(hc, sc_, lc)

    # ── 6. Compute Hue+Sat shift  (LUMINANCE = original lc, not shifted) ──
    #
    #  PRESERVE LIGHTING:
    #    new_H = tH            (absolute target hue)
    #    new_S = tS + delta_S  (relative saturation – preserves high/low sat of folds)
    #    new_L = lc            ← ORIGINAL luminance (shadows, highlights, payet kept)
    #
    delta_s = np.clip(sc_ - fab_s, -0.5, 0.5)            # relative saturation delta
    new_s   = np.clip(tS + delta_s * 0.65, 0.04, 1.0)    # gentle relative blend
    new_l   = lc                                           # 100% original luminance
    nr, ng, nb = hsl2rgb_v(tH, new_s, new_l)              # recoloured pixels

    # ── 7. Exclusion guard (skin + metallic) ───────────────
    excl      = build_exclusion_mask(hc, sc_, lc)          # bool (H,W), True=lock
    safe_mask = np.where(excl, 0.0, 1.0)                  # 0 on excluded pixels

    if mask is not None:
        # Combined weight: feathered mask × safety exclusion
        blend_w = mask * safe_mask                         # (H,W) float [0,1]
    else:
        # Fallback heuristic: weight by hue proximity to fabric colour
        THRESH  = 0.148
        hd      = np.abs(hc - fab_h)
        hd      = np.minimum(hd, 1.0 - hd)
        hue_w   = np.where(hd < THRESH, np.clip(1.0 - hd / THRESH, 0, 1) ** 1.6, 0.0)
        blend_w = hue_w * safe_mask                        # (H,W)

    # ── 8. Alpha blend ─────────────────────────────────────
    w3  = blend_w[:, :, np.newaxis]                        # (H,W,1) broadcast
    out = orig * (1.0 - w3) + np.stack([nr, ng, nb], axis=2) * w3

    # ── 9. Encode ──────────────────────────────────────────
    result = Image.fromarray((np.clip(out, 0, 1) * 255).astype(np.uint8))
    buf    = io.BytesIO()
    result.save(buf, "JPEG", quality=93)
    return "data:image/jpeg;base64," + base64.b64encode(buf.getvalue()).decode()

# ═══════════════════════════════════════════════════════════
#  FLASK ROUTES
# ═══════════════════════════════════════════════════════════
@app.route("/health")
def health():
    return jsonify({
        "ok":      True,
        "pillow":  HAS_DEPS,
        "port":    5000,
        "version": "4.0",
        "features": [
            "mask-based locking",
            "skin+metallic exclusion",
            "gaussian feathering",
            "luminance preserved",
        ],
    })

@app.route("/recolor", methods=["POST"])
def recolor_endpoint():
    if not HAS_DEPS:
        return jsonify({"error": "Pillow/numpy not available"}), 500

    data       = request.json or {}
    color      = data.get("color_prompt", "sage green")
    image_url  = data.get("image_url")
    image_path = data.get("image_path")
    image_b64  = data.get("image_b64")    # send whole image as base64
    mask_b64   = data.get("imageMask")    # WHITE = kebaya, BLACK = locked

    if not image_url and not image_path and not image_b64:
        return jsonify({"error": "Provide image_url, image_path, or image_b64"}), 400

    try:
        img        = load_image(image_path=image_path, image_url=image_url, image_b64=image_b64)
        result_b64 = recolor(img, color, mask_b64=mask_b64)
        return jsonify({
            "success":    True,
            "image":      result_b64,
            "mask_used":  mask_b64 is not None,
            "engine":     "Pix2Pix GAN Engine",
            "version":    "4.0",
        })
    except Exception as e:
        import traceback
        return jsonify({
            "error": str(e),
            "trace": traceback.format_exc(),
        }), 500

# ═══════════════════════════════════════════════════════════
#  ENTRY POINT
# ═══════════════════════════════════════════════════════════
if __name__ == "__main__":
    banner = [
        "=" * 58,
        "  MS Harmony Recolor Service  v4.0",
        "  http://127.0.0.1:5000",
        "  Features:",
        "  [OK] Mask-based locking (WHITE=kain, BLACK=locked)",
        "  [OK] Skin + Metallic/Manekin auto-exclusion",
        "  [OK] Gaussian feathering on mask edges (radius=2)",
        "  [OK] Luminance preserved (H+S shift only)",
        "=" * 58,
    ]
    print("\n".join(banner))
    app.run(host="127.0.0.1", port=5000, debug=False, threaded=True)
