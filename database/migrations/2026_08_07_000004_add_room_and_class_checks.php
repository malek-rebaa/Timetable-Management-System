<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Garde-fous de capacité (MariaDB >= 10.2 applique les CHECK)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE rooms ADD CONSTRAINT rooms_capacity_check CHECK (capacity > 0)');
            DB::statement('ALTER TABLE class_rooms ADD CONSTRAINT class_rooms_student_count_check CHECK (student_count >= 0)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE rooms DROP CONSTRAINT rooms_capacity_check');
            DB::statement('ALTER TABLE class_rooms DROP CONSTRAINT class_rooms_student_count_check');
        }
    }
};
