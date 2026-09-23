{{-- MODALS: Detail Transaksi + CRUD Kebaya --}}

    {{-- ═══════════════════════════════════════════════
         MODAL DETAIL PESANAN + GAMBAR AI
         ═══════════════════════════════════════════════ --}}
    <div x-show="detailModal"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:1rem;"
         @click.self="detailModal = false">

        {{-- Backdrop --}}
        <div style="position:absolute;inset:0;background:rgba(10,5,0,0.72);backdrop-filter:blur(4px);"></div>

        {{-- Modal Box --}}
        <div style="position:relative;z-index:1;width:100%;max-width:780px;max-height:92vh;overflow-y:auto;background:#fff9f0;border-radius:16px;box-shadow:0 24px 64px rgba(0,0,0,0.4);border:1px solid rgba(212,175,55,0.3);"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Modal Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid rgba(212,175,55,0.2);background:linear-gradient(135deg,#7a0000 0%,#3d0000 100%);border-radius:16px 16px 0 0;">
                <div>
                    <p style="font-size:0.65rem;color:rgba(255,255,255,0.6);font-weight:600;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.15rem;">Detail Pesanan</p>
                    <h3 style="font-size:1.05rem;font-weight:800;color:#fff;margin:0;" x-text="detailTrx.kode_transaksi"></h3>
                </div>
                <button @click="detailModal = false"
                        style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                    ✕
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding:1.5rem;">

                {{-- SECTION: Gambar AI --}}
                <div style="margin-bottom:1.5rem;">
                    <p style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#78350f;margin-bottom:0.75rem;display:flex;align-items:center;gap:0.4rem;">🤖 Hasil Simulasi Custom AI (Image-to-Image)</p>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:start;" x-show="detailTrx.gambar_ai_result">
                        {{-- Gambar Besar --}}
                        <div style="background:#f5f0e8;border-radius:12px;border:2px solid rgba(212,175,55,0.35);overflow:hidden;">
                            <img :src="detailTrx.gambar_ai_result"
                                 :alt="'Gambar AI – ' + detailTrx.nama_kebaya"
                                 style="width:100%;height:320px;object-fit:cover;display:block;"
                                 onerror="this.onerror=null; this.closest('div').innerHTML='<div style=\'display:flex;flex-direction:column;align-items:center;justify-content:center;height:320px;color:#a8a29e;\'><span style=\'font-size:3rem;\'>🖼️</span><p style=\'font-size:0.72rem;margin-top:0.5rem;\'>Gambar tidak tersedia</p></div>'">
                            <div style="padding:0.6rem 0.75rem;background:rgba(212,175,55,0.08);border-top:1px solid rgba(212,175,55,0.2);">
                                <p style="font-size:0.6rem;color:#78350f;font-weight:600;">🎨 Warna Custom: <span x-text="detailTrx.warna_custom_ai"></span></p>
                            </div>
                        </div>

                        {{-- Info Kebaya --}}
                        <div style="display:flex;flex-direction:column;gap:0.75rem;">
                            <div style="background:#fff;border:1px solid rgba(212,175,55,0.2);border-radius:10px;padding:0.85rem;">
                                <p style="font-size:0.6rem;color:#a8a29e;font-weight:700;text-transform:uppercase;margin-bottom:0.35rem;">Nama Kebaya</p>
                                <p style="font-size:0.95rem;font-weight:700;color:#1c1917;" x-text="detailTrx.nama_kebaya"></p>
                                <p style="font-size:0.72rem;color:#78350f;margin-top:0.25rem;">Ukuran: <strong x-text="'Size ' + detailTrx.ukuran"></strong></p>
                            </div>
                            <div style="background:linear-gradient(135deg,rgba(212,175,55,0.1),rgba(139,0,0,0.05));border:1px solid rgba(212,175,55,0.3);border-radius:10px;padding:0.85rem;">
                                <p style="font-size:0.6rem;color:#a8a29e;font-weight:700;text-transform:uppercase;margin-bottom:0.35rem;">🎨 Custom Warna AI</p>
                                <p style="font-size:0.88rem;font-weight:600;color:#92400e;font-style:italic;" x-text='"\"" + detailTrx.warna_custom_ai + "\""'></p>
                            </div>
                            <div style="background:#fff;border:1px solid rgba(212,175,55,0.2);border-radius:10px;padding:0.85rem;">
                                <p style="font-size:0.6rem;color:#a8a29e;font-weight:700;text-transform:uppercase;margin-bottom:0.35rem;">Status Pesanan</p>
                                <p style="font-size:0.8rem;font-weight:700;color:#7a0000;" x-text="detailTrx.status"></p>
                                <p style="font-size:0.68rem;color:#a8a29e;margin-top:0.2rem;" x-text="detailTrx.sudah_bayar ? '✅ DP Sudah Dibayar' : '⏳ DP Belum Dibayar'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Jika tidak ada gambar AI --}}
                    <div x-show="!detailTrx.gambar_ai_result"
                         style="background:#f5f0e8;border:2px dashed rgba(212,175,55,0.4);border-radius:12px;padding:2rem;text-align:center;color:#a8a29e;">
                        <p style="font-size:2rem;">🖼️</p>
                        <p style="font-size:0.78rem;margin-top:0.5rem;">Customer tidak menggunakan fitur custom warna AI</p>
                        <p style="font-size:0.65rem;color:#d6d3d1;margin-top:0.25rem;">Kebaya dipesan dengan warna default standar katalog</p>
                    </div>
                </div>

                {{-- SECTION: Info Customer --}}
                <div style="background:#fff;border:1px solid rgba(212,175,55,0.2);border-radius:12px;padding:1rem;margin-bottom:1rem;">
                    <p style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#78350f;margin-bottom:0.75rem;">👤 Data Customer</p>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem;">
                        <div>
                            <p style="font-size:0.6rem;color:#a8a29e;font-weight:600;text-transform:uppercase;margin-bottom:0.2rem;">Nama</p>
                            <p style="font-size:0.82rem;font-weight:700;color:#1c1917;" x-text="detailTrx.nama_user"></p>
                        </div>
                        <div>
                            <p style="font-size:0.6rem;color:#a8a29e;font-weight:600;text-transform:uppercase;margin-bottom:0.2rem;">No. WhatsApp</p>
                            <p style="font-size:0.82rem;font-weight:700;color:#047857;" x-text="detailTrx.no_wa"></p>
                        </div>
                        <div>
                            <p style="font-size:0.6rem;color:#a8a29e;font-weight:600;text-transform:uppercase;margin-bottom:0.2rem;">Metode Bayar</p>
                            <p style="font-size:0.78rem;font-weight:600;color:#1c1917;" x-text="detailTrx.metode_pembayaran"></p>
                        </div>
                    </div>
                    <div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px dashed rgba(212,175,55,0.2);">
                        <p style="font-size:0.6rem;color:#a8a29e;font-weight:600;text-transform:uppercase;margin-bottom:0.2rem;">📍 Alamat / Catatan Fitting</p>
                        <p style="font-size:0.78rem;color:#57534e;" x-text="detailTrx.alamat || '— Fitting di Studio MS Harmony —'"></p>
                    </div>
                </div>

                {{-- SECTION: Detail Sewa & Keuangan --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div style="background:#fff;border:1px solid rgba(212,175,55,0.2);border-radius:12px;padding:1rem;">
                        <p style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#78350f;margin-bottom:0.75rem;">📅 Periode Sewa</p>
                        <div style="display:flex;flex-direction:column;gap:0.4rem;">
                            <div style="display:flex;justify-content:space-between;font-size:0.75rem;">
                                <span style="color:#a8a29e;">Tanggal Ambil:</span>
                                <strong x-text="detailTrx.tanggal_sewa"></strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:0.75rem;">
                                <span style="color:#a8a29e;">Tanggal Kembali:</span>
                                <strong x-text="detailTrx.tanggal_pengembalian"></strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:0.75rem;">
                                <span style="color:#a8a29e;">Durasi:</span>
                                <strong x-text="detailTrx.durasi_hari + ' hari'"></strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:0.75rem;">
                                <span style="color:#a8a29e;">Skema Bayar:</span>
                                <strong style="color:#92400e;" x-text="detailTrx.skema_bayar"></strong>
                            </div>
                        </div>
                    </div>
                    <div style="background:linear-gradient(135deg,rgba(139,0,0,0.04),rgba(212,175,55,0.06));border:1px solid rgba(212,175,55,0.25);border-radius:12px;padding:1rem;">
                        <p style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#78350f;margin-bottom:0.75rem;">💰 Rincian Pembayaran</p>
                        <div style="display:flex;flex-direction:column;gap:0.4rem;">
                            <div style="display:flex;justify-content:space-between;font-size:0.75rem;">
                                <span style="color:#a8a29e;">Total Harga:</span>
                                <strong style="color:#7a0000;" x-text="'Rp ' + detailTrx.total_harga"></strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:0.75rem;">
                                <span style="color:#a8a29e;">Status DP:</span>
                                <strong x-text="detailTrx.sudah_bayar ? '✅ Sudah Bayar' : '⏳ Belum Bayar'"
                                        :style="detailTrx.sudah_bayar ? 'color:#166534' : 'color:#92400e'"></strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Catatan Admin --}}
                <div x-show="detailTrx.catatan_admin" style="margin-top:1rem;background:#fef9c3;border:1px solid #fde68a;border-radius:10px;padding:0.85rem;">
                    <p style="font-size:0.6rem;font-weight:700;color:#92400e;margin-bottom:0.3rem;">📝 Catatan Admin</p>
                    <p style="font-size:0.78rem;color:#78350f;" x-text="detailTrx.catatan_admin"></p>
                </div>

                {{-- Action Buttons --}}
                <div style="display:flex;gap:0.75rem;margin-top:1.5rem;padding-top:1rem;border-top:1px solid rgba(212,175,55,0.2);flex-wrap:wrap;">
                    <a :href="'/admin/nota/' + detailTrx.id" target="_blank"
                       style="flex:1;min-width:140px;text-align:center;padding:0.6rem 1rem;background:linear-gradient(135deg,#7a0000,#3d0000);color:#fff;border-radius:8px;font-size:0.72rem;font-weight:700;text-decoration:none;transition:opacity 0.2s;"
                       onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                        🖨️ Cetak Nota Admin
                    </a>
                    <a :href="'https://wa.me/' + detailTrx.no_wa.replace(/^0/, '62') + '?text=Halo+Kak+' + encodeURIComponent(detailTrx.nama_user) + '+dari+MS+Harmony'" target="_blank"
                       style="flex:1;min-width:140px;text-align:center;padding:0.6rem 1rem;background:#15803d;color:#fff;border-radius:8px;font-size:0.72rem;font-weight:700;text-decoration:none;transition:opacity 0.2s;"
                       onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                        💬 Hubungi via WhatsApp
                    </a>
                    <button @click="detailModal = false"
                            style="flex:0 0 auto;padding:0.6rem 1.25rem;background:transparent;border:1px solid rgba(212,175,55,0.4);border-radius:8px;font-size:0.72rem;font-weight:600;color:#78350f;cursor:pointer;transition:background 0.2s;"
                            onmouseover="this.style.background='rgba(212,175,55,0.1)'" onmouseout="this.style.background='transparent'">
                        ✕ Tutup
                    </button>
                </div>

            </div>{{-- end modal body --}}
        </div>{{-- end modal box --}}
    </div>{{-- end modal overlay --}}

{{-- ═══ MODAL CRUD KEBAYA ══════════════════════════════════════ --}}
<div class="kebaya-crud-modal" id="kebaya-crud-modal" style="display:none;">
    <div class="kebaya-crud-panel">
        <div class="kebaya-crud-header">
            <span class="kebaya-crud-title" id="kebaya-crud-title">Tambah Kebaya Baru</span>
            <button class="kebaya-crud-close" onclick="closeKebayaForm()">✕</button>
        </div>
        <form id="kebaya-crud-form" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="kebaya-form-method" value="POST">
            <input type="hidden" name="slug" id="kebaya-form-slug" value="">
            <div class="kebaya-crud-body">
                <div class="kebaya-form-group">
                    <label class="kebaya-form-label">Nama Kebaya *</label>
                    <input type="text" name="nama" id="kebaya-f-nama" class="kebaya-form-input"
                           placeholder="Contoh: Kebaya Brokat Premium" required maxlength="100">
                </div>
                <div class="kebaya-form-row">
                    <div class="kebaya-form-group">
                        <label class="kebaya-form-label">Harga Sewa / Hari (Rp) *</label>
                        <input type="number" name="harga" id="kebaya-f-harga" class="kebaya-form-input"
                               placeholder="750000" min="10000" required>
                    </div>
                    <div class="kebaya-form-group">
                        <label class="kebaya-form-label">Status</label>
                        <select name="status" id="kebaya-f-status" class="kebaya-form-select">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="perbaikan">Perbaikan</option>
                            <option value="segera">Segera Hadir</option>
                        </select>
                    </div>
                </div>
                <div class="kebaya-form-group">
                    <label class="kebaya-form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="kebaya-f-deskripsi" class="kebaya-form-textarea"
                              placeholder="Deskripsi singkat kebaya..."></textarea>
                </div>
                <div class="kebaya-form-group" id="kebaya-foto-group">
                    <label class="kebaya-form-label">Foto Kebaya (opsional)</label>
                    <input type="file" name="foto" id="kebaya-f-foto" class="kebaya-form-input"
                           accept="image/*" style="padding:6px;">
                    <p style="font-size:0.62rem;color:#8A7060;margin-top:4px;">Format: JPG/PNG/WebP. Simpan di folder public/</p>
                </div>
            </div>
            <div class="kebaya-form-actions">
                <button type="submit" class="btn-save-kebaya" id="kebaya-submit-btn">Simpan Data</button>
                <button type="button" class="btn-cancel-kebaya" onclick="closeKebayaForm()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openKebayaForm(mode, data) {
    const modal   = document.getElementById('kebaya-crud-modal');
    const title   = document.getElementById('kebaya-crud-title');
    const form    = document.getElementById('kebaya-crud-form');
    const method  = document.getElementById('kebaya-form-method');
    const slug    = document.getElementById('kebaya-form-slug');
    const fotGrp  = document.getElementById('kebaya-foto-group');

    if (mode === 'new') {
        title.textContent = 'Tambah Kebaya Baru';
        form.action = '{{ route("admin.kebaya.store") }}';
        method.value = 'POST';
        slug.value = '';
        form.reset();
        fotGrp.style.display = '';
    } else {
        title.textContent = 'Edit Kebaya: ' + data.nama;
        form.action = `/admin/kebaya/${data.slug}/update`;
        method.value = 'PUT';
        slug.value = data.slug;
        document.getElementById('kebaya-f-nama').value      = data.nama;
        document.getElementById('kebaya-f-harga').value     = data.harga;
        document.getElementById('kebaya-f-deskripsi').value = data.deskripsi;
        document.getElementById('kebaya-f-status').value    = data.status;
        fotGrp.style.display = '';
    }
    modal.style.display = 'flex';
}
function closeKebayaForm() {
    document.getElementById('kebaya-crud-modal').style.display = 'none';
}
// Tutup modal jika klik di luar panel
document.getElementById('kebaya-crud-modal').addEventListener('click', function(e) {
    if (e.target === this) closeKebayaForm();
});
</script>
