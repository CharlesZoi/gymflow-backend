<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->string('avatar_url')->nullable()->after('theme');
            $table->json('notification_settings')->nullable()->after('avatar_url');
            $table->string('membership_plan')->nullable()->after('notification_settings');
            $table->date('membership_renews_on')->nullable()->after('membership_plan');
        });

        Schema::table('workouts', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'avatar_url',
                'notification_settings',
                'membership_plan',
                'membership_renews_on',
            ]);
        });
    }
};
