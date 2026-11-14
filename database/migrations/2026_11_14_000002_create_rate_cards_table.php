<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('rate_card_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('levy_id')->constrained()->cascadeOnDelete();
            $table->decimal('percentage', 6, 3);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->unique(['rate_card_id', 'levy_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_card_items');
        Schema::dropIfExists('rate_cards');
    }
};
