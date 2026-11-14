<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 80);
            // Levies that share a group have their withholding amounts summed before the
            // minimum-withholding floor below is checked (e.g. a trio of small contribution
            // levies that only get withheld once their combined amount clears the floor).
            $table->string('withholding_group', 40)->nullable();
            $table->decimal('withholding_floor', 10, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levies');
    }
};
