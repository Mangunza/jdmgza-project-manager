<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->unique(
                ['project_id', 'service_id'],
                'project_services_project_id_service_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('project_services', function (Blueprint $table) {
            $table->dropUnique(
                'project_services_project_id_service_id_unique'
            );
        });
    }
};
