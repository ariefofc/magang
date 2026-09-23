<?php

namespace App\Helpers;

/**
 * ┌───────────────────────────────────────────────────────────────────┐
 * │  QrisDynamicHelper — EMVCo QRIS Statis → Dinamis Converter       │
 * │                                                                   │
 * │  Sesuai permintaan: App\Helpers\QrisDynamicHelper                 │
 * │  Standar: EMVCo Merchant Presented QR Specification v1.1          │
 * │  CRC    : CRC-16/CCITT-FALSE (Poly 0x1021, Init 0xFFFF)           │
 * └───────────────────────────────────────────────────────────────────┘
 */
class QrisDynamicHelper
{
    // ─── Konstanta Tag EMVCo ──────────────────────────────────────────
    const TAG_INITIATION_METHOD = '01';
    const TAG_AMOUNT            = '54';
    const TAG_CRC               = '63';
    const STATIC_MODE           = '11';
    const DYNAMIC_MODE          = '12';
    const CRC_LENGTH            = '04'; // Tag 63 selalu length 4

    // ─── CRC-16/CCITT-FALSE ──────────────────────────────────────────

    /**
     * Hitung CRC-16/CCITT-FALSE.
     *   Polynomial : 0x1021
     *   Init value : 0xFFFF
     *   Reflected  : tidak
     *   XOR out    : 0x0000
     */
    public static function crc16(string $data): int
    {
        $crc = 0xFFFF;
        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000)
                     ? (($crc << 1) ^ 0x1021) & 0xFFFF
                     : ($crc << 1) & 0xFFFF;
            }
        }
        return $crc;
    }

    /** Format CRC integer menjadi 4-digit hex uppercase. */
    private static function crcHex(int $crc): string
    {
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    // ─── TLV Parser ───────────────────────────────────────────────────

    /**
     * Parse TLV stream → array of ['tag', 'value'].
     * Parsing berhenti tepat sebelum Tag 63 (CRC).
     */
    private static function parseTlv(string $payload): array
    {
        $fields = [];
        $i      = 0;
        $len    = strlen($payload);

        while ($i + 4 <= $len) {
            $tag    = substr($payload, $i, 2);
            if ($tag === self::TAG_CRC) break;          // stop sebelum CRC
            $length = (int) substr($payload, $i + 2, 2);
            if ($i + 4 + $length > $len) break;         // guard out-of-bounds
            $fields[] = ['tag' => $tag, 'value' => substr($payload, $i + 4, $length)];
            $i += 4 + $length;
        }

        return $fields;
    }

    /**
     * Serialize array of fields kembali ke TLV string.
     * Format: TAG(2) + LEN(2, desimal, zero-padded) + VALUE
     */
    private static function buildTlv(array $fields): string
    {
        $out = '';
        foreach ($fields as $f) {
            $out .= $f['tag']
                  . str_pad((string) strlen($f['value']), 2, '0', STR_PAD_LEFT)
                  . $f['value'];
        }
        return $out;
    }

    // ─── Konversi Utama ───────────────────────────────────────────────

    /**
     * Konversi QRIS Statis → Dinamis dengan nominal terkunci.
     *
     * Algoritma:
     *  1. Parse TLV (tanpa Tag 63)
     *  2. Ubah Tag 01: '11' → '12'  (static → dynamic)
     *  3. Sisipkan Tag 54 dengan nominal bulat IDR:
     *       format → '54' + len_2digit + nominal_string
     *       contoh → '540585000'  (Rp 85.000)
     *  4. Rebuild TLV + '6304' (CRC tag tanpa value)
     *  5. Hitung CRC16 → append 4 hex digit
     *
     * @param  string $staticPayload  Raw QRIS dari DANA Bisnis (inklusif CRC)
     * @param  int    $amount         Nominal dalam Rupiah (dibulatkan)
     * @return string                 Dynamic QRIS payload siap di-encode QR
     *
     * @throws \InvalidArgumentException
     */
    public static function generate(string $staticPayload, int $amount): string
    {
        $staticPayload = trim($staticPayload);

        if (empty($staticPayload)) {
            throw new \InvalidArgumentException(
                'Payload QRIS kosong. Isi QRIS_STATIC_STRING di .env.'
            );
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                'Nominal harus lebih dari 0. Diterima: ' . $amount
            );
        }

        // 1. Parse
        $fields = self::parseTlv($staticPayload);
        if (empty($fields)) {
            throw new \InvalidArgumentException(
                'Gagal parse payload QRIS. Pastikan format EMVCo TLV yang valid.'
            );
        }

        // 2. Ubah Tag 01 → dynamic
        $foundTag01 = false;
        foreach ($fields as &$f) {
            if ($f['tag'] === self::TAG_INITIATION_METHOD) {
                $f['value'] = self::DYNAMIC_MODE;
                $foundTag01 = true;
                break;
            }
        }
        unset($f);
        if (! $foundTag01) {
            array_unshift($fields, ['tag' => self::TAG_INITIATION_METHOD, 'value' => self::DYNAMIC_MODE]);
        }

        // 3. Sisipkan / update Tag 54 (Transaction Amount)
        //    Format EMVCo: '54' + panjang_2digit + nominal_string
        //    Contoh Rp 85.000 → '54' + '05' + '85000' = '540585000'
        $amountStr = (string) $amount;
        $tag54     = ['tag' => self::TAG_AMOUNT, 'value' => $amountStr];

        $has54 = false;
        foreach ($fields as &$f) {
            if ($f['tag'] === self::TAG_AMOUNT) {
                $f['value'] = $amountStr;
                $has54      = true;
                break;
            }
        }
        unset($f);

        if (! $has54) {
            // Sisipkan sebelum tag pertama yang > '53' (biasanya '58' Country Code)
            $new      = [];
            $inserted = false;
            foreach ($fields as $f) {
                if (! $inserted && strcmp($f['tag'], '53') > 0) {
                    $new[]    = $tag54;
                    $inserted = true;
                }
                $new[] = $f;
            }
            if (! $inserted) $new[] = $tag54;
            $fields = $new;
        }

        // 4. Rebuild + append CRC tag
        $body    = self::buildTlv($fields) . self::TAG_CRC . self::CRC_LENGTH;

        // 5. Hitung CRC dan append
        return $body . self::crcHex(self::crc16($body));
    }


    /**
     * Render QRIS payload menjadi gambar PNG (base64) menggunakan
     * SimpleSoftwareIO/simple-qrcode (ext-gd required).
     *
     * @param  string $payload  Dynamic QRIS string
     * @param  int    $size     Ukuran pixel (default 280)
     * @return string           Base64-encoded PNG: "data:image/png;base64,..."
     */
    public static function toBase64Png(string $payload, int $size = 280): string
    {
        try {
            // SimpleSoftwareIO/simple-qrcode
            $png = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                        ->size($size)
                        ->errorCorrection('M')
                        ->margin(1)
                        ->generate($payload);

            return 'data:image/png;base64,' . base64_encode($png);
        } catch (\Throwable $e) {
            // Fallback: external API (butuh internet)
            \Illuminate\Support\Facades\Log::warning('[QrisDynamicHelper] GD render failed, fallback to API: ' . $e->getMessage());
            return self::toApiUrl($payload, $size);
        }
    }

    /**
     * Fallback: URL dari api.qrserver.com (butuh internet, gratis).
     */
    public static function toApiUrl(string $payload, int $size = 280): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?'
             . http_build_query([
                 'data'   => $payload,
                 'size'   => $size . 'x' . $size,
                 'format' => 'png',
                 'ecc'    => 'M',
                 'margin' => 10,
             ]);
    }

    /**
     * Decode payload untuk debugging: tampilkan semua tag beserta label.
     */
    public static function decode(string $payload): array
    {
        static $labels = [
            '00' => 'Payload Format Indicator',
            '01' => 'Initiation Method (11=Statis, 12=Dinamis)',
            '26' => 'Merchant Account — DANA/QRIS',
            '52' => 'Merchant Category Code',
            '53' => 'Currency (360=IDR)',
            '54' => '*** Transaction Amount [TERKUNCI] ***',
            '58' => 'Country Code',
            '59' => 'Merchant Name',
            '60' => 'Merchant City',
            '61' => 'Postal Code',
            '62' => 'Additional Data',
            '63' => 'CRC16 Checksum',
        ];

        $result = [];
        foreach (self::parseTlv($payload) as $f) {
            $result[] = [
                'tag'   => $f['tag'],
                'len'   => strlen($f['value']),
                'value' => $f['value'],
                'label' => $labels[$f['tag']] ?? 'Tag ' . $f['tag'],
            ];
        }
        return $result;
    }

    // ─── CRC Validation ──────────────────────────────────────────────

    /**
     * Verifikasi CRC16 dari payload QRIS.
     * Menghitung CRC dari string sampai (termasuk) "6304",
     * lalu bandingkan dengan 4 karakter CRC di akhir string.
     *
     * @param string $payload  String QRIS lengkap (termasuk tag 63)
     * @return bool
     */
    public static function validate(string $payload): bool
    {
        // Payload harus diakhiri dengan 63xx<4-hex>
        if (strlen($payload) < 8) return false;

        // Temukan posisi "6304" — tag 63, length 04
        $crcTagPos = strrpos($payload, '6304');
        if ($crcTagPos === false) return false;

        // String yang di-CRC adalah sampai dan termasuk "6304"
        $dataForCrc = substr($payload, 0, $crcTagPos + 4);
        $expectedCrc = strtoupper(substr($payload, $crcTagPos + 4, 4));
        $actualCrc   = self::crcHex(self::crc16($dataForCrc));

        return $actualCrc === $expectedCrc;
    }
}
