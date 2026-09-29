<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recovery_contact_verifications', function (Blueprint $table) {
            $table->string('destination_hash', 64)
                ->nullable()
                ->after('channel');
        });
    }

    public function down(): void
    {
        Schema::table('recovery_contact_verifications', function (Blueprint $table) {
            $table->dropColumn('destination_hash');
        });
    }
};
