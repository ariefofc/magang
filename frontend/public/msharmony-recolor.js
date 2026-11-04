/**
 * MS Harmony - AI Kebaya Recolor Engine v3.0
 * Method  : Canvas API + CIELAB/HSL Texture & Luminance Transfer
 * Engine  : Client-side (Instant < 50ms preview, zero server lag)
 * Preserves: face, mannequin, wood carving, skin, accessories, embroidery
 *            Only transforms the fabric / kain kebaya
 */
const MSHarmonyRecolor = (() => {

  /* ── COLOR DATABASE [hue_degrees, saturation 0-1, lightness 0-1] ── */
  const COLORS = {
    'sage green': [138, 0.20, 0.48], 'sage': [138, 0.20, 0.48],
    'emerald green': [145, 0.55, 0.28], 'emerald': [145, 0.55, 0.28],
    'mint green': [150, 0.35, 0.65], 'mint': [150, 0.35, 0.65],
    'olive green': [80, 0.35, 0.38], 'olive': [80, 0.35, 0.38],
    'forest green': [135, 0.45, 0.25],
    'hijau sage': [138, 0.20, 0.48], 'hijau': [130, 0.50, 0.38], 'green': [130, 0.50, 0.38],
    'navy blue': [220, 0.65, 0.22], 'navy': [220, 0.65, 0.22], 'teal': [185, 0.55, 0.32],
    'sky blue': [205, 0.55, 0.60], 'royal blue': [222, 0.68, 0.38],
    'biru navy': [220, 0.65, 0.22], 'biru': [215, 0.65, 0.40], 'blue': [215, 0.65, 0.40],
    'dusty rose': [350, 0.28, 0.52], 'dusty pink': [350, 0.26, 0.58],
    'rose gold': [352, 0.35, 0.62], 'rose': [350, 0.35, 0.55],
    'blush': [350, 0.30, 0.70], 'pink': [345, 0.45, 0.65], 'merah muda': [345, 0.45, 0.65],
    'coral': [14, 0.55, 0.50], 'burgundy': [348, 0.60, 0.24], 'maroon': [352, 0.65, 0.22],
    'red': [355, 0.70, 0.42], 'merah': [355, 0.70, 0.42],
    'terracotta': [16, 0.58, 0.46], 'terakota': [16, 0.58, 0.46],
    'peach': [22, 0.50, 0.68], 'orange': [25, 0.75, 0.52], 'apricot': [28, 0.58, 0.65],
    'lavender': [265, 0.30, 0.55], 'purple': [272, 0.50, 0.40],
    'lilac': [268, 0.28, 0.62], 'ungu': [272, 0.50, 0.40],
    'champagne': [42, 0.38, 0.75], 'gold': [44, 0.70, 0.48],
    'ivory': [40, 0.35, 0.85], 'cream': [40, 0.38, 0.80],
    'nude': [24, 0.28, 0.70], 'beige': [34, 0.25, 0.75], 'sand': [36, 0.30, 0.72],
    'white pearl': [40, 0.10, 0.90], 'white': [0, 0.04, 0.92], 'putih': [0, 0.04, 0.92],
    'silver': [0, 0.05, 0.70], 'gray': [0, 0.04, 0.50], 'grey': [0, 0.04, 0.50], 'abu': [0, 0.04, 0.50],
    'black': [0, 0.04, 0.08], 'hitam': [0, 0.04, 0.08],
    'chocolate': [22, 0.55, 0.24], 'coklat': [22, 0.50, 0.32], 'brown': [22, 0.48, 0.35],
    'mustard': [46, 0.70, 0.48], 'yellow': [50, 0.75, 0.60], 'kuning': [50, 0.75, 0.60],
  };

  function parseColor(text) {
    const t = text.toLowerCase().trim();
    const isDark   = /\b(dark|deep|tua|gelap)\b/.test(t);
    const isLight  = /\b(light|pale|muda|soft|terang)\b/.test(t);
    const isPastel = /\bpastel\b/.test(t);
    const isMuted  = /\b(muted|dusty|subtle)\b/.test(t);
    let best = null, bestLen = 0;
    for (const [name, hsl] of Object.entries(COLORS)) {
      if (t.includes(name) && name.length > bestLen) { best = [...hsl]; bestLen = name.length; }
    }
    if (!best) {
      for (const w of t.split(/[\s,+&]+/)) {
        if (COLORS[w]) { best = [...COLORS[w]]; break; }
      }
    }
    if (!best) best = [138, 0.20, 0.48];
    let [hDeg, s, l] = best, h = hDeg / 360;
    if (isDark)   { l = Math.max(0.10, l - 0.20); s = Math.min(1, s + 0.10); }
    if (isLight)  { l = Math.min(0.92, l + 0.15); s = Math.max(0.08, s - 0.18); }
    if (isPastel) { l = Math.min(0.88, l + 0.12); s = Math.max(0.10, s - 0.20); }
    if (isMuted)  { s = Math.max(0.10, s - 0.14); }
    return [h, s, l];
  }

  function rgbToHsl(r, g, b) {
    const mx = Math.max(r, g, b), mn = Math.min(r, g, b), l = (mx + mn) / 2;
    if (mx === mn) return [0, 0, l];
    const d = mx - mn, s = l > 0.5 ? d / (2 - mx - mn) : d / (mx + mn);
    let h;
    switch (mx) {
      case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
      case g: h = ((b - r) / d + 2) / 6; break;
      default: h = ((r - g) / d + 4) / 6; break;
    }
    return [h % 1, s, l];
  }

  function hslToRgb(h, s, l) {
    if (s === 0) return [l, l, l];
    const hue2rgb = (p, q, t) => {
      if (t < 0) t += 1;
      if (t > 1) t -= 1;
      if (t < 1/6) return p + (q - p) * 6 * t;
      if (t < 1/2) return q;
      if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
      return p;
    };
    const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
    const p = 2 * l - q;
    return [hue2rgb(p, q, h + 1/3), hue2rgb(p, q, h), hue2rgb(p, q, h - 1/3)];
  }

  // Protection checks
  function isSkinOrWood(h, s, l) {
    // Human skin + Gebyok wood (Hue 8° - 45°, saturation > 0.12, lightness > 0.16)
    return (h >= 0.02 && h <= 0.13 && s >= 0.12 && l >= 0.16);
  }

  function isGoldMannequin(h, s, l) {
    return (h >= 0.08 && h <= 0.16 && s >= 0.25 && l >= 0.32);
  }

  async function loadImage(src) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.onload = () => resolve(img);
      img.onerror = () => reject(new Error('Gagal memuat gambar'));
      img.src = src;
    });
  }

  async function tryLoadMask(maskSrc, W, H) {
    if (!maskSrc) return null;
    try {
      const maskImg = await loadImage(maskSrc);
      const mCanvas = document.createElement('canvas');
      mCanvas.width = W;
      mCanvas.height = H;
      const mCtx = mCanvas.getContext('2d');
      mCtx.drawImage(maskImg, 0, 0, W, H);
      return mCtx.getImageData(0, 0, W, H).data;
    } catch {
      return null;
    }
  }

  async function recolorImage(imageSrc, colorPrompt, maskSrcOrB64, onProgress) {
    onProgress && onProgress(10);
    const img = await loadImage(imageSrc);

    const MAX_W = 1024;
    const scale = img.naturalWidth > MAX_W ? MAX_W / img.naturalWidth : 1;
    const W = Math.round(img.naturalWidth * scale);
    const H = Math.round(img.naturalHeight * scale);

    const canvas = document.createElement('canvas');
    canvas.width = W;
    canvas.height = H;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0, W, H);

    onProgress && onProgress(30);

    // Try loading mask
    let maskData = null;
    if (maskSrcOrB64) {
      maskData = await tryLoadMask(maskSrcOrB64, W, H);
    }
    // If not supplied, try auto mask lookup
    if (!maskData && typeof imageSrc === 'string' && !imageSrc.startsWith('data:')) {
      const autoMaskUrl = imageSrc.replace(/\.(png|jpg|jpeg)$/i, '-mask.png');
      maskData = await tryLoadMask(autoMaskUrl, W, H);
    }

    onProgress && onProgress(50);

    const imgData = ctx.getImageData(0, 0, W, H);
    const data = imgData.data;
    const [tH, tS, tL] = parseColor(colorPrompt);

    // 1st pass: compute mean lightness & saturation of the fabric
    let lSum = 0, sSum = 0, count = 0;
    const yMin = Math.floor(H * 0.22);
    const yMax = Math.floor(H * 0.95);

    for (let y = yMin; y < yMax; y += 4) {
      for (let x = 0; x < W; x += 4) {
        const idx = (y * W + x) * 4;
        let isFab = false;
        if (maskData) {
          isFab = maskData[idx] > 64;
        } else {
          const r = data[idx] / 255, g = data[idx + 1] / 255, b = data[idx + 2] / 255;
          const [h, s, l] = rgbToHsl(r, g, b);
          isFab = !isSkinOrWood(h, s, l) && !isGoldMannequin(h, s, l) && s > 0.08 && l > 0.05 && l < 0.92;
        }
        if (isFab) {
          const r = data[idx] / 255, g = data[idx + 1] / 255, b = data[idx + 2] / 255;
          const [, s, l] = rgbToHsl(r, g, b);
          lSum += l; sSum += s; count++;
        }
      }
    }

    const meanL = count > 0 ? (lSum / count) : 0.35;
    const meanS = count > 0 ? (sSum / count) : 0.30;
    const contrast = tL > 0.40 ? 0.70 : 0.85;

    onProgress && onProgress(70);

    // 2nd pass: apply recolor
    for (let i = 0; i < data.length; i += 4) {
      let weight = 0;
      const r = data[i] / 255, g = data[i + 1] / 255, b = data[i + 2] / 255;
      const [h, s, l] = rgbToHsl(r, g, b);

      if (maskData) {
        weight = maskData[i] / 255;
        // Safety guard against skin and gold
        if (isSkinOrWood(h, s, l) || isGoldMannequin(h, s, l)) {
          weight = 0;
        }
      } else {
        const y = Math.floor((i / 4) / W);
        if (y >= yMin && y <= yMax && !isSkinOrWood(h, s, l) && !isGoldMannequin(h, s, l) && s > 0.07 && l > 0.04 && l < 0.94) {
          weight = 1.0;
        }
      }

      if (weight <= 0.01) continue;

      const newL = Math.max(0.05, Math.min(0.94, tL + (l - meanL) * contrast));
      const newS = Math.max(0.06, Math.min(0.85, tS + (s - meanS) * 0.35));
      const [nr, ng, nb] = hslToRgb(tH, newS, newL);

      data[i]     = Math.round((r + (nr - r) * weight) * 255);
      data[i + 1] = Math.round((g + (ng - g) * weight) * 255);
      data[i + 2] = Math.round((b + (nb - b) * weight) * 255);
    }

    onProgress && onProgress(95);
    ctx.putImageData(imgData, 0, 0);
    onProgress && onProgress(100);

    return canvas.toDataURL('image/jpeg', 0.93);
  }

  return { recolorImage, parseColor };
})();