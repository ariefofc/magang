# MS Harmony - Python AI Recolor Service

## Setup (Opsional — diperlukan hanya untuk kualitas lebih tinggi)

Install dependencies:
```bash
pip install flask flask-cors pillow numpy
```

Jalankan service:
```bash
python python/recolor_service.py
```

Service berjalan di: http://localhost:5001

## Cara Kerja

- Jika Python service berjalan → digunakan sebagai primary engine
- Jika tidak berjalan → sistem otomatis fallback ke Canvas engine (client-side)

## Endpoint

POST http://localhost:5001/recolor

Body (JSON):
```json
{
  "image_path": "C:/path/to/public/Kebaya.png",
  "color_prompt": "Sage Green pastel",
  "mask_path": null
}
```

Response:
```json
{
  "success": true,
  "image": "data:image/jpeg;base64,..."
}
```

## mask_path (Opsional)

Untuk hasil lebih presisi, sediakan file mask PNG:
- Putih = area kebaya (yang akan diwarnai)
- Hitam/Transparan = background & wajah (dilindungi)

Nama konvensi: `Kebaya-Model-mask.png` di sebelah file aslinya.
