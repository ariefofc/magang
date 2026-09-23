{{-- TAB 3: Penjadwalan Fitting --}}
<div x-show="tab === 'fitting'" x-cloak>
    <div class="db-panel">
        <div class="db-panel-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <div>
                <span class="db-panel-badge">Penjadwalan &amp; Status Real-Time</span>
                <h3 class="db-panel-title">Penjadwalan Ukur &amp; Fitting Kebaya</h3>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <a href="{{ route('dashboard', ['tab' => 'fitting']) }}" class="btn btn-sm" style="background:var(--bg-ivory);border:1px solid var(--border-gold);color:var(--maroon);font-weight:700;padding:0.45rem 0.85rem;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:0.35rem;font-size:0.75rem;">
                    🔄 Refresh Data Real-Time
                </a>
            </div>
        </div>
        <div class="db-panel-body">

            <div class="db-step-guide">
                <p class="db-step-guide-title">✂️ Cara Mengelola Penjadwalan Fitting:</p>
                <ol>
                    <li>Customer dapat mengajukan jadwal fitting dari Nota Digital mereka, atau via <strong>Live Chat Admin</strong>.</li>
                    <li>Pengajuan jadwal dari customer otomatis muncul di atas dengan badge 🔔 <strong>Customer Mengajukan Jadwal Fitting</strong>.</li>
                    <li>Klik <strong>"✅ Setujui &amp; Konfirmasi Fitting"</strong> untuk menyetujui jadwal fitting customer secara langsung.</li>
                </ol>
            </div>

            @php
                // Ambil SEMUA transaksi fitting & pengambilan langsung dari DB (bebas dari limitasi pagination)
                $trxFitting = \App\Models\Transaksi::whereIn('status', [
                    'Menunggu Fitting',
                    'Telah Dikonfirmasi',
                    'Sedang Fitting',
                    'Kebaya Telah Diambil',
                    'Menunggu Konfirmasi'
                ])->orderByRaw("CASE 
                    WHEN status = 'Menunggu Fitting' THEN 1 
                    WHEN status = 'Telah Dikonfirmasi' THEN 2 
                    WHEN status = 'Sedang Fitting' THEN 3 
                    WHEN status = 'Kebaya Telah Diambil' THEN 4 
                    ELSE 5 END")
                  ->orderBy('updated_at', 'desc')
                  ->get();

                $totalReqFitting = $trxFitting->filter(function($t) {
                    return $t->status === 'Menunggu Fitting' || str_contains($t->catatan_admin ?? '', 'Request Fitting Customer');
                })->count();
            @endphp

            @if($totalReqFitting > 0)
                <div style="background:#e0f2fe;border:1.5px solid #0284c7;border-radius:10px;padding:0.85rem 1.1rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;box-shadow:0 4px 12px rgba(2,132,199,0.15);">
                    <div style="display:flex;align-items:center;gap:0.6rem;">
                        <span style="font-size:1.4rem;">🔔</span>
                        <div>
                            <strong style="color:#0369a1;font-size:0.85rem;">Ada {{ $totalReqFitting }} Pengajuan Jadwal Fitting Baru dari Customer!</strong>
                            <p style="font-size:0.72rem;color:#075985;margin:2px 0 0 0;">Customer telah memilih jam &amp; lokasi studio fitting. Periksa detailnya di bawah dan klik konfirmasi.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Panduan Live Chat --}}
            <div style="background:rgba(212,175,55,0.06);border:1px solid var(--border-gold);border-radius:var(--radius-md);padding:0.85rem 1.1rem;margin-bottom:1.25rem;">
                <p style="font-size:0.7rem;font-weight:700;color:var(--maroon);margin-bottom:0.25rem;">💡 Live Chat &amp; Koordinasi Fitting</p>
                <p style="font-size:0.7rem;color:var(--text-muted);margin:0;">Gunakan tombol <strong>💬 Live Chat Fitting</strong> untuk menghubungi atau mengonfirmasi ketersediaan studio kepada customer secara langsung.</p>
            </div>

            @if($trxFitting->count() > 0)
                <p style="font-size:0.78rem;font-weight:700;color:var(--text-dark);margin-bottom:0.75rem;">
                    Daftar Pesanan Fitting &amp; Pengambilan ({{ $trxFitting->count() }})
                </p>
                <div style="display:flex;flex-direction:column;gap:0.85rem;">
                    @foreach($trxFitting as $tf)
                        @php
                            $tglSewa    = $tf->tanggal_sewa ? \Carbon\Carbon::parse($tf->tanggal_sewa)->format('d/m/Y') : '-';
                            $tglKembali = $tf->tanggal_pengembalian ? \Carbon\Carbon::parse($tf->tanggal_pengembalian)->format('d/m/Y') : '-';
                            $isDiambil  = $tf->status === 'Kebaya Telah Diambil';
                            $isReqFit   = $tf->status === 'Menunggu Fitting' || str_contains($tf->catatan_admin ?? '', 'Request Fitting Customer');

                            $statusFitBadge = match($tf->status) {
                                'Telah Dikonfirmasi'  => 'badge-dikonfirmasi',
                                'Sedang Fitting'      => 'badge-fitting',
                                'Kebaya Telah Diambil'=> 'badge-diambil',
                                'Menunggu Fitting'    => 'badge-fitting',
                                'Menunggu Konfirmasi' => 'badge-menunggu-konfirmasi',
                                default               => 'badge-default',
                            };
                        @endphp
                        <div class="db-fitting-card" style="background:{{ $isReqFit ? '#f0f9ff' : '#fff' }};border:1.5px solid {{ $isReqFit ? '#0284c7' : 'var(--border-light)' }};border-radius:12px;padding:1.1rem 1.25rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;box-shadow:{{ $isReqFit ? '0 4px 12px rgba(2,132,199,0.12)' : '0 2px 6px rgba(0,0,0,0.03)' }};">
                            <div class="db-fitting-card-left" style="display:flex;gap:1rem;align-items:center;">
                                <div class="db-fitting-avatar" style="width:48px;height:48px;border-radius:50%;background:{{ $isReqFit ? 'rgba(2,132,199,0.15)' : ($isDiambil ? 'rgba(212,175,55,0.15)' : 'rgba(107,0,18,0.08)') }};display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
                                    {{ $isReqFit ? '📩' : ($isDiambil ? '📦' : '✂️') }}
                                </div>
                                <div>
                                    @if($isReqFit)
                                        <span style="background:#0284c7;color:#fff;font-size:0.62rem;font-weight:800;padding:2px 8px;border-radius:10px;display:inline-block;margin-bottom:0.25rem;">
                                            🔔 Customer Mengajukan Jadwal Fitting
                                        </span>
                                    @endif
                                    <h4 class="db-fitting-name" style="font-size:0.95rem;font-weight:700;color:var(--text-dark);margin:0 0 0.2rem 0;">{{ $tf->nama_user }}</h4>
                                    <p class="db-fitting-info" style="font-size:0.75rem;color:var(--text-medium);margin:0;">{{ $tf->nama_kebaya }} — Size <strong>{{ $tf->ukuran }}</strong></p>
                                    <p class="db-fitting-kode" style="font-size:0.7rem;color:var(--text-light);margin:0.2rem 0 0 0;">
                                        Kode: <strong>{{ $tf->kode_transaksi }}</strong> · Tgl Sewa: <strong>{{ $tglSewa }}</strong> s/d <strong>{{ $tglKembali }}</strong>
                                    </p>
                                    @if(!empty($tf->catatan_admin))
                                        <div style="margin-top:0.35rem;background:rgba(2,132,199,0.08);border-left:3px solid #0284c7;padding:0.25rem 0.5rem;font-size:0.7rem;color:#0369a1;font-weight:600;">
                                            📌 {{ $tf->catatan_admin }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.5rem;">
                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <span class="status-badge {{ $statusFitBadge }}">{{ $tf->status }}</span>
                                </div>

                                {{-- Aksi Cepat: Quick Setujui & Live Chat & Dropdown Ubah Status --}}
                                <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                    @if($isReqFit)
                                        <form action="{{ route('transaksi.updateStatus', $tf->id) }}" method="POST" style="display:inline-flex;">
                                            @csrf
                                            <input type="hidden" name="status" value="Sedang Fitting">
                                            <input type="hidden" name="redirect_tab" value="fitting">
                                            <button type="submit" class="btn btn-sm" style="background:#0284c7;color:white;border:none;font-weight:700;font-size:0.75rem;padding:0.45rem 0.75rem;border-radius:6px;cursor:pointer;">
                                                ✅ Setujui &amp; Konfirmasi Fitting
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.chat.inbox', ['code' => $tf->kode_transaksi]) }}"
                                       class="btn btn-sm" style="background:#8B5A2B;color:white;border:none;gap:0.35rem;font-weight:700;font-size:0.75rem;padding:0.45rem 0.75rem;border-radius:6px;text-decoration:none;">
                                        💬 Live Chat Fitting
                                    </a>

                                    <form action="{{ route('transaksi.updateStatus', $tf->id) }}" method="POST" style="display:inline-flex;align-items:center;">
                                        @csrf
                                        <input type="hidden" name="redirect_tab" value="fitting">
                                        <select name="status" onchange="if(confirm('Ubah status pesanan ini?')) this.form.submit()" style="font-size:0.75rem;padding:0.4rem 0.6rem;border-radius:6px;border:1px solid var(--border-gold);background:var(--bg-ivory);color:var(--text-dark);font-weight:700;cursor:pointer;">
                                            <option value="" disabled selected>⚙️ Ubah Status...</option>
                                            <option value="Telah Dikonfirmasi">Telah Dikonfirmasi</option>
                                            <option value="Sedang Fitting">✂️ Sedang Fitting</option>
                                            <option value="Kebaya Telah Diambil">📦 Kebaya Telah Diambil</option>
                                            <option value="Mengajukan Pengembalian">📩 Mengajukan Pengembalian</option>
                                            <option value="Telah Dikembalikan">✅ Telah Dikembalikan</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="db-empty-state">
                    <div class="db-empty-icon">✂️</div>
                    <p class="db-empty-title">Tidak ada pesanan yang perlu dijadwalkan fitting</p>
                    <p class="db-empty-sub">Pesanan yang telah dikonfirmasi atau sedang dalam proses fitting akan otomatis muncul di sini secara real-time</p>
                </div>
            @endif
        </div>
    </div>
</div>
