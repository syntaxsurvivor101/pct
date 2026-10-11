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
        Schema::create('pact_slot_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pact_id')->constrained('pacts')->restrictOnDelete();

            $table->unsignedBigInteger('pact_slot_id');
            $table->unsignedBigInteger('pact_member_id');
            

            $table->foreign(['pact_id', 'pact_slot_id'])->references(['pact_id', 'id'])->on('pact_slots')->restrictOnDelete();
            $table->foreign(['pact_id', 'pact_member_id'])->references(['pact_id', 'id'])->on('pact_members')->restrictOnDelete();


            $table->unsignedInteger('allocation_units');

            $table->unsignedInteger('accepted_rules_version')->nullable();
            $table->unsignedInteger('accepted_schedule_version')->nullable();

            $table->unique(['pact_slot_id', 'pact_member_id'], 'slot_member_unique');
            $table->index('pact_member_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pact_slot_allocations');
    }
};
