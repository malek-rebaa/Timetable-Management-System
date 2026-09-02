<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('master')->create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('database_name')->unique();
            $table->enum('status', ['PENDING', 'ACTIVE', 'SUSPENDED', 'ARCHIVED'])->default('PENDING');
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#2563EB');
            $table->string('secondary_color', 7)->nullable();
            $table->string('timezone')->default('Africa/Tunis');
            $table->string('locale', 10)->default('fr');
            $table->timestamps();
        });

        Schema::connection('master')->create('school_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['SCHOOL_ADMIN', 'TEACHER', 'PARENT', 'STUDENT']);
            $table->enum('status', ['ACTIVE', 'SUSPENDED'])->default('ACTIVE');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index(['school_id', 'role', 'status']);
        });

        Schema::connection('master')->create('system_roles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
        });

        Schema::connection('master')->create('system_role_user', function (Blueprint $table) {
            $table->foreignId('system_role_id')->constrained('system_roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->primary(['system_role_id', 'user_id']);
        });

        Schema::connection('master')->create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('master')->dropIfExists('audit_logs');
        Schema::connection('master')->dropIfExists('system_role_user');
        Schema::connection('master')->dropIfExists('system_roles');
        Schema::connection('master')->dropIfExists('school_user');
        Schema::connection('master')->dropIfExists('schools');
    }
};
