<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('recovery_email')
                ->nullable()
                ->unique();

            $table->timestamp('recovery_email_verified_at')
                ->nullable();

            $table->string('recovery_phone', 20)
                ->nullable()
                ->unique();

            $table->timestamp('recovery_phone_verified_at')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['recovery_email']);
            $table->dropUnique(['recovery_phone']);

            $table->dropColumn([
                'recovery_email',
                'recovery_email_verified_at',
                'recovery_phone',
                'recovery_phone_verified_at',
            ]);
        });
    }
};
