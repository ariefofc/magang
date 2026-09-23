<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen User — MS Harmony Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
<style>
.um-page { padding: 28px; max-width: 1100px; margin: 0 auto; }
.um-header { margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; }
.um-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: #3D342F; }
.um-sub { font-size: 0.72rem; color: #8A7060; margin-top: 4px; }
.btn-add {
    background: linear-gradient(135deg, #6B0012, #4A0B10);
    color: #DFC28A;
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.2s;
    display: flex; align-items: center; gap: 6px;
}
.btn-add:hover { opacity: 0.9; transform: translateY(-1px); }

/* Alert */
.alert-success { background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; border-radius: 10px; padding: 12px 18px; margin-bottom: 18px; font-size: 0.8rem; }
.alert-error   { background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 12px 18px; margin-bottom: 18px; font-size: 0.8rem; }

/* Role Badges */
.role-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 100px; font-size: 0.65rem; font-weight: 700; }
.role-badge-superadmin { background: #7c3aed22; color: #7c3aed; border: 1px solid #7c3aed44; }
.role-badge-admin      { background: #6B001222; color: #6B0012; border: 1px solid #6B001244; }
.role-badge-owner      { background: #C8A96E22; color: #92400e; border: 1px solid #C8A96E66; }

/* Table */
.um-table-wrap { background: white; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid rgba(200,169,110,0.2); }
table { width: 100%; border-collapse: collapse; }
thead tr { background: linear-gradient(135deg, #6B0012, #4A0B10); }
thead th { padding: 13px 16px; text-align: left; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(230,198,135,0.8); }
tbody tr { border-bottom: 1px solid rgba(200,169,110,0.12); transition: background 0.15s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #fdf9f3; }
td { padding: 13px 16px; font-size: 0.8rem; color: #3D342F; vertical-align: middle; }
.td-actions { display: flex; gap: 6px; }
.btn-edit { padding: 6px 12px; border-radius: 8px; font-size: 0.68rem; font-weight: 600; cursor: pointer; border: 1px solid rgba(200,169,110,0.4); background: #fdf9f3; color: #78350f; font-family: inherit; transition: all 0.15s; }
.btn-edit:hover { background: #fef3c7; }
.btn-del  { padding: 6px 12px; border-radius: 8px; font-size: 0.68rem; font-weight: 600; cursor: pointer; border: 1px solid #fecaca; background: #fee2e2; color: #991b1b; font-family: inherit; transition: all 0.15s; }
.btn-del:hover { background: #fecaca; }
.td-you { font-size: 0.6rem; color: #059669; background: #dcfce7; border-radius: 4px; padding: 2px 6px; margin-left: 4px; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 16px; }
.modal-overlay.open { display: flex; }
.modal-box { background: #FDFBF7; border-radius: 18px; padding: 28px; width: 100%; max-width: 440px; box-shadow: 0 24px 60px rgba(0,0,0,0.3); position: relative; }
.modal-title { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #3D342F; margin-bottom: 20px; }
.form-field { margin-bottom: 14px; }
.form-label { display: block; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #5A4A40; margin-bottom: 5px; }
.form-input, .form-select { width: 100%; padding: 9px 12px; border: 1.5px solid rgba(200,169,110,0.35); border-radius: 10px; font-size: 0.8rem; color: #3D342F; background: white; outline: none; transition: border-color 0.2s; font-family: inherit; }
.form-input:focus, .form-select:focus { border-color: #6B0012; }
.form-hint { font-size: 0.62rem; color: #8A7060; margin-top: 4px; }
.form-error { font-size: 0.65rem; color: #dc2626; margin-top: 4px; }
.modal-actions { display: flex; gap: 10px; margin-top: 20px; }
.btn-submit { flex: 1; padding: 11px; background: linear-gradient(135deg, #6B0012, #4A0B10); color: #DFC28A; border: none; border-radius: 10px; font-weight: 700; font-size: 0.78rem; cursor: pointer; font-family: inherit; }
.btn-cancel { flex: 1; padding: 11px; background: transparent; border: 1.5px solid rgba(200,169,110,0.35); border-radius: 10px; color: #8A7060; font-size: 0.78rem; cursor: pointer; font-family: inherit; }
.back-link { display: inline-flex; align-items: center; gap: 6px; color: #8A7060; text-decoration: none; font-size: 0.75rem; margin-bottom: 20px; transition: color 0.2s; }
.back-link:hover { color: #6B0012; }
</style>
</head>
<body class="db-body" style="background:#F4EDE0;min-height:100vh;">

<div class="um-page">
    <a href="{{ route('dashboard') }}" class="back-link">⬅ Kembali ke Dashboard</a>

    <div class="um-header">
        <div>
            <h1 class="um-title">👥 Manajemen Akun Pengguna</h1>
            <p class="um-sub">Kelola akun Admin & Owner sistem MS Harmony — hanya dapat diakses Super Admin</p>
        </div>
        <button class="btn-add" onclick="openAddModal()">+ Tambah Akun Baru</button>
    </div>

    @if(session('success'))
        <div class="alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">⚠️ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-error">⚠️ {{ $errors->first() }}</div>
    @endif

    <div class="um-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="color:#a8a29e;font-size:0.7rem;">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        @if($user->id === auth()->id())
                            <span class="td-you">Anda</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;color:#5A4A40;">{{ $user->email }}</td>
                    <td>
                        <span class="role-badge {{ $user->roleBadgeClass() }}">
                            {{ match($user->role) { 'super_admin' => '⚡', 'admin' => '🛡️', 'owner' => '👑', default => '👤' } }}
                            {{ $user->roleLabel() }}
                        </span>
                    </td>
                    <td style="font-size:0.72rem;color:#8A7060;">{{ $user->created_at?->format('d M Y') ?? '-' }}</td>
                    <td>
                        <div class="td-actions">
                            <button class="btn-edit"
                                    onclick="openEditModal({{ $user->id }}, '{{ e($user->name) }}', '{{ e($user->email) }}', '{{ $user->role }}')">
                                ✏️ Edit
                            </button>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Hapus akun {{ e($user->name) }}? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del">🗑 Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:#8A7060;">
                        Belum ada akun pengguna.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal-overlay" id="modal-add">
    <div class="modal-box">
        <h2 class="modal-title">➕ Tambah Akun Baru</h2>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="form-field">
                <label class="form-label">Nama Lengkap *</label>
                <input class="form-input" type="text" name="name" required placeholder="Nama lengkap" value="{{ old('name') }}">
            </div>
            <div class="form-field">
                <label class="form-label">Email *</label>
                <input class="form-input" type="email" name="email" required placeholder="email@domain.com" value="{{ old('email') }}">
            </div>
            <div class="form-field">
                <label class="form-label">Role *</label>
                <select class="form-select" name="role" required>
                    <option value="admin">🛡️ Admin</option>
                    <option value="owner">👑 Owner</option>
                    <option value="super_admin">⚡ Super Admin</option>
                </select>
            </div>
            <div class="form-field">
                <label class="form-label">Password *</label>
                <input class="form-input" type="password" name="password" required placeholder="Min. 8 karakter">
            </div>
            <div class="form-field">
                <label class="form-label">Konfirmasi Password *</label>
                <input class="form-input" type="password" name="password_confirmation" required placeholder="Ulangi password">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modal-add')">Batal</button>
                <button type="submit" class="btn-submit">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal-overlay" id="modal-edit">
    <div class="modal-box">
        <h2 class="modal-title">✏️ Edit Akun</h2>
        <form method="POST" id="edit-form" action="">
            @csrf @method('PUT')
            <div class="form-field">
                <label class="form-label">Nama Lengkap *</label>
                <input class="form-input" type="text" name="name" id="edit-name" required>
            </div>
            <div class="form-field">
                <label class="form-label">Email *</label>
                <input class="form-input" type="email" name="email" id="edit-email" required>
            </div>
            <div class="form-field">
                <label class="form-label">Role *</label>
                <select class="form-select" name="role" id="edit-role">
                    <option value="admin">🛡️ Admin</option>
                    <option value="owner">👑 Owner</option>
                    <option value="super_admin">⚡ Super Admin</option>
                </select>
            </div>
            <div class="form-field">
                <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                <input class="form-input" type="password" name="password" placeholder="Kosongkan jika tidak diubah">
            </div>
            <div class="form-field">
                <label class="form-label">Konfirmasi Password</label>
                <input class="form-input" type="password" name="password_confirmation">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal('modal-edit')">Batal</button>
                <button type="submit" class="btn-submit">Update Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() { document.getElementById('modal-add').classList.add('open'); }
function openEditModal(id, name, email, role) {
    document.getElementById('edit-form').action = '/admin/users/' + id;
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-role').value  = role;
    document.getElementById('modal-edit').classList.add('open');
}
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
// Close on overlay click
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) { if (e.target === m) closeModal(m.id); });
});
</script>
</body>
</html>
