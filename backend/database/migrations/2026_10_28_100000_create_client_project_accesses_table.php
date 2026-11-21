<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_project_accesses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->string('status', 20)->default('active');

            $table->foreignId('granted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('granted_at')->nullable();

            $table->foreignId('revoked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['client_id', 'project_id'],
                'client_project_accesses_client_project_unique'
            );

            $table->index(
                ['project_id', 'status'],
                'client_project_accesses_project_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_project_accesses');
    }
};
