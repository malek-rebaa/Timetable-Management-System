<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('capacity')->unsigned();
            $table->enum('type', ['CLASSROOM', 'LABORATORY', 'AMPHITHEATER'])->default('CLASSROOM');
            $table->timestamps();
        });
        Schema::table('rooms', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
