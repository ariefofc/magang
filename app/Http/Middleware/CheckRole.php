<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Penggunaan di route: ->middleware('role:super_admin')
     *                   atau ->middleware('role:admin,super_admin')
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Pastikan user sudah login
        if (! $request->user()) {
            return redirect()->route('login')
                             ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = $request->user();

        // Cek apakah role user ada di daftar role yang diizinkan
        if (! $user->hasRole(...$roles)) {
            // Jika ada role tapi tidak authorized → tampilkan halaman 403
            abort(403, 'Anda tidak memiliki akses ke halaman ini. (Role: ' . $user->roleLabel() . ')');
        }

        return $next($request);
    }
}
