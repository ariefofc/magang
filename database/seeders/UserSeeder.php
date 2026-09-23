<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed akun default untuk 3 role sistem.
     * Jalankan: php artisan db:seed --class=UserSeeder
     */
    public function run(): void
    {
        $users = [
            // ── Super Admin: akses penuh semua fitur ──────────────────
            [
                'name'              => 'Super Admin MS Harmony',
                'email'             => 'superadmin@msharmony.id',
                'role'              => 'super_admin',
                'password'          => Hash::make('SuperAdmin123!'),
                'email_verified_at' => now(),
            ],
            // ── Admin: kelola pesanan, chat, stok ─────────────────────
            [
                'name'              => 'Admin MS Harmony',
                'email'             => 'admin@msharmony.id',
                'role'              => 'admin',
                'password'          => Hash::make('Admin123!'),
                'email_verified_at' => now(),
            ],
            // ── Owner: laporan & statistik (read-only) ────────────────
            [
                'name'              => 'Owner MS Harmony',
                'email'             => 'owner@msharmony.id',
                'role'              => 'owner',
                'password'          => Hash::make('Owner123!'),
                'email_verified_at' => now(),
            ],
            // ── Akun admin lama (dipertahankan dengan role admin) ─────
            [
                'name'              => 'Arief Harmoni',
                'email'             => 'ariefharmoni@gmail.com',
                'role'              => 'admin',
                'password'          => Hash::make('Harmoni123'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }

        $this->command->info('✅ UserSeeder: 4 akun berhasil dibuat/diperbarui.');
        $this->command->table(
            ['Name', 'Email', 'Role', 'Password'],
            [
                ['Super Admin MS Harmony', 'superadmin@msharmony.id', 'super_admin', 'SuperAdmin123!'],
                ['Admin MS Harmony',       'admin@msharmony.id',      'admin',       'Admin123!'],
                ['Owner MS Harmony',       'owner@msharmony.id',      'owner',       'Owner123!'],
                ['Arief Harmoni',          'ariefharmoni@gmail.com',  'admin',       'Harmoni123'],
            ]
        );
    }
}
