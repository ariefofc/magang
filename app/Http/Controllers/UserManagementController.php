<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Daftar semua user (hanya Super Admin).
     */
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => ['required', Rule::in(['admin', 'owner', 'super_admin'])],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'role'              => $request->role,
            'password'          => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Akun ' . $request->name . ' berhasil dibuat.');
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        // Prevent demoting own account
        if ($user->id === auth()->id() && $request->role !== 'super_admin') {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $rules = [
            'name'  => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'  => ['required', Rule::in(['admin', 'owner', 'super_admin'])],
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $request->validate($rules);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Akun ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Akun ' . $name . ' berhasil dihapus.');
    }
}
