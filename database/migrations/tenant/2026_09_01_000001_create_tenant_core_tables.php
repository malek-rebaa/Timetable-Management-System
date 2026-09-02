<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('tenant_metadata', function (Blueprint $table) {
            $table->id();
            // Référence logique vers edutime_master.schools, volontairement sans FK inter-base.
            $table->unsignedBigInteger('school_id')->unique();
            $table->string('schema_version')->nullable();
            $table->timestamp('provisioned_at')->useCurrent();
        });

        Schema::connection('tenant')->create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();

            $table->unique(['academic_year_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('terms');
        Schema::connection('tenant')->dropIfExists('academic_years');
        Schema::connection('tenant')->dropIfExists('tenant_settings');
        Schema::connection('tenant')->dropIfExists('tenant_metadata');
    }
};
