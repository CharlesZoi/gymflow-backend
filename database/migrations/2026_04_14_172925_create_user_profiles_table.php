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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('onboarding_completed')->default(false);
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('gender')->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->string('primary_goal')->nullable();
            $table->string('focus_area')->nullable();
            $table->string('result_speed')->nullable();
            $table->string('intensity_preference')->nullable();
            $table->unsignedSmallInteger('session_preference_minutes')->nullable();
            $table->text('motivation')->nullable();
            $table->text('blockers')->nullable();
            $table->string('theme')->default('system');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
