<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withholding_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('withholding_profile_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withholding_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('levy_id')->constrained()->cascadeOnDelete();
            $table->boolean('applies')->default(true);
            $table->boolean('withheld')->default(false);
            $table->timestamps();

            $table->unique(['withholding_profile_id', 'levy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withholding_profile_rules');
        Schema::dropIfExists('withholding_profiles');
    }
};
