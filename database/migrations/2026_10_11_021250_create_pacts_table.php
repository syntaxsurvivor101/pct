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
        Schema::create('pacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organiser_id')->constrained('users')->restrictOnDelete();


            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->enum('type', ['rosca'])->default('rosca');
            $table->string('currency', 3)->default('GBP');

            $table->unsignedBigInteger('contribution_amount');

            $table->enum('contribution_frequency', ['every_5_days', 'weekly', 'biweekly', 'monthly']);
            $table->enum('collection_method', ['direct_to_recipient', 'via_organiser']);
            $table->enum('visibility', ['private', 'public'])->default('private');
            $table->enum('join_method', ['approval_required', 'automatic'])->default('approval_required');
            $table->enum('activation_method', ['manual', 'automatic'])->default('manual');
            $table->enum('status', ['draft', 'recruiting', 'ready', 'active', 'completed', 'cancelled']);

            $table->unsignedTinyInteger('min_members')->default(3);
            $table->unsignedTinyInteger('max_members')->default(30);


            $table->unsignedTinyInteger('planned_slot');
            $table->unsignedTinyInteger('max_slots_per_member');

            $table->unsignedInteger('schedule_version')->default(1);

            $table->date('start_date')->nullable();


            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->softDeletes();

            $table->index(['status', 'visibility']);
            $table->index(['organiser_id', 'status']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacts');
    }
};
