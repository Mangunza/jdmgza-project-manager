<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_type', 32);
            $table->string('disk', 64)->nullable();
            $table->string('storage_path', 680)->nullable();
            $table->string('external_url', 2048)->nullable();
            $table->string('mime_type', 127)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('visibility', 16)->default('private');
            $table->string('processing_status', 16)->default('ready');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['disk', 'storage_path']);
            $table->index(['checksum_sha256', 'size_bytes']);
            $table->index(['visibility', 'processing_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
