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
        Schema::create('pact_invitations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pact_id')->constrained('pacts')->restrictOnDelete();
            $table->foreignId('invited_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();

            $table->string('email')->nullable();
            $table->string('token_hash', 64)->unique();

            $table->enum('status', ['pending', 'accepted', 'declined', 'revoked', 'expired'])->default('pending');

            $table->timestamp('expired_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('revoked_at')->nullable();


            $table->index(['pact_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['email', 'status']);
            $table->index(['status', 'expired_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pact_invitations');
    }
};
