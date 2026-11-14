<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->date('period')->unique();
            $table->string('status', 10)->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('levy_estimate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 120)->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reconciliation_id', 'client_id']);
        });

        Schema::create('reconciliation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained()->cascadeOnDelete();
            $table->string('action', 10);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason', 500)->nullable();
            $table->unsignedInteger('total_items');
            $table->unsignedInteger('confirmed_items');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_events');
        Schema::dropIfExists('reconciliation_items');
        Schema::dropIfExists('reconciliations');
    }
};
