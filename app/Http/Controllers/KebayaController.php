<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\KebayaService;

class KebayaController extends Controller
{
    protected KebayaService $kebayaService;

    // Path file JSON untuk custom kebaya (tambahan dari admin)
    protected string $customPath;

    public function __construct(KebayaService $kebayaService)
    {
        $this->kebayaService = $kebayaService;
        $this->customPath    = storage_path('app/kebaya_custom.json');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /** Baca daftar kebaya custom dari JSON */
    protected function readCustom(): array
    {
        if (!file_exists($this->customPath)) return [];
        $data = json_decode(file_get_contents($this->customPath), true);
        return is_array($data) ? $data : [];
    }

    /** Tulis daftar kebaya custom ke JSON */
    protected function writeCustom(array $data): void
    {
        file_put_contents($this->customPath, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // ─── Status Management ────────────────────────────────────────

    /**
     * Toggle status kebaya: aktif → nonaktif → perbaikan → aktif
     */
    public function toggleStatus(Request $request, string $slug)
    {
        $newStatus = $this->kebayaService->cycleStatus($slug);

        $kebaya = KebayaService::findBySlug($slug);
        $label  = KebayaService::statusBadge($newStatus)['label'];

        return redirect()->back()->with(
            'success',
            "Status kebaya \"" . ($kebaya['nama'] ?? $slug) . "\" diubah menjadi: {$label}"
        );
    }

    /**
     * Set status kebaya secara langsung (dari form select)
     */
    public function setStatus(Request $request, string $slug)
    {
        $request->validate(['status' => 'required|in:aktif,nonaktif,perbaikan,segera']);

        $this->kebayaService->setStatus($slug, $request->status);

        $kebaya = KebayaService::findBySlug($slug);
        $label  = KebayaService::statusBadge($request->status)['label'];

        return redirect()->back()->with(
            'success',
            "Status kebaya \"" . ($kebaya['nama'] ?? $slug) . "\" diubah menjadi: {$label}"
        );
    }

    /**
     * Sembunyikan kebaya (set nonaktif — soft delete)
     */
    public function hide(string $slug)
    {
        $kebaya  = KebayaService::findBySlug($slug);
        $customs = $this->readCustom();
        if (!$kebaya) {
            $kebaya = collect($customs)->firstWhere('slug', $slug);
        }

        $this->kebayaService->setStatus($slug, KebayaService::STATUS_NONAKTIF);

        return redirect()->back()->with(
            'success',
            "Kebaya \"" . ($kebaya['nama'] ?? $slug) . "\" telah disembunyikan dari katalog publik."
        );
    }

    /**
     * Reset semua status ke default
     */
    public function resetAll()
    {
        $this->kebayaService->resetAll();
        return redirect()->back()->with('success', 'Semua status kebaya telah direset ke default.');
    }

    // ─── CRUD Custom Kebaya ───────────────────────────────────────

    /**
     * Tambah kebaya baru ke kebaya_custom.json
     * Route: POST /admin/kebaya
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'harga'     => 'required|integer|min:10000',
            'status'    => 'required|in:aktif,nonaktif,perbaikan,segera',
            'deskripsi' => 'nullable|string|max:500',
            'foto'      => 'nullable|image|max:3072',
        ]);

        // Auto-generate slug unik dari nama
        $slug     = Str::slug($request->nama);
        $existing = collect(KebayaService::$masterKatalog)->pluck('slug')->toArray();
        $customs  = $this->readCustom();
        $existing = array_merge($existing, array_column($customs, 'slug'));
        $base     = $slug;
        $i        = 1;
        while (in_array($slug, $existing)) {
            $slug = $base . '-' . $i++;
        }

        // Handle upload foto
        $fotoName = null;
        if ($request->hasFile('foto')) {
            $file     = $request->file('foto');
            $fotoName = $slug . '.' . $file->getClientOriginalExtension();
            $file->move(public_path(), $fotoName);
        }

        $customs[] = [
            'slug'      => $slug,
            'nama'      => $request->nama,
            'harga'     => (int) $request->harga,
            'foto'      => $fotoName,
            'deskripsi' => $request->deskripsi ?? '',
            'is_custom' => true,
        ];
        $this->writeCustom($customs);
        $this->kebayaService->setStatus($slug, $request->status);

        return redirect()->back()->with('success', "Kebaya \"{$request->nama}\" berhasil ditambahkan.");
    }

    /**
     * Edit kebaya (custom: semua field; master: harga & deskripsi & status saja).
     * Route: PUT /admin/kebaya/{slug}/update
     */
    public function update(Request $request, string $slug)
    {
        $request->validate([
            'nama'      => 'nullable|string|max:100',
            'harga'     => 'required|integer|min:10000',
            'status'    => 'required|in:aktif,nonaktif,perbaikan,segera',
            'deskripsi' => 'nullable|string|max:500',
            'foto'      => 'nullable|image|max:3072',
        ]);

        $customs   = $this->readCustom();
        $customIdx = null;
        foreach ($customs as $idx => $c) {
            if ($c['slug'] === $slug) { $customIdx = $idx; break; }
        }

        if ($customIdx !== null) {
            $item = $customs[$customIdx];

            if ($request->hasFile('foto')) {
                if ($item['foto'] && file_exists(public_path($item['foto']))) {
                    @unlink(public_path($item['foto']));
                }
                $file         = $request->file('foto');
                $fotoName     = $slug . '.' . $file->getClientOriginalExtension();
                $file->move(public_path(), $fotoName);
                $item['foto'] = $fotoName;
            }

            $item['nama']        = $request->nama ?: $item['nama'];
            $item['harga']       = (int) $request->harga;
            $item['deskripsi']   = $request->deskripsi ?? ($item['deskripsi'] ?? '');
            $customs[$customIdx] = $item;
            $this->writeCustom($customs);
        }

        // Status disimpan untuk semua (master maupun custom)
        $this->kebayaService->setStatus($slug, $request->status);

        $masterItem = KebayaService::findBySlug($slug);
        $nama       = $masterItem['nama']
                   ?? ($customIdx !== null ? $customs[$customIdx]['nama'] : $slug);

        return redirect()->back()->with('success', "Kebaya \"{$nama}\" berhasil diperbarui.");
    }

    /**
     * Hapus kebaya.
     * - Custom: hapus permanen dari custom.json + hapus foto.
     * - Master: hanya set nonaktif (data hardcoded tidak bisa dihapus).
     * Route: DELETE /admin/kebaya/{slug}
     */
    public function destroy(string $slug)
    {
        $customs    = $this->readCustom();
        $customItem = collect($customs)->firstWhere('slug', $slug);

        if (!$customItem) {
            // Master kebaya: hanya bisa dinonaktifkan
            $this->kebayaService->setStatus($slug, KebayaService::STATUS_NONAKTIF);
            $kebaya = KebayaService::findBySlug($slug);
            return redirect()->back()->with(
                'success',
                "Kebaya \"" . ($kebaya['nama'] ?? $slug) . "\" disembunyikan (data master tidak dapat dihapus permanen)."
            );
        }

        // Hapus foto jika ada
        if (!empty($customItem['foto']) && file_exists(public_path($customItem['foto']))) {
            @unlink(public_path($customItem['foto']));
        }

        // Hapus dari custom.json
        $updated = array_filter($customs, fn($c) => $c['slug'] !== $slug);
        $this->writeCustom($updated);

        // Hapus juga dari status.json
        $statusPath = storage_path('app/kebaya_status.json');
        if (file_exists($statusPath)) {
            $statuses = json_decode(file_get_contents($statusPath), true) ?? [];
            unset($statuses[$slug]);
            file_put_contents($statusPath, json_encode($statuses, JSON_PRETTY_PRINT));
        }

        return redirect()->back()->with('success', "Kebaya \"{$customItem['nama']}\" berhasil dihapus permanen.");
    }
}
