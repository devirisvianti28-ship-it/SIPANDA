<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Contoh pakai di route:
     *   ->middleware('role:kepala_dinas')
     *   ->middleware('role:kepala_dinas,master_admin')   // salah satu role ini cukup
     *
     * User sekarang bisa punya LEBIH DARI 1 role sekaligus (many-to-many),
     * jadi pengecekannya pakai method hasAnyRole() di model User,
     * bukan lagi bandingin ke 1 kolom `peran` string.
     */
    public function handle(Request $request, Closure $next, string ...$rolesDiizinkan): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Anda harus login terlebih dahulu.');
        }

        if (! $user->hasAnyRole($rolesDiizinkan)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}