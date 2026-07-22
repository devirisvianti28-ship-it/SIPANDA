<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Isi 3 role default
        $roles = [
            ['name' => 'master_admin', 'label' => 'Master Admin'],
            ['name' => 'pengelola',    'label' => 'Pengelola'],
            ['name' => 'kepala_dinas', 'label' => 'Kepala Dinas'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                ['label' => $role['label'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2) Pindahkan nilai kolom `peran` yang lama (1 user = 1 nilai teks)
        //    ke tabel pivot role_user, supaya data lama gak hilang.
        //    Kalau kolom `peran` sudah tidak ada / sudah dihapus, blok ini
        //    otomatis dilewati (try-catch) supaya migrasi tidak gagal.
        try {
            $users = DB::table('users')->select('id', 'peran')->get();

            foreach ($users as $user) {
                if (empty($user->peran)) {
                    continue;
                }

                $roleId = DB::table('roles')->where('name', $user->peran)->value('id');

                if ($roleId) {
                    DB::table('role_user')->updateOrInsert([
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                    ], [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Kolom `peran` mungkin sudah tidak ada — aman diabaikan.
        }
    }

    public function down(): void
    {
        DB::table('role_user')->truncate();
        DB::table('roles')->truncate();
    }
};