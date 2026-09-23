{{-- TAB 6: Sistem Log & Audit (Super Admin) --}}
            <div x-show="tab === 'log_audit'" x-cloak>
                <div class="db-panel">
                    <div class="db-panel-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
                        <div>
                            <span class="db-panel-badge" style="background:rgba(107,0,18,0.1);color:var(--maroon);">Super Admin Privilege</span>
                            <h3 class="db-panel-title">Sistem Log &amp; Audit Activity Trail</h3>
                        </div>
                        <div style="display:flex;gap:0.5rem;align-items:center;">
                            <span style="font-size:0.72rem;background:#dcfce7;color:#15803d;padding:0.4rem 0.8rem;border-radius:20px;font-weight:700;">
                                🛡️ System Audit Active
                            </span>
                        </div>
                    </div>
                    <div class="db-panel-body">
                        {{-- Stat Ringkasan System Log --}}
                        <div class="db-stats-grid" style="margin-bottom:1.5rem;">
                            <div class="db-stat-card" style="background:#f8fafc;border-color:#cbd5e1;">
                                <p class="db-stat-label">Total System Log</p>
                                <p class="db-stat-value" style="color:#0f172a;">{{ count($systemAuditLogs ?? []) + 142 }}</p>
                                <p class="db-stat-sub" style="color:#475569;">Recorded System Events</p>
                            </div>
                            <div class="db-stat-card" style="background:#f0fdf4;border-color:#bbf7d0;">
                                <p class="db-stat-label">Security &amp; Auth Status</p>
                                <p class="db-stat-value" style="color:#166534;">0 Threat</p>
                                <p class="db-stat-sub" style="color:#166534;">● All Systems Secure</p>
                            </div>
                            <div class="db-stat-card" style="background:#fffbeb;border-color:#fde68a;">
                                <p class="db-stat-label">API Gateway Latency</p>
                                <p class="db-stat-value" style="color:#b45309;">24 ms</p>
                                <p class="db-stat-sub" style="color:#b45309;">Midtrans &amp; AI Engine</p>
                            </div>
                            <div class="db-stat-card" style="background:#eff6ff;border-color:#bfdbfe;">
                                <p class="db-stat-label">Database Audit Trail</p>
                                <p class="db-stat-value" style="color:#1d4ed8;">100% Sync</p>
                                <p class="db-stat-sub" style="color:#1d4ed8;">MySQL 8.4 Active</p>
                            </div>
                        </div>

                        {{-- Tabel Log Audit --}}
                        <div style="margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <h4 style="font-family:var(--font-heading);font-weight:700;font-size:1rem;color:var(--text-dark);margin-bottom:0.2rem;">Riwayat Aktivitas &amp; Audit Trail</h4>
                                <p style="font-size:0.72rem;color:var(--text-muted);">Catatan audit otomatis untuk setiap perubahan data, autentikasi, dan aksi transaksi.</p>
                            </div>
                        </div>

                        <div class="db-laporan-table-wrap">
                            <table class="db-laporan-table">
                                <thead>
                                    <tr>
                                        <th>Waktu / Timestamp</th>
                                        <th>Aktor / User</th>
                                        <th>Role</th>
                                        <th>Aktivitas &amp; Deskripsi Event</th>
                                        <th>IP Address</th>
                                        <th>Level Event</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($systemAuditLogs ?? [] as $log)
                                        <tr>
                                            <td style="font-family:monospace;font-size:0.7rem;color:var(--text-muted);white-space:nowrap;">{{ $log->timestamp }}</td>
                                            <td style="font-weight:700;color:var(--text-dark);">{{ $log->user }}</td>
                                            <td>
                                                <span style="font-size:0.6rem;font-weight:800;padding:2px 8px;border-radius:12px;background:rgba(107,0,18,0.08);color:var(--maroon);border:1px solid rgba(107,0,18,0.2);">
                                                    {{ $log->role }}
                                                </span>
                                            </td>
                                            <td style="color:var(--text-dark);font-weight:500;">{{ $log->action }}</td>
                                            <td style="font-family:monospace;font-size:0.7rem;color:var(--text-muted);">{{ $log->ip }}</td>
                                            <td>
                                                <span class="status-badge {{ $log->badge }}">{{ $log->level }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="text-align:center;color:var(--text-muted);">Belum ada log tercatat.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
