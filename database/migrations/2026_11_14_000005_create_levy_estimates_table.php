<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levy_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rate_card_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withholding_profile_id')->nullable()->constrained()->nullOnDelete();
            // Names are copied at calculation time so a later rename doesn't rewrite history.
            $table->string('rate_card_name', 120)->nullable();
            $table->string('withholding_profile_name', 120)->nullable();
            $table->decimal('base_amount', 12, 2);
            $table->decimal('levies_total', 12, 2);
            $table->decimal('withheld_total', 12, 2);
            $table->decimal('net_amount', 12, 2);
            $table->json('breakdown')->nullable();
            $table->string('notes', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levy_estimates');
    }
};
