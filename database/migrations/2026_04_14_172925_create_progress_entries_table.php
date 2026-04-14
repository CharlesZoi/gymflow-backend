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
        Schema::create('progress_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->unsignedTinyInteger('workouts_completed')->default(0);
            $table->unsignedSmallInteger('active_minutes')->default(0);
            $table->unsignedSmallInteger('calories_burned')->default(0);
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->unsignedTinyInteger('completion_rate')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'entry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_entries');
    }
};
