<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('media_asset_id');
            $table->string('purpose', 32)->default('marketing');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('editorial_status', 16)->default('draft');
            $table->string('title', 255)->nullable();
            $table->string('alt_text', 500)->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('media_asset_id')->references('id')->on('media_assets')->restrictOnDelete();
            $table->unique(['product_id', 'media_asset_id', 'purpose']);
            $table->index(['product_id', 'sort_order']);
            $table->index(['editorial_status', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};
