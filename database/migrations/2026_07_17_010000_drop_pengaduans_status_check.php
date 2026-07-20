<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE pengaduans DROP CONSTRAINT IF EXISTS pengaduans_status_check');
    }

    public function down(): void
    {
        // Sengaja dikosongin
    }
};