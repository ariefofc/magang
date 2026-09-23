{{-- TAB 5: Laporan Penjualan --}}
            <div x-show="tab === 'laporan'" x-cloak>
                <div class="db-panel">

                    {{-- ── PANEL HEADER ── --}}
                    <div class="db-panel-header" style="flex-direction:column; align-items:stretch; gap:1rem;">

                        {{-- Row 1: Judul + Tombol Export --}}
                        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
                            <div>
                                <span class="db-panel-badge">Component 3: Output Laporan</span>
                                <h3 class="db-panel-title">Rekapitulasi &amp; Laporan Penjualan</h3>
                            </div>
                            <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                                <a href="{{ route('admin.laporan.pdf', request()->query()) }}" target="_blank" class="db-pdf-btn" title="Cetak laporan PDF sesuai filter">
                                    🖨️ Cetak PDF
                                </a>
                                <button type="button" onclick="window.print()" class="db-print-btn" title="Print via browser">
                                    🖥️ Print
                                </button>
                            </div>
                        </div>

                        {{-- Row 2: Filter Card --}}
                        <div style="background:linear-gradient(135deg,rgba(212,175,55,0.08),rgba(139,0,0,0.04));border:1px solid rgba(212,175,55,0.3);border-radius:12px;padding:1rem 1.25rem;">
                            @php
                                $namaBulan = ['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
                                $labelFilter = $periodeLabel ?? '';
                                $isFiltered = request()->filled('month') || request()->filled('year') || request()->filled('week') || request()->filled('start_date') || request()->filled('sort_order');
                            @endphp
                            <form action="{{ route('dashboard') }}" method="GET" id="laporan-filter-form">
                                <input type="hidden" name="tab" value="laporan">
                                <div style="display:flex;align-items:flex-end;gap:0.75rem;flex-wrap:wrap;">

                                    {{-- Label Kiri --}}
                                    <div style="flex-shrink:0;">
                                        <p style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--maroon);margin-bottom:0.25rem;">🔍 Filter &amp; Urutan Laporan</p>
                                        <p style="font-size:0.62rem;color:var(--text-muted);white-space:nowrap;">Per Bulan, Minggu, &amp; Tanggal</p>
                                    </div>

                                    {{-- Select Tahun --}}
                                    <div style="flex:1;min-width:110px;">
                                        <label style="display:block;font-size:0.6rem;font-weight:700;color:var(--text-dark);margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:0.05em;">Tahun</label>
                                        <div style="position:relative;">
                                            <select name="year" onchange="this.form.submit()"
                                                    style="width:100%;appearance:none;-webkit-appearance:none;background:#fff;border:1.5px solid rgba(212,175,55,0.5);border-radius:8px;padding:0.45rem 1.8rem 0.45rem 0.65rem;font-size:0.75rem;color:var(--text-dark);font-weight:500;cursor:pointer;outline:none;transition:border-color 0.2s;">
                                                <option value="">— Semua Tahun —</option>
                                                @foreach([2026, 2025] as $y)
                                                    <option value="{{ $y }}" {{ request('year', '2026') == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                                @endforeach
                                            </select>
                                            <span style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);pointer-events:none;font-size:0.6rem;color:var(--gold-dark);">▼</span>
                                        </div>
                                    </div>

                                    {{-- Select Bulan --}}
                                    <div style="flex:1;min-width:130px;">
                                        <label style="display:block;font-size:0.6rem;font-weight:700;color:var(--text-dark);margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:0.05em;">Bulan</label>
                                        <div style="position:relative;">
                                            <select name="month" onchange="this.form.submit()"
                                                    style="width:100%;appearance:none;-webkit-appearance:none;background:#fff;border:1.5px solid rgba(212,175,55,0.5);border-radius:8px;padding:0.45rem 1.8rem 0.45rem 0.65rem;font-size:0.75rem;color:var(--text-dark);font-weight:500;cursor:pointer;outline:none;transition:border-color 0.2s;">
                                                <option value="">— Semua Bulan —</option>
                                                @foreach($namaBulan as $val => $label)
                                                    <option value="{{ $val }}" {{ request('month') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <span style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);pointer-events:none;font-size:0.6rem;color:var(--gold-dark);">▼</span>
                                        </div>
                                    </div>

                                    {{-- Select Minggu --}}
                                    <div style="flex:1;min-width:140px;">
                                        <label style="display:block;font-size:0.6rem;font-weight:700;color:var(--text-dark);margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:0.05em;">Minggu Ke-</label>
                                        <div style="position:relative;">
                                            <select name="week" onchange="this.form.submit()"
                                                    style="width:100%;appearance:none;-webkit-appearance:none;background:#fff;border:1.5px solid rgba(212,175,55,0.5);border-radius:8px;padding:0.45rem 1.8rem 0.45rem 0.65rem;font-size:0.75rem;color:var(--text-dark);font-weight:500;cursor:pointer;outline:none;transition:border-color 0.2s;">
                                                <option value="">— Semua Minggu —</option>
                                                <option value="1" {{ request('week') == '1' ? 'selected' : '' }}>Minggu 1 (Tgl 1 - 7)</option>
                                                <option value="2" {{ request('week') == '2' ? 'selected' : '' }}>Minggu 2 (Tgl 8 - 14)</option>
                                                <option value="3" {{ request('week') == '3' ? 'selected' : '' }}>Minggu 3 (Tgl 15 - 21)</option>
                                                <option value="4" {{ request('week') == '4' ? 'selected' : '' }}>Minggu 4 (Tgl 22 - Akhir)</option>
                                            </select>
                                            <span style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);pointer-events:none;font-size:0.6rem;color:var(--gold-dark);">▼</span>
                                        </div>
                                    </div>

                                    {{-- Select Urutan --}}
                                    <div style="flex:1;min-width:150px;">
                                        <label style="display:block;font-size:0.6rem;font-weight:700;color:var(--text-dark);margin-bottom:0.3rem;text-transform:uppercase;letter-spacing:0.05em;">Urutan Tanggal</label>
                                        <div style="position:relative;">
                                            <select name="sort_order" onchange="this.form.submit()"
                                                    style="width:100%;appearance:none;-webkit-appearance:none;background:#fff;border:1.5px solid rgba(212,175,55,0.5);border-radius:8px;padding:0.45rem 1.8rem 0.45rem 0.65rem;font-size:0.75rem;color:var(--text-dark);font-weight:600;cursor:pointer;outline:none;transition:border-color 0.2s;">
                                                <option value="asc" {{ request('sort_order', 'asc') == 'asc' ? 'selected' : '' }}>📅 Tanggal 1 → 31 (Lama ke Baru)</option>
                                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>📅 Tanggal 31 → 1 (Baru ke Lama)</option>
                                            </select>
                                            <span style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);pointer-events:none;font-size:0.6rem;color:var(--gold-dark);">▼</span>
                                        </div>
                                    </div>

                                    {{-- Badge Aktif / Reset --}}
                                    <div style="flex-shrink:0;display:flex;align-items:flex-end;gap:0.5rem;padding-bottom:0.05rem;">
                                        @if($isFiltered)
                                            <span style="background:rgba(139,0,0,0.08);border:1px solid rgba(139,0,0,0.2);border-radius:20px;padding:0.4rem 0.8rem;font-size:0.68rem;color:var(--maroon);font-weight:700;white-space:nowrap;">
                                                📅 {{ $labelFilter }}
                                            </span>
                                            <a href="{{ route('dashboard', ['tab' => 'laporan']) }}"
                                               style="display:inline-flex;align-items:center;gap:0.3rem;background:#fff;border:1.5px solid #fca5a5;border-radius:8px;padding:0.45rem 0.8rem;font-size:0.7rem;font-weight:700;color:#dc2626;text-decoration:none;transition:all 0.2s;white-space:nowrap;"
                                               onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff'">
                                                ✕ Reset
                                            </a>
                                        @else
                                            <p style="font-size:0.62rem;color:var(--text-muted);font-style:italic;padding-bottom:0.1rem;">Menampilkan Seluruh Periode 2026</p>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ── PANEL BODY ── --}}
                    <div class="db-panel-body">

                        @php
                            $totalSemua    = collect($allLaporanTransaksi ?? [])->count();
                            $totalSelesai  = collect($allLaporanTransaksi ?? [])->where('status', 'Telah Dikembalikan')->count();
                            $totalBerjalan = collect($allLaporanTransaksi ?? [])->whereIn('status', ['Telah Dikonfirmasi', 'Sedang Fitting', 'Kebaya Telah Diambil'])->count();
                            $totalBelum    = collect($allLaporanTransaksi ?? [])->whereIn('status', ['Menunggu Pembayaran DP', 'Menunggu Konfirmasi', 'Menunggu Fitting'])->count();
                        @endphp

                        {{-- Info Periode Aktif --}}
                        @if($isFiltered)
                        <div style="display:flex;align-items:center;gap:0.75rem;background:rgba(212,175,55,0.07);border:1px solid rgba(212,175,55,0.25);border-radius:10px;padding:0.75rem 1rem;margin-bottom:1.5rem;">
                            <span style="font-size:1.25rem;flex-shrink:0;">📅</span>
                            <div>
                                <p style="font-size:0.75rem;font-weight:700;color:var(--text-dark);margin:0;">
                                    Laporan Periode: <span style="color:var(--maroon);">{{ $labelFilter }}</span>
                                </p>
                                <p style="font-size:0.65rem;color:var(--text-muted);margin:0.15rem 0 0 0;">
                                    @if($totalSemua > 0)
                                        Ditemukan <strong>{{ $totalSemua }}</strong> transaksi ·
                                        Omzet: <strong style="color:#166534;">Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</strong>
                                    @else
                                        Tidak ada transaksi pada periode ini
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- EMPTY STATE: Tidak Ada Transaksi Pada Periode Filter --}}
                        @if($isFiltered && $totalSemua === 0)
                            <div style="text-align:center;padding:3rem 2rem;background:linear-gradient(135deg,rgba(212,175,55,0.04),rgba(139,0,0,0.02));border:2px dashed rgba(212,175,55,0.35);border-radius:16px;margin-bottom:1.5rem;">
                                <div style="font-size:3.5rem;margin-bottom:1rem;opacity:0.6;">📭</div>
                                <h4 style="font-family:var(--font-heading);font-size:1.1rem;font-weight:800;color:var(--text-dark);margin-bottom:0.5rem;">
                                    Tidak Ada Transaksi di {{ $labelFilter }}
                                </h4>
                                <p style="font-size:0.78rem;color:var(--text-muted);max-width:380px;margin:0 auto 1.25rem;">
                                    Belum ada data transaksi yang tercatat pada periode <strong>{{ $labelFilter }}</strong>.
                                    Coba pilih bulan atau tahun lain, atau reset filter untuk melihat semua transaksi.
                                </p>
                                <a href="{{ route('dashboard', ['tab' => 'laporan']) }}"
                                   style="display:inline-flex;align-items:center;gap:0.5rem;background:linear-gradient(135deg,#7a0000,#3d0000);color:#fff;text-decoration:none;padding:0.65rem 1.5rem;border-radius:10px;font-size:0.78rem;font-weight:700;transition:opacity 0.2s;"
                                   onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                    📊 Lihat Semua Transaksi
                                </a>
                            </div>
                        @endif

                        {{-- Stat Cards (tetap tampil, tapi semua 0 saat filter kosong) --}}
                        <div class="db-stats-grid" style="margin-bottom:2rem;">
                            <div class="db-stat-card variant-omzet">
                                <div class="db-stat-card-deco"></div>
                                <p class="db-stat-label">Total Omzet Terekap</p>
                                <p class="db-stat-value">Rp {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</p>
                                <p class="db-stat-sub" style="color:#166534;">{{ $isFiltered ? '● Periode: '.$labelFilter : '● Realtime' }}</p>
                            </div>
                            <div class="db-stat-card variant-selesai">
                                <div class="db-stat-card-deco"></div>
                                <p class="db-stat-label">Selesai</p>
                                <p class="db-stat-value">{{ $totalSelesai }}</p>
                                <p class="db-stat-sub">Kebaya Telah Dikembalikan</p>
                            </div>
                            <div class="db-stat-card variant-berjalan">
                                <div class="db-stat-card-deco"></div>
                                <p class="db-stat-label">Sedang Berjalan</p>
                                <p class="db-stat-value">{{ $totalBerjalan }}</p>
                                <p class="db-stat-sub">Dikonfirmasi / Fitting / Diambil</p>
                            </div>
                            <div class="db-stat-card variant-belum">
                                <div class="db-stat-card-deco"></div>
                                <p class="db-stat-label">Belum Diselesaikan</p>
                                <p class="db-stat-value">{{ $totalBelum }}</p>
                                <p class="db-stat-sub">Menunggu Konfirmasi / DP</p>
                            </div>
                        </div>

                        {{-- Tabel Rekapitulasi --}}
                        @if(!($isFiltered && $totalSemua === 0))
                        <div style="margin-bottom:1rem;">
                            <h4 style="font-family:var(--font-heading);font-weight:700;font-size:1rem;color:var(--text-dark);margin-bottom:0.25rem;">Tabel Rekapitulasi Transaksi</h4>
                            <p style="font-size:0.72rem;color:var(--text-muted);">Daftar seluruh transaksi persewaan beserta status dan nominal per transaksi.</p>
                        </div>

                        <div class="db-laporan-table-wrap">
                            <table class="db-laporan-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kode Transaksi</th>
                                        <th>Customer</th>
                                        <th>Kebaya</th>
                                        <th style="text-align:right;">Total</th>
                                        <th style="text-align:right;">DP (50%)</th>
                                        <th>Metode</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $lapTotal = 0;
                                        $lapDP    = 0;
                                    @endphp
                                    @forelse(collect($allLaporanTransaksi ?? [])->sortByDesc('created_at') as $ltrx)
                                        @php
                                            $lapTotal += $ltrx->total_harga;
                                            $lapDP    += $ltrx->total_harga * 0.5;
                                            $tglTrx    = isset($ltrx->created_at) ? \Carbon\Carbon::parse($ltrx->created_at)->format('d/m/Y') : '-';
                                            $lStatusBadge = match($ltrx->status ?? '') {
                                                'Telah Dikembalikan'  => 'badge-dikembalikan',
                                                'Telah Dikonfirmasi', 'Sedang Fitting', 'Kebaya Telah Diambil' => 'badge-diambil',
                                                'Menunggu Pembayaran DP', 'Menunggu Konfirmasi', 'Menunggu Fitting' => 'badge-menunggu-dp',
                                                'Dibatalkan'          => 'badge-dibatalkan',
                                                default               => 'badge-default',
                                            };
                                            $lStatusLabel = match($ltrx->status ?? '') {
                                                'Telah Dikembalikan'  => '✅ Selesai',
                                                'Telah Dikonfirmasi'  => '🔵 Berjalan',
                                                'Sedang Fitting'      => '✂️ Fitting',
                                                'Kebaya Telah Diambil'=> '📦 Diambil',
                                                'Menunggu Pembayaran DP' => '⏳ Belum DP',
                                                'Menunggu Konfirmasi' => '🔔 Belum Konfirmasi',
                                                'Menunggu Fitting'    => '📅 Belum Fitting',
                                                'Dibatalkan'          => '❌ Batal',
                                                default               => $ltrx->status ?? '-',
                                            };
                                        @endphp
                                        <tr>
                                            <td style="color:var(--text-muted);white-space:nowrap;">{{ $tglTrx }}</td>
                                            <td style="font-family:monospace;font-size:0.65rem;color:var(--text-medium);">{{ $ltrx->kode_transaksi }}</td>
                                            <td style="font-weight:600;color:var(--text-dark);">{{ $ltrx->nama_user }}</td>
                                            <td style="color:var(--text-medium);">{{ $ltrx->nama_kebaya }}</td>
                                            <td style="text-align:right;font-weight:700;color:var(--maroon);white-space:nowrap;">Rp {{ number_format($ltrx->total_harga, 0, ',', '.') }}</td>
                                            <td style="text-align:right;color:#166534;white-space:nowrap;">Rp {{ number_format($ltrx->total_harga * 0.5, 0, ',', '.') }}</td>
                                            <td style="color:var(--text-medium);">{{ $ltrx->metode_pembayaran ?? '-' }}</td>
                                            <td><span class="status-badge {{ $lStatusBadge }}">{{ $lStatusLabel }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8">
                                                <div class="db-empty-state">
                                                    <div class="db-empty-icon">📊</div>
                                                    <p class="db-empty-title">Belum ada data transaksi</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(collect($allLaporanTransaksi ?? [])->count() > 0)
                                <tfoot>
                                    <tr>
                                        <td colspan="4" style="text-align:right;color:var(--text-dark);">TOTAL REKAPITULASI:</td>
                                        <td style="text-align:right;color:var(--maroon);white-space:nowrap;">Rp {{ number_format($lapTotal, 0, ',', '.') }}</td>
                                        <td style="text-align:right;color:#166534;white-space:nowrap;">Rp {{ number_format($lapDP, 0, ',', '.') }}</td>
                                        <td colspan="2" style="font-size:0.65rem;color:var(--text-muted);">{{ collect($allLaporanTransaksi ?? [])->count() }} transaksi total</td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                        @endif {{-- end: hide tabel jika filter kosong --}}

                    </div>
                </div>
            </div>
