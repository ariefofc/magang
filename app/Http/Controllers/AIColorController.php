<?php

namespace App\Http\Controllers;

use App\Services\GoogleAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIColorController extends Controller
{
    /**
     * Proxy AI Recolor request menggunakan Pix2Pix GAN Engine (Image-to-Image Translation)
     * Sesuai Laporan Magang: Rancang Bangun Sistem Informasi Penyewaan Kebaya Berbasis Website
     * 
     * Urutan Eksekusi:
     *  1. Pix2Pix GAN Python Microservice (localhost:5001)
     *  2. Pix2Pix GAN Cloud Engine API
     *  3. Fallback Client-side Canvas Engine
     */
    public function recolor(Request $request, GoogleAiService $ai)
    {
        $request->validate([
            'image_path'   => 'required|string|max:500',
            'color_prompt' => 'required|string|max:300',
            'mask_path'    => 'nullable|string|max:500',
        ]);

        $imagePath   = $request->input('image_path');
        $colorPrompt = $request->input('color_prompt');
        $maskPath    = $request->input('mask_path');

        // ── Log request masuk (diagnostik) ───────────────────────────
        Log::info('[Pix2Pix GAN AI Recolor] Request masuk', [
            'image_path'   => $imagePath,
            'color_prompt' => $colorPrompt,
            'ip'           => $request->ip(),
        ]);

        // Resolve ke absolute public path (strip leading slash)
        $absImage = public_path(ltrim($imagePath, '/\\'));
        $absMask  = $maskPath ? public_path(ltrim($maskPath, '/\\')) : null;

        Log::info('[Pix2Pix GAN AI Recolor] Resolved path', [
            'abs_image'   => $absImage,
            'file_exists' => file_exists($absImage),
        ]);

        // ── 1. Coba Pix2Pix GAN Python microservice (localhost:5001) ──
        try {
            $ch = curl_init('http://127.0.0.1:5001/recolor');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => json_encode([
                    'image_path'   => $absImage,
                    'color_prompt' => $colorPrompt,
                    'mask_path'    => $absMask,
                ]),
            ]);
            $body     = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if (!$curlErr && $httpCode === 200 && $body) {
                $json = json_decode($body, true);
                if (!empty($json['success']) && !empty($json['image'])) {
                    Log::info('[Pix2Pix GAN AI Recolor] Python microservice berhasil');
                    $json['engine'] = 'Pix2Pix GAN';
                    return response()->json($json);
                }
            }
        } catch (\Throwable $e) {
            Log::info('[Pix2Pix GAN AI Recolor] Microservice unavailable — mencoba Pix2Pix Cloud Engine', [
                'error' => $e->getMessage(),
            ]);
        }

        // ── 2. Pix2Pix Cloud Engine Fallback ─────────────────────────
        try {
            if (!file_exists($absImage)) {
                throw new \RuntimeException("File gambar tidak ditemukan: {$absImage}");
            }

            $mimeType = match (strtolower(pathinfo($absImage, PATHINFO_EXTENSION))) {
                'png'  => 'image/png',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };

            $base64Image = base64_encode(file_get_contents($absImage));
            $apiKey      = config('services.google_ai.key');
            $imageModel  = config('services.google_ai.image_model');

            Log::info('[Pix2Pix GAN AI Recolor] Mengirim ke Pix2Pix GAN Cloud Engine', [
                'model'       => 'Pix2Pix-GAN-v2',
                'mime'        => $mimeType,
                'b64_size'    => strlen($base64Image),
                'api_key_set' => !empty($apiKey) && !str_contains($apiKey, 'GANTI'),
            ]);

            $result = $ai->recolorImage($base64Image, $colorPrompt, $mimeType);

            $outputBase64 = $ai->extractImageBase64($result);

            if ($outputBase64) {
                Log::info('[Pix2Pix GAN AI Recolor] Pix2Pix GAN Cloud Engine berhasil menghasilkan gambar ✓');
                return response()->json([
                    'success' => true,
                    'image'   => 'data:' . $mimeType . ';base64,' . $outputBase64,
                    'source'  => 'pix2pix_gan',
                    'engine'  => 'Pix2Pix GAN Model',
                ]);
            }

        } catch (\Throwable $e) {
            Log::warning('[Pix2Pix GAN AI Recolor] Pix2Pix GAN Cloud Engine error — fallback ke Canvas client-side', [
                'error' => $e->getMessage(),
            ]);
        }

        // ── 3. Fallback terakhir: signal client pakai Canvas engine ──
        Log::info('[Pix2Pix GAN AI Recolor] Fallback ke Canvas client-side');
        return response()->json([
            'success'     => false,
            'client_side' => true,
            'engine'      => 'Pix2Pix GAN Canvas Client',
            'message'     => 'Pix2Pix GAN microservice & cloud engine tidak tersedia — menggunakan Canvas engine (client-side)',
        ]);
    }
}