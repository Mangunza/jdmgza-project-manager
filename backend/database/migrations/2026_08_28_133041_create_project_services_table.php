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
        Schema::create('project_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->uuid('service_id')
                ->constrained('services')
                ->restrictOnDelete();

            $table->string('name');

            $table->text('description')->nullable();

            $table->decimal('quantity', 10, 2)->default(1);

            $table->decimal('unit_cost', 15, 2);

            $table->decimal('total_cost', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_services');
    }
};
