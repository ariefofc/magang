<?php

namespace App\Services;

use App\Models\Transaksi;

/**
 * ┌──────────────────────────────────────────────────────────────────┐
 * │  QrisHelper — QRIS Statis → Dinamis EMVCo Converter             │
 * │                                                                  │
 * │  Spesifikasi:                                                    │
 * │  - Standar: EMVCo Merchant Presented QR Code Specification       │
 * │  - Algoritma CRC: CRC-16/CCITT-FALSE                             │
 * │    (Poly 0x1021, Init 0xFFFF, tanpa refleksi, XOR out 0x0000)    │
 * │  - Kompatibel: DANA Bisnis, OVO, GoPay, QRIS Nasional            │
 * └──────────────────────────────────────────────────────────────────┘
 */
class QrisHelper
{
    // ─── TLV Parser ──────────────────────────────────────────────────

    /**
     * Parse EMVCo TLV payload menjadi array of ['tag' => xx, 'value' => yy].
     * Parsing berhenti sebelum tag '63' (CRC).
     */
    private static function parseTlv(string $payload): array
    {
        $fields = [];
        $i      = 0;
        $len    = strlen($payload);

        while ($i < $len) {
            // Minimal 4 karakter: 2 tag + 2 length
            if ($i + 4 > $len) {
                break;
            }

            $tag    = substr($payload, $i, 2);

            // Berhenti sebelum tag CRC
            if ($tag === '63') {
                break;
            }

            $length = (int) substr($payload, $i + 2, 2);

            // Guard: jangan baca melebihi panjang string
            if ($i + 4 + $length > $len) {
                break;
            }

            $value = substr($payload, $i + 4, $length);
            $fields[] = ['tag' => $tag, 'value' => $value];
            $i += 4 + $length;
        }

        return $fields;
    }

    /**
     * Rebuild TLV stream dari array of fields.
     * Format: TAG(2) + LEN(2, desimal, zero-padded) + VALUE
     */
    private static function buildTlv(array $fields): string
    {
        $result = '';
        foreach ($fields as $field) {
            $len     = strlen($field['value']);
            $result .= $field['tag']
                     . str_pad((string) $len, 2, '0', STR_PAD_LEFT)
                     . $field['value'];
        }
        return $result;
    }

    // ─── CRC-16 CCITT-FALSE ──────────────────────────────────────────

    /**
     * Hitung CRC-16/CCITT-FALSE.
     * - Polynomial : 0x1021
     * - Init value : 0xFFFF
     * - Reflected  : tidak (input & output)
     * - XOR out    : 0x0000
     *
     * @param  string $data String ASCII yang dihitung CRC-nya
     * @return int          Nilai CRC 16-bit (0–65535)
     */
    public static function crc16(string $data): int
    {
        $crc = 0xFFFF;

        for ($i = 0, $len = strlen($data); $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);

            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return $crc;
    }

    /**
     * Format nilai CRC sebagai 4 karakter hex uppercase.
     */
    private static function crcHex(int $crc): string
    {
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    // ─── Validasi ────────────────────────────────────────────────────

    /**
     * Validasi payload QRIS: periksa apakah CRC (Tag 63) cocok.
     *
     * @param  string $payload  Raw QRIS string lengkap termasuk Tag 63
     * @return bool
     */
    public static function validate(string $payload): bool
    {
        $payload = trim($payload);

        // Panjang minimal: tag63 (4) + CRC value (4)
        if (strlen($payload) < 8) {
            return false;
        }

        // CRC dihitung dari awal sampai SETELAH "6304" (inklusif),
        // tidak termasuk 4 digit nilai CRC di paling akhir.
        $withoutCrcValue   = substr($payload, 0, -4);    // buang 4 hex terakhir
        $embeddedCrcValue  = strtoupper(substr($payload, -4)); // ambil 4 hex terakhir

        $computed = self::crcHex(self::crc16($withoutCrcValue));

        return $computed === $embeddedCrcValue;
    }

    // ─── Konversi Statis → Dinamis ───────────────────────────────────

    /**
     * Konversi payload QRIS statis (Tag 01 = '11') menjadi dinamis
     * (Tag 01 = '12') dengan nominal terkunci pada Tag 54.
     *
     * Urutan operasi:
     *  1. Trim & parse TLV (tanpa Tag 63)
     *  2. Ubah Tag 01: '11' → '12'
     *  3. Sisipkan/update Tag 54 dengan nominal bulat
     *  4. Rebuild TLV + append "6304"
     *  5. Hitung CRC16 dan append 4-hex result
     *
     * @param  string $staticPayload  Raw QRIS statis (termasuk CRC akhir)
     * @param  int    $amount         Nominal dalam Rupiah (sudah dibulatkan)
     * @return string                 Dynamic QRIS payload yang siap di-encode ke QR image
     *
     * @throws \InvalidArgumentException Jika payload kosong atau bukan QRIS valid
     */
    public static function makeDynamic(string $staticPayload, int $amount): string
    {
        $staticPayload = trim($staticPayload);

        if (empty($staticPayload)) {
            throw new \InvalidArgumentException(
                'Payload QRIS kosong. Periksa QRIS_STATIC_STRING di .env.'
            );
        }

        // ── 1. Parse TLV ─────────────────────────────────────────────
        $fields = self::parseTlv($staticPayload);

        if (empty($fields)) {
            throw new \InvalidArgumentException(
                'Gagal parse payload QRIS. Pastikan format benar (EMVCo TLV).'
            );
        }

        // ── 2. Ubah Tag 01: static '11' → dynamic '12' ───────────────
        $tag01Found = false;
        foreach ($fields as &$field) {
            if ($field['tag'] === '01') {
                $field['value'] = '12'; // Dynamic
                $tag01Found     = true;
                break;
            }
        }
        unset($field);

        // Jika Tag 01 tidak ada (jarang), tambahkan di awal
        if (! $tag01Found) {
            array_unshift($fields, ['tag' => '01', 'value' => '12']);
        }

        // ── 3. Sisipkan/update Tag 54 (Transaction Amount) ───────────
        // Nominal IDR tidak memakai desimal dalam format QRIS
        $amountStr = (string) $amount;
        $tag54     = ['tag' => '54', 'value' => $amountStr];

        // Cek apakah Tag 54 sudah ada (jarang pada statis, tapi antisipasi)
        $has54 = false;
        foreach ($fields as &$field) {
            if ($field['tag'] === '54') {
                $field['value'] = $amountStr;
                $has54          = true;
                break;
            }
        }
        unset($field);

        if (! $has54) {
            // Sisipkan sebelum tag pertama yang > '53' (biasanya Tag 58)
            // Sehingga urutan: ... 52 → 53 → [54] → 58 → 59 → ...
            $newFields  = [];
            $inserted   = false;

            foreach ($fields as $field) {
                // Tag 54 harus sebelum tag >= 58 (Country Code)
                // Kita sisipkan sebelum tag pertama yang > '53'
                if (! $inserted && strcmp($field['tag'], '53') > 0) {
                    $newFields[] = $tag54;
                    $inserted    = true;
                }
                $newFields[] = $field;
            }

            // Fallback: jika semua tag ≤ '53', tambahkan di akhir sebelum CRC
            if (! $inserted) {
                $newFields[] = $tag54;
            }

            $fields = $newFields;
        }

        // ── 4. Rebuild TLV + "6304" (CRC tag tanpa nilai) ────────────
        $rebuilt    = self::buildTlv($fields);
        $forCrc     = $rebuilt . '6304'; // String yang di-hash CRC

        // ── 5. Hitung CRC16 ───────────────────────────────────────────
        $crcValue   = self::crcHex(self::crc16($forCrc));

        return $forCrc . $crcValue;
    }

    // ─── Helper untuk Transaksi ───────────────────────────────────────

    /**
     * Generate dynamic QRIS payload untuk sebuah Transaksi.
     * Nominal diambil dari $transaksi->nominalDP().
     *
     * @param  Transaksi $transaksi
     * @return array ['payload' => string, 'nominal' => int, 'skema' => string]
     *
     * @throws \RuntimeException  Jika QRIS_STATIC_STRING belum diisi
     * @throws \InvalidArgumentException Jika payload tidak valid
     */
    public static function forTransaksi(Transaksi $transaksi): array
    {
        $staticPayload = config('qris.static_string', '');

        if (empty($staticPayload) || $staticPayload === 'TEMPEL_RAW_STRING_QRIS_DANA_BISNIS_ANDA_DI_SINI') {
            throw new \RuntimeException(
                'QRIS_STATIC_STRING belum diisi di file .env. '
                . 'Salin raw string QRIS dari DANA Bisnis Anda.'
            );
        }

        // Nominal bulat dalam Rupiah (ceiling untuk menghindari pecahan sen)
        $nominal = (int) ceil($transaksi->nominalDP());

        if ($nominal <= 0) {
            throw new \InvalidArgumentException('Nominal pembayaran harus lebih dari 0.');
        }

        $dynamicPayload = self::makeDynamic($staticPayload, $nominal);

        return [
            'payload'      => $dynamicPayload,
            'nominal'      => $nominal,
            'skema'        => $transaksi->labelSkema(),
            'total_harga'  => (int) $transaksi->total_harga,
            'sisa'         => (int) $transaksi->sisaPelunasan(),
            'is_valid_crc' => self::validate($dynamicPayload),
        ];
    }

    // ─── Decode Helper (untuk debugging) ─────────────────────────────

    /**
     * Decode payload QRIS menjadi array key-value yang mudah dibaca.
     * Berguna untuk debugging / verifikasi string QRIS.
     *
     * @param  string $payload
     * @return array  ['tag' => tag, 'len' => len, 'value' => value, 'label' => label]
     */
    public static function decode(string $payload): array
    {
        $fields   = self::parseTlv($payload);
        $labelMap = [
            '00' => 'Payload Format Indicator',
            '01' => 'Point of Initiation Method (11=Statis, 12=Dinamis)',
            '02' => 'Merchant Account Info (Visa)',
            '26' => 'Merchant Account Info (DANA/QRIS)',
            '27' => 'Merchant Account Info (QRIS)',
            '28' => 'Merchant Account Info (OVO)',
            '51' => 'Merchant Account Info (GoPay/Linkaja)',
            '52' => 'Merchant Category Code',
            '53' => 'Transaction Currency (360=IDR)',
            '54' => 'Transaction Amount ← [NOMINAL DIKUNCI]',
            '55' => 'Tip or Convenience Indicator',
            '57' => 'Value of Convenience Fee Fixed',
            '58' => 'Country Code',
            '59' => 'Merchant Name',
            '60' => 'Merchant City',
            '61' => 'Postal Code',
            '62' => 'Additional Data Field',
            '63' => 'CRC16 Checksum',
        ];

        $decoded = [];
        foreach ($fields as $field) {
            $decoded[] = [
                'tag'   => $field['tag'],
                'len'   => strlen($field['value']),
                'value' => $field['value'],
                'label' => $labelMap[$field['tag']] ?? 'Unknown Tag ' . $field['tag'],
            ];
        }

        return $decoded;
    }
}
