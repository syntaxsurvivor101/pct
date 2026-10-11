<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pact_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pact_id')->constrained('pacts')->restrictOnDelete();
            $table->unsignedInteger('position');
            $table->date('scheduled_date')->nullable();
            $table->enum('status', ['pending', 'scheduled', 'due', 'completed', 'suspended'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->unique(['pact_id', 'position']);
            $table->unique(['pact_id', 'id']);
            $table->index(['pact_id', 'scheduled_date']);
            $table->index(['pact_id', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pact_slots');
    }
};
