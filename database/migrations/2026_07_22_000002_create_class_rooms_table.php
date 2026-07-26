<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('student_count')->unsigned()->default(0);
            $table->timestamps();
        });
        Schema::table('class_rooms', function (Blueprint $table) {
            $table->unique(['level_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_rooms');
    }
};
