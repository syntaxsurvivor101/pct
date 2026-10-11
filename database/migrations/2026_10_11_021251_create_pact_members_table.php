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
        Schema::create('pact_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pact_id')->constrained('pacts')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            $table->enum('role', ['organiser', 'co-organiser', 'member'])->default('member');
            $table->boolean('is_financial_participant')->default(true);



            $table->enum('status', ['pending', 'active', 'suspended', 'withdrawn', 'removed'])->default('pending');

            $table->timestamp('rules_accepted_at')->nullable();
            $table->timestamp('schedule_accepted_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            

            $table->unique(['pact_id', 'id']);
            $table->index(['pact_id', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pact_members');
    }
};
