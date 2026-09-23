{{-- TAB 4: Verifikasi Pengembalian --}}
<div x-show="tab === 'pengembalian'" x-cloak>
    <div class="db-panel">
        <div class="db-panel-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
            <div>
                <span class="db-panel-badge">Verifikasi &amp; Status Real-Time</span>
                <h3 class="db-panel-title">Verifikasi Pengembalian Kebaya</h3>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <a href="{{ route('dashboard', ['tab' => 'pengembalian']) }}" class="btn btn-sm" style="background:var(--bg-ivory);border:1px solid var(--border-gold);color:var(--maroon);font-weight:700;padding:0.45rem 0.85rem;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:0.35rem;font-size:0.75rem;">
                    🔄 Refresh Data Real-Time
                </a>
            </div>
        </div>
        <div class="db-panel-body">
            <div class="db-step-guide" style="margin-bottom:1rem;">
                <p class="db-step-guide-title">🔁 Cara Memverifikasi Pengembalian:</p>
                <ol>
                    <li>Pastikan kebaya yang dikembalikan dalam kondisi baik dan tidak rusak/hilang.</li>
                    <li>Pastikan pelunasan (jika ada sisa) sudah dibayarkan oleh customer.</li>
                    <li>Jika melewati tanggal kembali, kenakan denda Rp50.000/hari.</li>
                    <li>Klik <strong>"Verifikasi &amp; Selesai"</strong> untuk menutup pesanan dan membebaskan jadwal pada kalender sewa real-time.</li>
                </ol>
            </div>
            
            @php
                // Ambil SEMUA transaksi pengembalian & aktif langsung dari DB (tidak terpaginasi)
                $trxKembali = \App\Models\Transaksi::whereIn('status', [
                    'Mengajukan Pengembalian',
                    'Kebaya Telah Diambil',
                    'Sedang Fitting',
                    'Telah Dikonfirmasi',
                    'Menunggu Fitting'
                ])->orderByRaw("CASE 
                    WHEN status = 'Mengajukan Pengembalian' THEN 1 
                    WHEN status = 'Kebaya Telah Diambil' THEN 2 
                    ELSE 3 END")
                  ->orderBy('updated_at', 'desc')
                  ->get();

                $totalPengajuan = $trxKembali->where('status', 'Mengajukan Pengembalian')->count();
            @endphp

            @if($totalPengajuan > 0)
                <div style="background:#e0f2fe;border:1.5px solid #0284c7;border-radius:10px;padding:0.85rem 1.1rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;box-shadow:0 4px 12px rgba(2,132,199,0.15);">
                    <div style="display:flex;align-items:center;gap:0.6rem;">
                        <span style="font-size:1.4rem;">🔔</span>
                        <div>
                            <strong style="color:#0369a1;font-size:0.85rem;">Ada {{ $totalPengajuan }} Pengajuan Pengembalian Kebaya Baru!</strong>
                            <p style="font-size:0.72rem;color:#075985;margin:2px 0 0 0;">Customer telah mengajukan pengembalian kebaya. Verifikasi fisik &amp; sisa pelunasan sebelum menyelesaikan pesanan.</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if($trxKembali->count() > 0)
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    @foreach($trxKembali as $tk)
                        @php
                            $isRequest = $tk->status === 'Mengajukan Pengembalian';
                        @endphp
                        <div style="background:{{ $isRequest ? '#f0f9ff' : '#fff' }};border:1.5px solid {{ $isRequest ? '#0284c7' : 'rgba(21,128,61,0.2)' }};border-radius:12px;padding:1.25rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;box-shadow:{{ $isRequest ? '0 4px 12px rgba(2,132,199,0.12)' : 'none' }};">
                            <div style="display:flex;gap:1rem;align-items:center;">
                                <div style="width:48px;height:48px;background:{{ $isRequest ? 'rgba(2,132,199,0.15)' : 'rgba(21,128,61,0.1)' }};border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                                    {{ $isRequest ? '📩' : '📦' }}
                                </div>
                                <div>
                                    @if($isRequest)
                                        <span style="background:#0284c7;color:#fff;font-size:0.62rem;font-weight:800;padding:2px 8px;border-radius:10px;display:inline-block;margin-bottom:0.25rem;">
                                            🔔 Customer Mengajukan Pengembalian
                                        </span>
                                    @endif
                                    <h4 style="font-size:1rem;color:var(--text-dark);font-weight:700;margin:0 0 0.2rem 0;">{{ $tk->nama_user }}</h4>
                                    <p style="font-size:0.75rem;color:var(--text-medium);margin:0;">{{ $tk->nama_kebaya }} (Size {{ $tk->ukuran }}) | Kode: <strong>{{ $tk->kode_transaksi }}</strong></p>
                                    <p style="font-size:0.7rem;color:var(--text-light);margin:0.2rem 0 0 0;">
                                        Tgl Kembali Seharusnya: <strong>{{ $tk->tanggal_pengembalian ? \Carbon\Carbon::parse($tk->tanggal_pengembalian)->format('d/m/Y') : '-' }}</strong>
                                        <span style="margin-left:0.5rem;color:var(--gold-dark);font-weight:600;">(Status: {{ $tk->status }})</span>
                                    </p>
                                </div>
                            </div>
                            <div style="text-align:right;">
                                @php
                                    $sisa = max(0, $tk->total_harga - ($tk->total_harga * 0.5));
                                @endphp
                                <p style="font-size:0.75rem;color:var(--maroon);font-weight:600;margin:0 0 0.5rem 0;">Sisa Pelunasan: Rp {{ number_format($sisa, 0, ',', '.') }}</p>
                                <form action="{{ route('transaksi.updateStatus', $tk->id) }}" method="POST" onsubmit="return confirm('Verifikasi pengembalian dan selesaikan pesanan?')">
                                    @csrf
                                    <input type="hidden" name="status" value="Telah Dikembalikan">
                                    <input type="hidden" name="redirect_tab" value="pengembalian">
                                    <button type="submit" class="btn btn-sm" style="background:#15803d;color:white;border:none;padding:0.5rem 1rem;font-weight:700;font-size:0.75rem;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:0.35rem;">
                                        <span>✅ Verifikasi &amp; Selesai</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="db-empty-state">
                    <div class="db-empty-icon">🔁</div>
                    <p class="db-empty-title">Tidak ada pesanan untuk diverifikasi</p>
                    <p class="db-empty-sub">Pesanan yang sedang disewa atau diajukan pengembalian oleh customer akan otomatis muncul di sini secara real-time</p>
                </div>
            @endif
        </div>
    </div>
</div>
