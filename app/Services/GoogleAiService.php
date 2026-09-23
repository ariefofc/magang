<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAiService
{
    protected string $apiKey;
    protected string $model;
    protected string $imageModel;
    protected string $endpoint;

    public function __construct()
    {
        $this->apiKey     = config('services.google_ai.key', '');
        $this->model      = config('services.google_ai.model', 'gemini-2.5-flash');
        $this->imageModel = config('services.google_ai.image_model', 'gemini-2.0-flash-preview-image-generation');
        $this->endpoint   = config('services.google_ai.endpoint', 'https://generativelanguage.googleapis.com/v1beta');
    }

    /**
     * Kirim prompt teks biasa (tanpa gambar keluaran) ke Gemini.
     */
    public function generateContent(string $prompt, ?string $base64Image = null, string $mimeType = 'image/jpeg'): array
    {
        $parts = [['text' => $prompt]];

        if ($base64Image) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data'      => $base64Image,
                ],
            ];
        }

        return $this->callApi($this->model, $parts);
    }

    /**
     * Edit/recolor gambar menggunakan model image generation Gemini.
     * Mengembalikan array mentah dari Gemini; gunakan extractImageBase64() untuk ambil hasilnya.
     */
    public function recolorImage(string $base64Image, string $colorPrompt, string $mimeType = 'image/jpeg'): array
    {
        $parts = [
            [
                'text' => "Ubah warna pakaian/kebaya pada gambar ini menjadi {$colorPrompt}. "
                        . "Pertahankan bentuk, tekstur kain, bordir, payet, pose model, dan latar belakang tetap sama persis. "
                        . "Hanya warna kain kebaya yang berubah, jangan ubah apapun selain warna kain.",
            ],
            [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data'      => $base64Image,
                ],
            ],
        ];

        return $this->callApi($this->imageModel, $parts, ['TEXT', 'IMAGE']);
    }

    /**
     * Panggil Gemini API dengan model dan parts tertentu.
     */
    protected function callApi(string $model, array $parts, ?array $responseModalities = null): array
    {
        $body = ['contents' => [['parts' => $parts]]];

        if ($responseModalities) {
            $body['generationConfig'] = ['responseModalities' => $responseModalities];
        }

        $url = "{$this->endpoint}/models/{$model}:generateContent";

        Log::debug('[GoogleAiService] callApi', [
            'model'    => $model,
            'url'      => $url,
            'modalities' => $responseModalities,
            'api_key_prefix' => !empty($this->apiKey) ? substr($this->apiKey, 0, 8) . '...' : 'KOSONG',
        ]);

        $response = Http::timeout(90)->withHeaders([
            'x-goog-api-key' => $this->apiKey,
            'Content-Type'   => 'application/json',
        ])->post($url, $body);

        if ($response->failed()) {
            Log::error('[GoogleAiService] API response failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new \RuntimeException(
                'Google AI API error: ' . $response->status() . ' - ' . $response->body()
            );
        }

        return $response->json();
    }

    /**
     * Ambil teks jawaban dari response Gemini.
     */
    public function extractText(array $response): ?string
    {
        $parts = $response['candidates'][0]['content']['parts'] ?? [];
        foreach ($parts as $part) {
            if (isset($part['text'])) {
                return $part['text'];
            }
        }
        return null;
    }

    /**
     * Ambil data gambar base64 dari response Gemini (hasil recolorImage()).
     * Mendukung dua format key: inline_data (snake_case) & inlineData (camelCase).
     */
    public function extractImageBase64(array $response): ?string
    {
        $parts = $response['candidates'][0]['content']['parts'] ?? [];
        foreach ($parts as $part) {
            if (isset($part['inline_data']['data'])) {
                return $part['inline_data']['data'];
            }
            if (isset($part['inlineData']['data'])) {
                return $part['inlineData']['data'];
            }
        }
        return null;
    }
}