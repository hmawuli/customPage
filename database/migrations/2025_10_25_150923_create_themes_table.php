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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('primary_color'); // Main color
            $table->string('secondary_color'); // Secondary color
            $table->string('accent_color'); // Accent/highlight color
            $table->string('text_color')->default('#000000');
            $table->string('background_color')->default('#FFFFFF');
            $table->json('additional_colors')->nullable(); // For extra color options
            $table->string('preview_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index('slug');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
