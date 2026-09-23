{{-- TAB 7: Konfigurasi API & AI (Super Admin) --}}
            <div x-show="tab === 'konfigurasi_api'" x-cloak>
                <div class="db-panel">
                    <div class="db-panel-header">
                        <div>
                            <span class="db-panel-badge" style="background:rgba(107,0,18,0.1);color:var(--maroon);">Super Admin Privilege</span>
                            <h3 class="db-panel-title">Konfigurasi API Gateway &amp; Engine AI</h3>
                        </div>
                    </div>
                    <div class="db-panel-body">
                        
                        @if(session('success'))
                            <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#15803d;padding:0.85rem 1rem;border-radius:10px;font-size:0.78rem;font-weight:700;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.5rem;">
                                <span>✅</span> {{ session('success') }}
                            </div>
                        @endif

                        {{-- Service Status Bar --}}
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:1rem;margin-bottom:1.5rem;">
                            <div style="background:#fff;border:1px solid rgba(212,175,55,0.4);border-radius:10px;padding:1rem;display:flex;align-items:center;gap:0.85rem;">
                                <div style="font-size:1.75rem;">💳</div>
                                <div>
                                    <p style="font-size:0.6rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin:0;">Payment Gateway</p>
                                    <h4 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0.1rem 0;">Midtrans Snap API</h4>
                                    <span style="font-size:0.62rem;color:#15803d;font-weight:700;">● Active (Sandbox)</span>
                                </div>
                            </div>

                            <div style="background:#fff;border:1px solid rgba(212,175,55,0.4);border-radius:10px;padding:1rem;display:flex;align-items:center;gap:0.85rem;">
                                <div style="font-size:1.75rem;">🎨</div>
                                <div>
                                    <p style="font-size:0.6rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin:0;">Virtual Try-On Engine</p>
                                    <h4 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0.1rem 0;">Pix2Pix GAN Recolor Engine</h4>
                                    <span style="font-size:0.62rem;color:#15803d;font-weight:700;">● Active (Pix2Pix GAN Model)</span>
                                </div>
                            </div>

                            <div style="background:#fff;border:1px solid rgba(212,175,55,0.4);border-radius:10px;padding:1rem;display:flex;align-items:center;gap:0.85rem;">
                                <div style="font-size:1.75rem;">📱</div>
                                <div>
                                    <p style="font-size:0.6rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin:0;">QRIS Gateway</p>
                                    <h4 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0.1rem 0;">QRIS Mayangsari</h4>
                                    <span style="font-size:0.62rem;color:#15803d;font-weight:700;">● Active (qris_mayangsari.jpeg)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Form Settings --}}
                        <form action="{{ route('admin.config.save') }}" method="POST">
                            @csrf
                            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:1.5rem;">
                                
                                {{-- Card 1: Midtrans Config --}}
                                <div style="background:#fff;border:1px solid var(--border-gold);border-radius:12px;padding:1.25rem;">
                                    <h4 style="font-family:var(--font-heading);font-weight:700;font-size:0.95rem;color:var(--maroon);margin-bottom:1rem;display:flex;align-items:center;gap:0.4rem;">
                                        💳 Midtrans Payment Gateway API
                                    </h4>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">Midtrans Server Key</label>
                                        <input type="text" name="midtrans_server_key" value="{{ env('MIDTRANS_SERVER_KEY') }}" placeholder="Server Key dari Dashboard Midtrans"
                                               style="width:100%;border:1.5px solid rgba(200,169,110,0.4);border-radius:8px;padding:0.55rem 0.75rem;font-size:0.78rem;font-family:monospace;">
                                    </div>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">Midtrans Client Key</label>
                                        <input type="text" name="midtrans_client_key" value="{{ env('MIDTRANS_CLIENT_KEY') }}" placeholder="Client Key dari Dashboard Midtrans"
                                               style="width:100%;border:1.5px solid rgba(200,169,110,0.4);border-radius:8px;padding:0.55rem 0.75rem;font-size:0.78rem;font-family:monospace;">
                                    </div>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">Environment Mode</label>
                                        <select name="midtrans_is_production" style="width:100%;border:1.5px solid rgba(200,169,110,0.4);border-radius:8px;padding:0.55rem 0.75rem;font-size:0.78rem;">
                                            <option value="false" {{ env('MIDTRANS_IS_PRODUCTION') ? '' : 'selected' }}>Sandbox (Testing)</option>
                                            <option value="true" {{ env('MIDTRANS_IS_PRODUCTION') ? 'selected' : '' }}>Production (Live Real Money)</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Card 2: AI Recolor Engine Config --}}
                                <div style="background:#fff;border:1px solid var(--border-gold);border-radius:12px;padding:1.25rem;">
                                    <h4 style="font-family:var(--font-heading);font-weight:700;font-size:0.95rem;color:var(--maroon);margin-bottom:1rem;display:flex;align-items:center;gap:0.4rem;">
                                        🎨 Pix2Pix GAN AI Recolor Engine Settings
                                    </h4>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">AI Model Service Model</label>
                                        <select name="ai_model" style="width:100%;border:1.5px solid rgba(200,169,110,0.4);border-radius:8px;padding:0.55rem 0.75rem;font-size:0.78rem;">
                                            <option value="pix2pix-gan-v2" selected>Pix2Pix GAN Image-to-Image (High Precision)</option>
                                            <option value="pix2pix-gan-v1">Pix2Pix GAN Standard Model (Fast)</option>
                                        </select>
                                    </div>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">Max Image Resolution Processing</label>
                                        <input type="text" name="ai_max_res" value="2048 x 2048 px (Full HD)"
                                               style="width:100%;border:1.5px solid rgba(200,169,110,0.4);border-radius:8px;padding:0.55rem 0.75rem;font-size:0.78rem;">
                                    </div>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">Fallback Recolor Mode</label>
                                        <select name="ai_fallback" style="width:100%;border:1.5px solid rgba(200,169,110,0.4);border-radius:8px;padding:0.55rem 0.75rem;font-size:0.78rem;">
                                            <option value="canvas_hsl" selected>HTML5 Canvas Color Matrix (Client-Side Instant)</option>
                                            <option value="python_opencv">Python OpenCV HSV Pipeline (Server-Side)</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Card 3: QRIS Mayangsari Statis Config --}}
                                <div style="background:#fff;border:1px solid var(--border-gold);border-radius:12px;padding:1.25rem;">
                                    <h4 style="font-family:var(--font-heading);font-weight:700;font-size:0.95rem;color:var(--maroon);margin-bottom:1rem;display:flex;align-items:center;gap:0.4rem;">
                                        📱 QRIS Mayangsari &amp; DANA Bisnis Config
                                    </h4>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">Nama Merchant QRIS</label>
                                        <input type="text" name="qris_merchant_name" value="{{ env('QRIS_MERCHANT_NAME', config('qris.merchant_name', 'Mayangsari Kebaya')) }}"
                                               style="width:100%;border:1.5px solid rgba(200,169,110,0.4);border-radius:8px;padding:0.55rem 0.75rem;font-size:0.78rem;" required>
                                    </div>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">Gambar QRIS Aktif di Public Folder</label>
                                        <div style="display:flex;align-items:center;gap:0.75rem;background:#fdfbf7;border:1px solid rgba(212,175,55,0.3);padding:0.5rem;border-radius:8px;">
                                            <img src="{{ asset('qris_mayangsari.jpeg') }}" alt="QRIS Mayangsari" style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                            <div>
                                                <div style="font-weight:700;font-size:0.72rem;color:var(--text-dark);">qris_mayangsari.jpeg</div>
                                                <div style="font-size:0.62rem;color:#15803d;font-weight:600;">✓ Terdeteksi & Terpasang di public/</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="margin-bottom:1rem;">
                                        <label style="display:block;font-size:0.7rem;font-weight:700;color:var(--text-dark);margin-bottom:0.35rem;">Raw String QRIS (EMVCo) - Opsional</label>
                                        <textarea name="qris_static_string" rows="2" placeholder="00020101021126..."
                                                  style="width:100%;border:1.5px solid rgba(200,169,110,0.4);border-radius:8px;padding:0.55rem 0.75rem;font-size:0.72rem;font-family:monospace;">{{ env('QRIS_STATIC_STRING', config('qris.static_string')) }}</textarea>
                                        <small style="font-size:0.63rem;color:var(--text-muted);display:block;margin-top:0.25rem;">
                                            *Jika diisi string QRIS, sistem akan me-render QRIS dinamis secara otomatis. Jika kosong, sistem langsung menggunakan gambar QRIS Mayangsari.
                                        </small>
                                    </div>
                                </div>

                            </div>

                            <div style="margin-top:1.5rem;display:flex;justify-content:flex-end;">
                                <button type="submit" class="btn" style="background:linear-gradient(135deg,#6B0012,#4A0B10);color:#E8C98A;border:1px solid rgba(200,169,110,0.4);padding:0.75rem 1.75rem;font-weight:700;font-size:0.8rem;border-radius:8px;cursor:pointer;">
                                    💾 Simpan Konfigurasi Sistem
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
