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
        Schema::create('pact_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pact_id')->unique()->constrained('pacts')->restrictOnDelete();

            $table->boolean('allow_partial_payments')->default(false);
            $table->boolean('allow_early_contributions')->default(false);
            $table->boolean('allow_advance_payments')->default(false);

            $table->unsignedInteger('grace_period_hours')->default(0);
            $table->boolean('late_penalty_enabled')->default(false);

            $table->enum('late_penalty_type', ['fixed', 'percentage'])->nullable();
            $table->unsignedBigInteger('late_penalty_amount')->nullable();
            $table->decimal('late_penalty_percentage', 5, 2)->nullable();
            $table->json('permitted_fraction_units')->nullable();
            $table->enum('payout_order_method', ['manual', 'random'])->default('manual');
            $table->boolean('automatic_reminders_enabled')->default(true);
            $table->unsignedInteger('reminder_hours_before')->default(24);
            $table->boolean('overdue_reminders_enabled')->default(true);
            
            $table->unsignedInteger('version')->default(1);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pact_rules');
    }
};
