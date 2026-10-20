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
        Schema::table('pact_members', function (Blueprint $table) {
             
            $table->unsignedInteger('accepted_rules_version')->nullable();
            $table->unsignedInteger('accepted_schedule_version')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pact_members', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_rules_version',
                'accepted_schedule_version'
            ]);
        });
    }
};
