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
        Schema::create('cost_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_budget_id')->nullable()->constrained('project_budgets')->nullOnDelete();
            $table->foreignId('project_service_id')->nullable()->constrained('project_services')->nullOnDelete();
            $table->string('cost_type',30)->default('actual');
            $table->string('status',30)->default('recorded');
            $table->string('description');
            $table->decimal('amount',15,2);
            $table->string('currency',3)->default('AOA');
            $table->date('incurred_on')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['project_id','cost_type','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_entries');
    }
};
