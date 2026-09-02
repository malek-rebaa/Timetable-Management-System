<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('OWNER', 'SUPER_ADMIN', 'ADMIN', 'TEACHER') NOT NULL DEFAULT 'TEACHER'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('SUPER_ADMIN', 'ADMIN', 'TEACHER') NOT NULL DEFAULT 'TEACHER'");
        }
    }
};