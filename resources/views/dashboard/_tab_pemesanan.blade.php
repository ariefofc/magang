{{-- TAB 2: Kelola Pemesanan --}}
            <div x-show="tab === 'pemesanan'">
                <div class="db-panel">
                    <div class="db-panel-header">
                        <div>
                            <span class="db-panel-badge">Component 2: Process Transaksi</span>
                            <h3 class="db-panel-title">Daftar Transaksi Persewaan</h3>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:0.72rem;color:var(--text-muted);display:block;">Pembaruan Sistem Realtime</span>
                            @if($daftarTransaksi instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                <span style="font-size:0.65rem;color:var(--text-light);">Total: {{ $daftarTransaksi->total() }} pesanan</span>
                            @endif
                        </div>
                    </div>
                    <div class="db-panel-body">

                        <!-- Info Banner -->
                        <div class="db-info-banner">
                            <span style="font-size:1rem;flex-shrink:0;">💡</span>
                            <span>Setelah melakukan konfirmasi via <strong>Live Chat Admin</strong> dengan customer, ubah status pesanan menggunakan dropdown di kolom <strong>Aksi Admin</strong>.</span>
                        </div>

                        <!-- Tabel Transaksi -->
                        <div class="db-table-wrap">
                            <table class="db-table">
                                {{-- Kolom lebar eksplisit agar tidak tumpang tindih --}}
                                <colgroup>
                                    <col style="width:16%">
                                    <col style="width:18%">
                                    <col style="width:13%">
                                    <col style="width:11%">
                                    <col style="width:13%">
                                    <col style="width:13%">
                                    <col style="width:16%">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Pelanggan &amp; Kode</th>
                                        <th>Kebaya &amp; Ukuran</th>
                                        <th>No WA &amp; Alamat</th>
                                        <th>Tgl Sewa</th>
                                        <th>Total &amp; DP</th>
                                        <th>Status</th>
                                        <th style="text-align:center;">Aksi Admin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($daftarTransaksi ?? [] as $trx)
                                        @php
                                            $noWa          = $trx->no_wa ?? '628123456789';
                                            $noWaFormatted = preg_replace('/^0/', '62', $noWa);
                                            $pesanWa       = rawurlencode("Halo {$trx->nama_user},\n\nTerima kasih telah memesan kebaya '{$trx->nama_kebaya}' di MS Harmony Boutique.\nKode: {$trx->kode_transaksi}\nStatus: {$trx->status}\n\nKami akan hubungi Anda untuk konfirmasi jadwal fitting. Terima kasih!");
                                            $statusBadge   = match($trx->status) {
                                                'Telah Dikonfirmasi'        => 'badge-dikonfirmasi',
                                                'Sedang Fitting'            => 'badge-fitting',
                                                'Kebaya Telah Diambil'      => 'badge-diambil',
                                                'Telah Dikembalikan'        => 'badge-dikembalikan',
                                                'Dibatalkan'                => 'badge-dibatalkan',
                                                'Menunggu Pembayaran DP'    => 'badge-menunggu-dp',
                                                'Menunggu Konfirmasi'       => 'badge-menunggu-konfirmasi',
                                                'Menunggu Fitting'          => 'badge-fitting',
                                                default                     => 'badge-default',
                                            };
                                            $tglSewa    = $trx->tanggal_sewa    ? \Carbon\Carbon::parse($trx->tanggal_sewa)->format('d/m/Y')    : '-';
                                            $tglKembali = $trx->tanggal_pengembalian ? \Carbon\Carbon::parse($trx->tanggal_pengembalian)->format('d/m/Y') : '-';
                                            $sudahBayar = $trx->sudah_bayar ?? false;
                                            $isHighlight = in_array($trx->status, ['Menunggu Pembayaran DP', 'Menunggu Konfirmasi Admin', 'Menunggu Fitting & Konfirmasi']);
                                        @endphp
                                        <tr class="{{ $isHighlight ? 'highlight' : '' }}">
                                            {{-- Pelanggan --}}
                                            <td>
                                                <div class="td-customer-name">{{ $trx->nama_user }}</div>
                                                <div class="td-kode">{{ $trx->kode_transaksi }}</div>
                                                <div class="td-metode">{{ $trx->metode_pembayaran }}</div>
                                                @if($sudahBayar)
                                                    <div class="dp-paid-badge">✅ DP Dibayar</div>
                                                @else
                                                    <div class="dp-unpaid-badge">⏳ Belum Bayar DP</div>
                                                @endif
                                            </td>
                                            {{-- Kebaya --}}
                                            <td>
                                                {{-- Thumbnail Gambar AI --}}
                                                @if($trx->gambar_ai_result)
                                                    @php
                                                    $imgSrc = str_starts_with($trx->gambar_ai_result, 'http')
                                                        ? $trx->gambar_ai_result
                                                        : asset($trx->gambar_ai_result);
                                                    $imgSrc = str_replace(' ', '%20', $imgSrc);
                                                @endphp
                                                    <div style="margin-bottom:0.35rem;">
                                                        <img src="{{ $imgSrc }}"
                                                             alt="AI Result"
                                                             style="width:52px;height:60px;object-fit:cover;border-radius:6px;border:1.5px solid var(--border-gold);display:block;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,0.12);transition:transform 0.15s;"
                                                             title="Klik untuk lihat detail gambar AI"
                                                             onclick="document.getElementById('ai-thumb-{{ $trx->id }}').click()"
                                                             onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                                        <span style="font-size:0.52rem;color:var(--gold-dark);display:block;margin-top:2px;">🤖 AI Custom</span>
                                                    </div>
                                                @endif
                                                <div style="font-weight:500;font-size:0.75rem;">{{ $trx->nama_kebaya }}</div>
                                                <span style="margin-top:0.25rem;display:inline-block;padding:0.15rem 0.5rem;background:var(--bg-ivory);border:1px solid var(--border-gold);border-radius:var(--radius-sm);font-family:monospace;font-size:0.6rem;">Size {{ $trx->ukuran }}</span>
                                                @if($trx->warna_custom_ai && $trx->warna_custom_ai !== 'Default')
                                                    <div style="font-size:0.6rem;color:var(--gold-dark);margin-top:0.25rem;">🎨 {{ Str::limit($trx->warna_custom_ai, 30) }}</div>
                                                @endif
                                            </td>
                                            {{-- No WA --}}
                                            <td>
                                                <a href="https://wa.me/{{ $noWaFormatted }}" target="_blank"
                                                   style="font-family:monospace;font-size:0.72rem;color:#15803d;font-weight:600;display:block;text-decoration:underline;">
                                                    📱 {{ $trx->no_wa ?? '-' }}
                                                </a>
                                                <div style="font-size:0.65rem;color:var(--text-medium);margin-top:0.25rem;max-width:130px;line-height:1.4;" title="{{ $trx->alamat ?? '-' }}">
                                                    📍 {{ $trx->alamat ? Str::limit($trx->alamat, 50) : '— tidak ada alamat —' }}
                                                </div>
                                            </td>
                                            {{-- Tanggal --}}
                                            <td>
                                                <div style="font-size:0.72rem;"><span style="color:var(--text-light);">Ambil:</span> {{ $tglSewa }}</div>
                                                <div style="font-size:0.72rem;"><span style="color:var(--text-light);">Kembali:</span> {{ $tglKembali }}</div>
                                                @if($trx->durasi_hari)
                                                    <div style="font-size:0.6rem;color:var(--text-light);">{{ $trx->durasi_hari }} hari</div>
                                                @endif
                                            </td>
                                            {{-- Total --}}
                                            <td>
                                                <div class="td-harga">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</div>
                                                @php
                                                    $skema = $trx->skema_bayar ?? 'DP 50%';
                                                    $pctDP = str_contains($skema, '70') ? 0.7 : (str_contains($skema, '100') || str_contains($skema, 'Lunas') ? 1.0 : 0.5);
                                                    $nominalDP = $trx->total_harga * $pctDP;
                                                    $sisa = $trx->total_harga - $nominalDP;
                                                @endphp
                                                <div class="td-dp">{{ $skema }}: Rp {{ number_format($nominalDP, 0, ',', '.') }}</div>
                                                @if($sisa > 0)
                                                    <div class="td-sisa">Sisa: Rp {{ number_format($sisa, 0, ',', '.') }}</div>
                                                @else
                                                    <div class="td-sisa" style="color:#16a34a;">Lunas 100%</div>
                                                @endif
                                            </td>
                                            {{-- Status --}}
                                            <td>
                                                <span class="status-badge {{ $statusBadge }}">{{ $trx->status ?? 'Menunggu' }}</span>
                                                @if($trx->status === 'Telah Dikonfirmasi')
                                                    <div style="margin-top:0.25rem;font-size:0.6rem;color:#1d4ed8;font-weight:600;">✅ Admin telah konfirmasi</div>
                                                @elseif($trx->status === 'Kebaya Telah Diambil')
                                                    <div style="margin-top:0.25rem;font-size:0.6rem;color:#c2410c;font-weight:600;">📦 Kebaya sudah diambil</div>
                                                @elseif($trx->status === 'Telah Dikembalikan')
                                                    <div style="margin-top:0.25rem;font-size:0.6rem;color:#166534;font-weight:600;">🔁 Proses selesai</div>
                                                @endif
                                            </td>
                                            {{-- ══ AKSI ADMIN — Compact 3-row design ══ --}}
                                            <td class="db-action-col">
                                                @php
                                                    /* ── URL gambar: encode spasi agar browser bisa load file bernamakan spasi ── */
                                                    if ($trx->gambar_ai_result) {
                                                        $rawGambar = str_starts_with($trx->gambar_ai_result, 'http')
                                                            ? $trx->gambar_ai_result
                                                            : asset($trx->gambar_ai_result);
                                                        $trxGambar = str_replace(' ', '%20', $rawGambar);
                                                    } else {
                                                        $trxGambar = null;
                                                    }
                                                    /* JSON untuk modal — gunakan base64 agar aman dari konflik HTML encoding */
                                                    $trxDataJson = base64_encode(json_encode([
                                                        'id'               => $trx->id,
                                                        'kode_transaksi'   => $trx->kode_transaksi,
                                                        'nama_user'        => $trx->nama_user,
                                                        'no_wa'            => $trx->no_wa,
                                                        'nama_kebaya'      => $trx->nama_kebaya,
                                                        'ukuran'           => $trx->ukuran,
                                                        'warna_custom_ai'  => $trx->warna_custom_ai ?? 'Default',
                                                        'gambar_ai_result' => $trxGambar,
                                                        'alamat'           => $trx->alamat ?? '-',
                                                        'status'           => $trx->status,
                                                        'metode_pembayaran'=> $trx->metode_pembayaran,
                                                        'skema_bayar'      => $trx->skema_bayar ?? 'DP 50%',
                                                        'total_harga'      => number_format($trx->total_harga, 0, ',', '.'),
                                                        'tanggal_sewa'     => $tglSewa,
                                                        'tanggal_pengembalian' => $tglKembali,
                                                        'durasi_hari'      => $trx->durasi_hari,
                                                        'sudah_bayar'      => $trx->sudah_bayar ? true : false,
                                                        'catatan_admin'    => $trx->catatan_admin ?? '',
                                                    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

                                                    /* ── Pesan WA kontekstual ── */
                                                    $noWaAksi    = preg_replace('/^0/', '62', $trx->no_wa ?? '628123456789');
                                                    $tglSewaAksi = $trx->tanggal_sewa ? \Carbon\Carbon::parse($trx->tanggal_sewa)->format('d/m/Y') : '-';
                                                    if (in_array($trx->status, ['Telah Dikonfirmasi','Sedang Fitting'])) {
                                                        $pesanWaAksi  = rawurlencode("Halo Kak {$trx->nama_user} 👋\n\nKami dari Admin *MS Harmony* ingin konfirmasi jadwal *Fitting Kebaya* Anda.\n\n📌 Detail:\n• Kebaya : {$trx->nama_kebaya}\n• Ukuran : {$trx->ukuran}\n• Kode   : {$trx->kode_transaksi}\n• Tgl    : {$tglSewaAksi}\n\nMohon konfirmasi waktu fitting. Terima kasih 🙏✨");
                                                        $labelWaAksi  = '💬 WA Fitting';
                                                        $colorWaAksi  = '#1d4ed8';
                                                    } elseif ($trx->status === 'Kebaya Telah Diambil') {
                                                        $pesanWaAksi  = rawurlencode("Halo Kak {$trx->nama_user} 👋\n\nKebaya *{$trx->nama_kebaya}* Anda telah berhasil diambil.\nKode: {$trx->kode_transaksi}\n\n📋 Ingat: pelunasan saat pengembalian, denda Rp50rb/hari jika terlambat. 🙏");
                                                        $labelWaAksi  = '📦 WA Ambil';
                                                        $colorWaAksi  = '#c2410c';
                                                    } else {
                                                        $pesanWaAksi  = null;
                                                        $labelWaAksi  = '💬 Chat WA';
                                                        $colorWaAksi  = '#15803d';
                                                    }
                                                @endphp

                                                <div class="db-action-group">

                                                    {{-- ┌ BARIS 1: [Konfirmasi] ┐ --}}
                                                    <div class="db-action-row">
                                                        @if(in_array($trx->status, ['Menunggu Fitting','Menunggu Konfirmasi','Menunggu Pembayaran DP']))
                                                            <form action="{{ route('transaksi.updateStatus', $trx->id) }}" method="POST" style="flex:1;">
                                                                @csrf
                                                                <input type="hidden" name="status" value="Telah Dikonfirmasi">
                                                                <button type="submit" class="db-btn db-btn-confirm" title="Validasi &amp; Konfirmasi">✅ Konfirmasi</button>
                                                            </form>
                                                        @else
                                                            <span class="db-btn db-btn-confirm-done" title="Sudah dikonfirmasi">
                                                                @if($trx->status === 'Telah Dikembalikan') ✔ Selesai
                                                                @elseif($trx->status === 'Dibatalkan') ✖ Batal
                                                                @else ✔ Terkonfirmasi @endif
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- ┌ BARIS 2: Dropdown Status + Simpan ┐ --}}
                                                    <form action="{{ route('transaksi.updateStatus', $trx->id) }}" method="POST" class="db-action-status-form">
                                                        @csrf
                                                        <select name="status" class="db-status-select">
                                                            <option value="Menunggu Pembayaran DP"  {{ $trx->status==='Menunggu Pembayaran DP'?'selected':'' }}>⏳ Menunggu Bayar DP</option>
                                                            <option value="Menunggu Konfirmasi"     {{ $trx->status==='Menunggu Konfirmasi'?'selected':'' }}>🔔 Menunggu Konfirmasi</option>
                                                            <option value="Menunggu Fitting"        {{ $trx->status==='Menunggu Fitting'?'selected':'' }}>📅 Menunggu Fitting</option>
                                                            <option value="Telah Dikonfirmasi"      {{ $trx->status==='Telah Dikonfirmasi'?'selected':'' }}>✅ Dikonfirmasi</option>
                                                            <option value="Sedang Fitting"          {{ $trx->status==='Sedang Fitting'?'selected':'' }}>✂️ Sedang Fitting</option>
                                                            <option value="Kebaya Telah Diambil"    {{ $trx->status==='Kebaya Telah Diambil'?'selected':'' }}>📦 Telah Diambil</option>
                                                            <option value="Telah Dikembalikan"      {{ $trx->status==='Telah Dikembalikan'?'selected':'' }}>🔁 Dikembalikan</option>
                                                            <option value="Dibatalkan"              {{ $trx->status==='Dibatalkan'?'selected':'' }}>❌ Dibatalkan</option>
                                                        </select>
                                                        <button type="submit" class="db-btn-save" title="Simpan Status">✓</button>
                                                    </form>

                                                    {{-- ┌ BARIS 3: Live Chat + Nota + Hapus ┐ --}}
                                                    <div class="db-action-row">
                                                        <a href="{{ route('admin.chat.inbox', ['code' => $trx->kode_transaksi]) }}"
                                                           class="db-btn" style="background:#8B5A2B;color:#ffffff;font-weight:600;padding:0.35rem 0.6rem;font-size:0.72rem;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:0.2rem;" title="Live Chat Admin">
                                                            💬 Live Chat
                                                        </a>

                                                        <a href="{{ route('admin.nota', $trx->id) }}" target="_blank"
                                                           class="db-btn db-btn-nota" title="Cetak Nota Admin">
                                                            🖨 Nota
                                                        </a>

                                                        <form action="{{ route('transaksi.destroy', $trx->id) }}" method="POST"
                                                              onsubmit="return confirm('Hapus pesanan {{ addslashes($trx->nama_user) }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="db-btn db-btn-hapus" title="Hapus Pesanan">
                                                                🗑
                                                            </button>
                                                        </form>
                                                    </div>

                                                </div>{{-- end db-action-group --}}
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                <div class="db-empty-state">
                                                    <div class="db-empty-icon">📋</div>
                                                    <p class="db-empty-title">Belum ada data pemesanan</p>
                                                    <p class="db-empty-sub">Pesanan dari customer akan muncul di sini secara otomatis</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>{{-- end db-table-wrap --}}


                        {{-- ═══════════════════════════════════════════════════
                             MOBILE CARD VIEW — Tampil di HP (≤768px)
                             Tabel disembunyikan, kartu ini ditampilkan via CSS
                             ═══════════════════════════════════════════════════ --}}
                        <div class="db-order-cards">
                            @forelse($daftarTransaksi ?? [] as $trx)
                                @php
                                    /* ── Recompute helpers (sama seperti di tabel) ── */
                                    $noWaC          = $trx->no_wa ?? '628123456789';
                                    $noWaFmtC       = preg_replace('/^0/', '62', $noWaC);
                                    $sudahBayarC    = $trx->sudah_bayar ?? false;
                                    $isHighlightC   = in_array($trx->status, ['Menunggu Pembayaran DP', 'Menunggu Konfirmasi Admin', 'Menunggu Fitting & Konfirmasi']);
                                    $tglSewaC       = $trx->tanggal_sewa    ? \Carbon\Carbon::parse($trx->tanggal_sewa)->format('d/m/Y')    : '-';
                                    $tglKembaliC    = $trx->tanggal_pengembalian ? \Carbon\Carbon::parse($trx->tanggal_pengembalian)->format('d/m/Y') : '-';
                                    $statusBadgeC   = match($trx->status) {
                                        'Telah Dikonfirmasi'     => 'badge-dikonfirmasi',
                                        'Sedang Fitting'         => 'badge-fitting',
                                        'Kebaya Telah Diambil'   => 'badge-diambil',
                                        'Telah Dikembalikan'     => 'badge-dikembalikan',
                                        'Dibatalkan'             => 'badge-dibatalkan',
                                        'Menunggu Pembayaran DP' => 'badge-menunggu-dp',
                                        'Menunggu Konfirmasi'    => 'badge-menunggu-konfirmasi',
                                        'Menunggu Fitting'       => 'badge-fitting',
                                        default                  => 'badge-default',
                                    };
                                    $skemaC    = $trx->skema_bayar ?? 'DP 50%';
                                    $pctDPC    = str_contains($skemaC,'70') ? 0.7 : (str_contains($skemaC,'100')||str_contains($skemaC,'Lunas') ? 1.0 : 0.5);
                                    $nomDPC    = $trx->total_harga * $pctDPC;
                                    $sisaC     = $trx->total_harga - $nomDPC;
                                    $imgSrcC   = $trx->gambar_ai_result
                                        ? (str_starts_with($trx->gambar_ai_result,'http') ? $trx->gambar_ai_result : asset($trx->gambar_ai_result))
                                        : null;
                                    /* WA aksi per status */
                                    $noWaAksiC  = preg_replace('/^0/','62', $trx->no_wa ?? '628123456789');
                                    $tglSewaAksiC = $trx->tanggal_sewa ? \Carbon\Carbon::parse($trx->tanggal_sewa)->format('d/m/Y') : '-';
                                    if (in_array($trx->status,['Telah Dikonfirmasi','Sedang Fitting'])) {
                                        $pesanWaC = rawurlencode("Halo Kak {$trx->nama_user} 👋\n\nKami dari Admin *MS Harmony* ingin konfirmasi jadwal *Fitting Kebaya* Anda.\n• Kebaya : {$trx->nama_kebaya}\n• Kode   : {$trx->kode_transaksi}\n• Tgl    : {$tglSewaAksiC}\n\nMohon konfirmasi waktu fitting. Terima kasih 🙏✨");
                                        $labelWaC = '💬 WA Fitting';
                                    } elseif ($trx->status === 'Kebaya Telah Diambil') {
                                        $pesanWaC = rawurlencode("Halo Kak {$trx->nama_user} 👋\n\nKebaya Anda (*{$trx->nama_kebaya}*) telah berhasil diambil.\n• Kode: {$trx->kode_transaksi}\n\nIngat: pelunasan saat pengembalian, denda Rp50rb/hari jika telat. 🙏");
                                        $labelWaC = '📦 WA Ambil';
                                    } else {
                                        $pesanWaC = null;
                                        $labelWaC = null;
                                    }
                                    /* JSON data untuk modal detail */
                                    $trxGambarC   = $imgSrcC;
                                    $trxDataJsonC = json_encode([
                                        'id'               => $trx->id,
                                        'kode_transaksi'   => $trx->kode_transaksi,
                                        'nama_user'        => $trx->nama_user,
                                        'no_wa'            => $trx->no_wa,
                                        'nama_kebaya'      => $trx->nama_kebaya,
                                        'ukuran'           => $trx->ukuran,
                                        'warna_custom_ai'  => $trx->warna_custom_ai ?? 'Default',
                                        'gambar_ai_result' => $trxGambarC,
                                        'alamat'           => $trx->alamat ?? '-',
                                        'status'           => $trx->status,
                                        'metode_pembayaran'=> $trx->metode_pembayaran,
                                        'skema_bayar'      => $trx->skema_bayar ?? 'DP 50%',
                                        'total_harga'      => number_format($trx->total_harga,0,',','.'),
                                        'tanggal_sewa'     => $tglSewaC,
                                        'tanggal_pengembalian' => $tglKembaliC,
                                        'durasi_hari'      => $trx->durasi_hari,
                                        'sudah_bayar'      => $trx->sudah_bayar ? true : false,
                                        'catatan_admin'    => $trx->catatan_admin ?? '',
                                    ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE);
                                @endphp

                                <div class="ocard {{ $isHighlightC ? 'highlight' : '' }}">

                                    {{-- ── HEADER KARTU: Nama Customer + Status ── --}}
                                    <div class="ocard-header">
                                        <div class="ocard-customer">
                                            <div class="ocard-customer-name">{{ $trx->nama_user }}</div>
                                            <div class="ocard-kode">{{ $trx->kode_transaksi }}</div>
                                            <div style="margin-top:0.25rem;display:flex;gap:0.4rem;flex-wrap:wrap;align-items:center;">
                                                <div class="{{ $sudahBayarC ? 'dp-paid-badge' : 'dp-unpaid-badge' }}">
                                                    {{ $sudahBayarC ? '✅ DP Dibayar' : '⏳ Belum Bayar DP' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ocard-status-wrap">
                                            <span class="status-badge {{ $statusBadgeC }}">{{ $trx->status }}</span>
                                        </div>
                                    </div>

                                    {{-- ── BODY KARTU: Info Grid ── --}}
                                    <div class="ocard-body">

                                        {{-- Baris: Kebaya + Thumbnail AI ── --}}
                                        <div class="ocard-kebaya-row">
                                            @if($imgSrcC)
                                                <img src="{{ $imgSrcC }}"
                                                     alt="AI Result"
                                                     class="ocard-thumb"
                                                     title="Klik lihat detail gambar AI"
                                                     @click="detailTrx = {{ $trxDataJsonC }}; detailModal = true">
                                            @else
                                                <div style="width:44px;height:52px;border-radius:6px;background:rgba(200,169,110,0.1);border:1.5px dashed var(--border-gold);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">🪡</div>
                                            @endif
                                            <div class="ocard-kebaya-info">
                                                <div class="ocard-kebaya-name">{{ $trx->nama_kebaya }}</div>
                                                <div style="margin-top:0.2rem;">
                                                    <span style="padding:0.12rem 0.45rem;background:var(--bg-ivory);border:1px solid var(--border-gold);border-radius:4px;font-family:monospace;font-size:0.6rem;">Size {{ $trx->ukuran }}</span>
                                                </div>
                                                @if($trx->warna_custom_ai && $trx->warna_custom_ai !== 'Default')
                                                    <div style="font-size:0.6rem;color:var(--gold-dark);margin-top:0.2rem;">🎨 {{ Str::limit($trx->warna_custom_ai,28) }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- No WA --}}
                                        <div class="ocard-field">
                                            <span class="ocard-label">📱 No WA</span>
                                            <a href="https://wa.me/{{ $noWaFmtC }}" target="_blank" class="ocard-value green mono">{{ $trx->no_wa }}</a>
                                        </div>

                                        {{-- Metode Bayar --}}
                                        <div class="ocard-field">
                                            <span class="ocard-label">💳 Metode</span>
                                            <span class="ocard-value">{{ $trx->metode_pembayaran }}</span>
                                        </div>

                                        {{-- Tanggal Sewa --}}
                                        <div class="ocard-field">
                                            <span class="ocard-label">📅 Tgl Ambil</span>
                                            <span class="ocard-value">{{ $tglSewaC }}</span>
                                        </div>

                                        {{-- Tanggal Kembali --}}
                                        <div class="ocard-field">
                                            <span class="ocard-label">🔁 Tgl Kembali</span>
                                            <span class="ocard-value">{{ $tglKembaliC }} <span style="font-size:0.62rem;color:var(--text-muted);">({{ $trx->durasi_hari }}h)</span></span>
                                        </div>

                                        {{-- Total Harga --}}
                                        <div class="ocard-field">
                                            <span class="ocard-label">💰 Total</span>
                                            <span class="ocard-value maroon">Rp {{ number_format($trx->total_harga,0,',','.') }}</span>
                                        </div>

                                        {{-- DP / Sisa --}}
                                        <div class="ocard-field">
                                            <span class="ocard-label">💵 {{ $skemaC }}</span>
                                            <span class="ocard-value" style="color:#166534;">Rp {{ number_format($nomDPC,0,',','.') }}</span>
                                            @if($sisaC > 0)
                                                <span style="font-size:0.62rem;color:#92400e;display:block;">Sisa: Rp {{ number_format($sisaC,0,',','.') }}</span>
                                            @else
                                                <span style="font-size:0.62rem;color:#16a34a;display:block;font-weight:700;">Lunas 100%</span>
                                            @endif
                                        </div>

                                        {{-- Alamat (full width) --}}
                                        @if($trx->alamat)
                                        <div class="ocard-field" style="grid-column:1/-1;">
                                            <span class="ocard-label">📍 Alamat</span>
                                            <span class="ocard-value" style="font-size:0.7rem;color:var(--text-medium);">{{ $trx->alamat }}</span>
                                        </div>
                                        @endif

                                    </div>{{-- end ocard-body --}}

                                    {{-- ── FOOTER KARTU: Semua Tombol Aksi Admin ── --}}
                                    <div class="ocard-footer">
                                        <span class="ocard-footer-label">⚙️ Aksi Admin</span>
                                        <div class="ocard-actions">

                                            {{-- Tombol Lihat Detail & Gambar AI --}}
                                            <button @click="detailTrx = {{ $trxDataJsonC }}; detailModal = true"
                                                    style="width:100%;font-size:0.65rem;font-weight:700;padding:0.5rem 0.75rem;background:linear-gradient(135deg,rgba(212,175,55,0.18),rgba(139,0,0,0.1));border:1px solid rgba(212,175,55,0.45);border-radius:8px;color:var(--maroon);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.3rem;">
                                                👁️ Lihat Detail &amp; Gambar AI
                                            </button>

                                            {{-- Validasi Konfirmasi (jika status menunggu) --}}
                                            @if(in_array($trx->status,['Menunggu Fitting','Menunggu Konfirmasi','Menunggu Pembayaran DP']))
                                            <form action="{{ route('transaksi.updateStatus', $trx->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="status" value="Telah Dikonfirmasi">
                                                <button type="submit" class="db-confirm-btn">✅ VALIDASI &amp; KONFIRMASI</button>
                                            </form>
                                            @endif

                                            {{-- Dropdown Ubah Status --}}
                                            <form action="{{ route('transaksi.updateStatus', $trx->id) }}" method="POST" style="display:flex;gap:0.35rem;">
                                                @csrf
                                                <select name="status" class="db-status-select" style="flex:1;">
                                                    <option value="Menunggu Pembayaran DP"  {{ $trx->status==='Menunggu Pembayaran DP'?'selected':'' }}>⏳ Menunggu Pembayaran DP</option>
                                                    <option value="Menunggu Konfirmasi"     {{ $trx->status==='Menunggu Konfirmasi'?'selected':'' }}>🔔 Menunggu Konfirmasi</option>
                                                    <option value="Menunggu Fitting"        {{ $trx->status==='Menunggu Fitting'?'selected':'' }}>📅 Menunggu Fitting</option>
                                                    <option value="Telah Dikonfirmasi"      {{ $trx->status==='Telah Dikonfirmasi'?'selected':'' }}>✅ Telah Dikonfirmasi</option>
                                                    <option value="Sedang Fitting"          {{ $trx->status==='Sedang Fitting'?'selected':'' }}>✂️ Sedang Fitting</option>
                                                    <option value="Kebaya Telah Diambil"    {{ $trx->status==='Kebaya Telah Diambil'?'selected':'' }}>📦 Kebaya Telah Diambil</option>
                                                    <option value="Telah Dikembalikan"      {{ $trx->status==='Telah Dikembalikan'?'selected':'' }}>🔁 Telah Dikembalikan</option>
                                                    <option value="Dibatalkan"              {{ $trx->status==='Dibatalkan'?'selected':'' }}>❌ Dibatalkan</option>
                                                </select>
                                                <button type="submit" class="db-save-btn" title="Simpan Status">✓</button>
                                            </form>

                                            {{-- Baris 2 tombol: Nota + Live Chat --}}
                                            <div class="ocard-action-row">
                                                <a href="{{ route('admin.nota', $trx->id) }}" target="_blank"
                                                   style="text-align:center;padding:0.45rem 0.5rem;background:#292524;color:#fff;border-radius:7px;font-size:0.6rem;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:0.25rem;">
                                                    🖨️ Cetak Nota
                                                </a>
                                                <a href="{{ route('admin.chat.inbox', ['code' => $trx->kode_transaksi]) }}"
                                                   style="text-align:center;padding:0.45rem 0.5rem;background:#8B5A2B;color:#fff;border-radius:7px;font-size:0.6rem;font-weight:700;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:0.25rem;">
                                                    💬 Live Chat
                                                </a>
                                            </div>

                                            {{-- Hapus --}}
                                            <form action="{{ route('transaksi.destroy', $trx->id) }}" method="POST"
                                                  onsubmit="return confirm('Hapus data pesanan {{ $trx->nama_user }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="db-delete-btn">🗑️ Hapus Pesanan Ini</button>
                                            </form>

                                        </div>{{-- end ocard-actions --}}
                                    </div>{{-- end ocard-footer --}}

                                </div>{{-- end .ocard --}}
                            @empty
                                <div class="db-empty-state" style="border:1px dashed var(--border-gold);border-radius:12px;">
                                    <div class="db-empty-icon">📋</div>
                                    <p class="db-empty-title">Belum ada data pemesanan</p>
                                    <p class="db-empty-sub">Pesanan dari customer akan muncul di sini</p>
                                </div>
                            @endforelse
                        </div>{{-- end db-order-cards --}}

                        {{-- Pagination --}}
                        @if($daftarTransaksi instanceof \Illuminate\Pagination\LengthAwarePaginator && $daftarTransaksi->hasPages())
                            <div class="db-pagination">
                                <span class="db-pagination-info">
                                    Menampilkan {{ $daftarTransaksi->firstItem() }}–{{ $daftarTransaksi->lastItem() }} dari {{ $daftarTransaksi->total() }} pesanan
                                </span>
                                <div class="db-pagination-btns">
                                    @if($daftarTransaksi->onFirstPage())
                                        <span class="db-page-btn" style="opacity:0.3;cursor:not-allowed;">← Prev</span>
                                    @else
                                        <a href="{{ $daftarTransaksi->previousPageUrl() }}&tab=pemesanan" class="db-page-btn">← Prev</a>
                                    @endif

                                    @foreach($daftarTransaksi->getUrlRange(1, $daftarTransaksi->lastPage()) as $page => $url)
                                        <a href="{{ $url }}&tab=pemesanan"
                                           class="db-page-num-btn {{ $page == $daftarTransaksi->currentPage() ? 'active' : '' }}">
                                            {{ $page }}
                                        </a>
                                    @endforeach

                                    @if($daftarTransaksi->hasMorePages())
                                        <a href="{{ $daftarTransaksi->nextPageUrl() }}&tab=pemesanan" class="db-page-btn">Next →</a>
                                    @else
                                        <span class="db-page-btn" style="opacity:0.3;cursor:not-allowed;">Next →</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
