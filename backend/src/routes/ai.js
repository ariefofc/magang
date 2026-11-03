const express = require('express');
const router = express.Router();
const axios = require('axios');

// POST /api/ai/recolor
router.post('/recolor', async (req, res) => {
  const { image_b64, image_url, color, mask_b64 } = req.body;

  // Tier 1: Python Flask microservice
  try {
    const pyEndpoint = process.env.PIX2PIX_AI_ENDPOINT || 'http://127.0.0.1:5001/recolor';
    const pyRes = await axios.post(pyEndpoint, {
      image_b64, image_url, color, mask_b64,
    }, { timeout: 30000 });

    if (pyRes.data && pyRes.data.result_b64) {
      return res.json({
        success: true,
        result_b64: pyRes.data.result_b64,
        engine: 'pix2pix_python',
      });
    }
  } catch (e) {
    console.log('[AI Recolor] Python service unavailable:', e.message);
  }

  // Tier 2: Google Gemini API
  try {
    const apiKey = process.env.GOOGLE_AI_API_KEY;
    if (apiKey) {
      const model = process.env.GOOGLE_AI_IMAGE_MODEL || 'gemini-2.0-flash-preview-image-generation';
      const endpoint = process.env.GOOGLE_AI_ENDPOINT || 'https://generativelanguage.googleapis.com/v1beta';
      const url = `${endpoint}/models/${model}:generateContent`;

      const parts = [
        {
          text: `Ubah warna pakaian/kebaya pada gambar ini menjadi ${color}. ` +
            'Pertahankan bentuk, tekstur kain, bordir, payet, pose model, dan latar belakang tetap sama persis. ' +
            'Hanya warna kain kebaya yang berubah.',
        },
      ];

      if (image_b64) {
        const cleanB64 = image_b64.includes(',') ? image_b64.split(',')[1] : image_b64;
        parts.push({
          inline_data: { mime_type: 'image/jpeg', data: cleanB64 },
        });
      }

      const gRes = await axios.post(url, {
        contents: [{ parts }],
        generationConfig: { responseModalities: ['TEXT', 'IMAGE'] },
      }, {
        headers: { 'x-goog-api-key': apiKey, 'Content-Type': 'application/json' },
        timeout: 90000,
      });

      const respParts = gRes.data?.candidates?.[0]?.content?.parts || [];
      for (const part of respParts) {
        const imgData = part.inline_data?.data || part.inlineData?.data;
        if (imgData) {
          return res.json({
            success: true,
            result_b64: `data:image/png;base64,${imgData}`,
            engine: 'google_gemini',
          });
        }
      }
    }
  } catch (e) {
    console.log('[AI Recolor] Google AI unavailable:', e.message);
  }

  // Tier 3: fallback to client-side
  res.json({
    success: false,
    message: 'Server AI tidak tersedia. Gunakan Canvas AI di browser.',
    engine: 'client_fallback',
  });
});

module.exports = router;
