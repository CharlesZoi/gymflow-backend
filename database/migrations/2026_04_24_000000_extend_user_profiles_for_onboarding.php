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
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->string('nickname')->nullable()->after('user_id');
            $table->string('first_name')->nullable()->after('nickname');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('body_type')->nullable()->after('gender');
            $table->string('fitness_level')->nullable()->after('body_type');
            $table->string('training_days')->nullable()->after('fitness_level');
            $table->string('training_preference')->nullable()->after('training_days');
            $table->string('main_fitness_goal')->nullable()->after('training_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'nickname',
                'first_name',
                'last_name',
                'body_type',
                'fitness_level',
                'training_days',
                'training_preference',
                'main_fitness_goal',
            ]);
        });
    }
};
