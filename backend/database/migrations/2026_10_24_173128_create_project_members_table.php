
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('role')->default('member');

            $table->string('status')->default('active');

            $table->foreignId('added_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['project_id', 'user_id'],
                'project_members_project_user_unique'
            );

            $table->index(
                ['user_id', 'status'],
                'project_members_user_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
