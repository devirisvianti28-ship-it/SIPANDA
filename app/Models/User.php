<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi lewat mass assignment (create/update).
     *
     * @var list<string>
     */

    protected $fillable = [
    'nip',
    'name',
    'username',
    'email',
    'password',
    'nama_lengkap',
    'peran',
    'foto_profil',
];

    /**
     * Kolom yang disembunyikan saat model di-serialize (misal ke JSON).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting otomatis untuk kolom tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi banyak-ke-banyak ke tabel roles lewat pivot role_user.
     * Satu user sekarang bisa punya lebih dari 1 role (misal Master Admin + Pengelola).
     */
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class);
    }

    /**
     * Cek apakah user punya SATU role tertentu.
     * Contoh: $user->hasRole('kepala_dinas')
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Cek apakah user punya SALAH SATU dari beberapa role.
     * Dipakai oleh middleware role:role1,role2,dst
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles->pluck('name')->intersect($roleNames)->isNotEmpty();
    }
}