{{-- TAB 1: Master Katalog --}}
            <div x-show="tab === 'katalog'" x-cloak>
                <div class="db-panel">
                    <div class="db-panel-header">
                        <div>
                            <span class="db-panel-badge">Component 1: Master Data Katalog</span>
                            <h3 class="db-panel-title">Koleksi Kebaya MS Harmony</h3>
                        </div>
                        <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                            @php
                                $jumlahAktif   = collect($masterKatalogDb ?? [])->where('status','aktif')->count();
                                $jumlahNon     = collect($masterKatalogDb ?? [])->where('status','nonaktif')->count();
                                $jumlahPerbk   = collect($masterKatalogDb ?? [])->where('status','perbaikan')->count();
                            @endphp
                            <span style="font-size:0.68rem;color:var(--text-muted);">
                                <strong>{{ $jumlahAktif }}</strong> Aktif &nbsp;
                                <strong>{{ $jumlahNon }}</strong> Nonaktif &nbsp;
                                <strong>{{ $jumlahPerbk }}</strong> Perbaikan
                            </span>
                            {{-- Tombol Tambah Kebaya (Admin & Super Admin) --}}
                            @if(Auth::user()?->canManageOrders())
                            <button class="btn-add-kebaya" onclick="openKebayaForm('new')">
                                + Tambah Kebaya Baru
                            </button>
                            @endif
                            <form action="{{ route('admin.kebaya.reset') }}" method="POST"
                                  onsubmit="return confirm('Reset semua status kebaya ke default?')">
                                @csrf
                                <button type="submit" style="font-size:0.6rem;padding:0.3rem 0.6rem;background:var(--bg-ivory);border:1px solid var(--border-gold-strong);border-radius:var(--radius-sm);color:var(--text-muted);cursor:pointer;">Reset Status</button>
                            </form>
                        </div>
                    </div>
                    <div class="db-panel-body">

                        <div class="db-kebaya-grid">
                            @foreach($masterKatalogDb ?? [] as $i => $kbItem)
                                @php
                                    $namaK      = $kbItem['nama'];
                                    $hargaK     = $kbItem['harga'];
                                    $fotoK      = $kbItem['foto'];
                                    $fotoBelakK = $kbItem['foto_belakang'] ?? null;
                                    $deskK      = $kbItem['deskripsi'];
                                    $slugK      = $kbItem['slug'];
                                    $kainUtamaK = $kbItem['kain_utama'] ?? 'Brokat Premium';
                                    $furingK    = $kbItem['furing'] ?? 'Katun Hero Adem';
                                    $statusK    = $kbItem['status'] ?? 'aktif';
                                    $badge      = \App\Services\KebayaService::statusBadge($statusK);
                                @endphp
                                <div x-data="{ dbView: 'depan' }" class="db-kebaya-card {{ $statusK !== 'aktif' ? 'db-kebaya-card-dim' : '' }}">
                                    <div class="db-kebaya-img-wrap" style="position:relative;">
                                        @if($fotoK)
                                            <img :src="dbView === 'depan' ? '{{ asset($fotoK) }}' : '{{ asset($fotoBelakK ?? $fotoK) }}'" alt="{{ $namaK }}" class="db-kebaya-img"
                                                 style="{{ $statusK !== 'aktif' ? 'filter:grayscale(60%) opacity(0.7);' : '' }}">
                                             @if($fotoBelakK)
                                                 <div style="position:absolute;bottom:8px;right:8px;display:flex;gap:4px;z-index:5;">
                                                     <button type="button" @click="dbView = 'depan'" :style="dbView === 'depan' ? 'background:#6B0012;color:#FFF;' : 'background:rgba(0,0,0,0.65);color:#FFF;'" style="font-size:0.7rem;font-weight:700;padding:4px 10px;border-radius:12px;border:none;cursor:pointer;">Tampak Depan</button>
                                                     <button type="button" @click="dbView = 'belakang'" :style="dbView === 'belakang' ? 'background:#6B0012;color:#FFF;' : 'background:rgba(0,0,0,0.65);color:#FFF;'" style="font-size:0.7rem;font-weight:700;padding:4px 10px;border-radius:12px;border:none;cursor:pointer;">Tampak Belakang</button>
                                                 </div>
                                             @endif
                                        @else
                                            <div class="db-kebaya-placeholder">
                                                <div class="db-kebaya-placeholder-icon">👗</div>
                                                <span class="db-kebaya-placeholder-text">Foto Segera</span>
                                            </div>
                                        @endif

                                        {{-- Badge Status --}}
                                        <span class="db-kebaya-status-badge {{ $badge['class'] }}">
                                            {{ $badge['icon'] }} {{ $badge['label'] }}
                                        </span>
                                        <span class="db-kebaya-num">#{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</span>
                                    </div>

                                    <div class="db-kebaya-body">
                                        <p class="db-kebaya-name">{{ $namaK }}</p>
                                        <p class="db-kebaya-desc">{{ $deskK }}</p>
                                        <div style="margin-top:0.4rem;padding:0.4rem 0.6rem;background:rgba(212,175,55,0.1);border-radius:6px;font-size:0.65rem;color:var(--text-medium);">
                                            🧵 <strong>Kain:</strong> {{ $kainUtamaK }} | <strong>Furing:</strong> {{ $furingK }}
                                        </div>
                                        <p class="db-kebaya-price" style="margin-top:0.4rem;">Rp {{ number_format($hargaK, 0, ',', '.') }}</p>
                                        <p class="db-kebaya-price-sub">per hari</p>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="db-kebaya-actions">
                                        {{-- Toggle Cepat --}}
                                        <form action="{{ route('admin.kebaya.toggle', $slugK) }}" method="POST" style="flex:1;">
                                            @csrf
                                            <button type="submit" class="db-kebaya-toggle-btn"
                                                    title="Klik untuk toggle status: Aktif → Nonaktif → Perbaikan → Aktif">
                                                🔄 Toggle Status
                                            </button>
                                        </form>

                                        {{-- Set Status Manual --}}
                                        <form action="{{ route('admin.kebaya.status', $slugK) }}" method="POST" style="display:flex;gap:0.25rem;">
                                            @csrf
                                            <select name="status" class="db-kebaya-status-select">
                                                <option value="aktif"     {{ $statusK==='aktif'     ? 'selected' : '' }}>🟢 Aktif</option>
                                                <option value="nonaktif"  {{ $statusK==='nonaktif'  ? 'selected' : '' }}>🔴 Nonaktif</option>
                                                <option value="perbaikan" {{ $statusK==='perbaikan' ? 'selected' : '' }}>🔧 Perbaikan</option>
                                                <option value="segera"    {{ $statusK==='segera'    ? 'selected' : '' }}>⏳ Segera</option>
                                            </select>
                                            <button type="submit" class="db-save-btn" title="Simpan Status">✓</button>
                                        </form>

                                        {{-- Sembunyikan (Soft Delete) --}}
                                        @if($statusK !== 'nonaktif')
                                        <form action="{{ route('admin.kebaya.hide', $slugK) }}" method="POST"
                                              onsubmit="return confirm('Sembunyikan kebaya \'{{ $namaK }}\' dari katalog publik?')">
                                            @csrf
                                            <button type="submit" class="db-kebaya-hide-btn" title="Sembunyikan dari katalog publik">Nonaktifkan</button>
                                        </form>
                                        @endif

                                        {{-- Edit Kebaya (Admin & Super Admin) --}}
                                        @if(Auth::user()?->canManageOrders())
                                        <div style="display:flex; gap:0.25rem;">
                                            <button class="db-kebaya-hide-btn"
                                                    style="background:#fef3c7;color:#92400e;border-color:#fde68a;flex:1;"
                                                    onclick="openKebayaForm('edit', {
                                                        slug: '{{ $slugK }}',
                                                        nama: '{{ addslashes($namaK) }}',
                                                        harga: {{ $hargaK }},
                                                        deskripsi: '{{ addslashes($deskK) }}',
                                                        status: '{{ $statusK }}'
                                                    })"
                                                    title="Edit kebaya ini">
                                                Edit
                                            </button>

                                            {{-- Hapus Permanen --}}
                                            <form action="{{ route('admin.kebaya.destroy', $slugK) }}" method="POST"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus kebaya \'{{ addslashes($namaK) }}\' secara permanen?')" style="flex:1;display:flex;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="db-kebaya-hide-btn"
                                                        style="background:#fee2e2;color:#b91c1c;border-color:#fecaca;width:100%;"
                                                        title="Hapus permanen kebaya ini">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Info Box --}}
                        <div style="margin-top:1rem;padding:0.75rem 1rem;background:rgba(212,175,55,0.06);border:1px solid var(--border-gold);border-radius:var(--radius-md);font-size:0.68rem;color:var(--text-muted);">
                            💡 <strong>Keterangan Status:</strong>
                            🟢 <strong>Aktif</strong> = Tampil di katalog publik &amp; bisa dipesan. &nbsp;
                            🔴 <strong>Nonaktif</strong> = Tersembunyi dari katalog publik. &nbsp;
                            🔧 <strong>Perbaikan</strong> = Sedang dalam perawatan (tersembunyi dari publik). &nbsp;
                            ⏳ <strong>Segera</strong> = Segera hadir, tampil di publik tapi tidak bisa dipesan.
                        </div>
                    </div>
                </div>
            </div>
