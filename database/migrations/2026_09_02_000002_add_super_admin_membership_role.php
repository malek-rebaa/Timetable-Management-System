<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection('master')->getDriverName() === 'mysql') {
            DB::connection('master')->statement("ALTER TABLE school_user MODIFY role ENUM('SUPER_ADMIN', 'SCHOOL_ADMIN', 'TEACHER', 'PARENT', 'STUDENT') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::connection('master')->getDriverName() === 'mysql') {
            DB::connection('master')->statement("ALTER TABLE school_user MODIFY role ENUM('SCHOOL_ADMIN', 'TEACHER', 'PARENT', 'STUDENT') NOT NULL");
        }
    }
};