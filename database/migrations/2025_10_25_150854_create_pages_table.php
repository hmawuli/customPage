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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
             $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();

            // Content sections (JSON format for flexibility)
            $table->json('content'); // Stores all editable text content
            $table->json('images')->nullable(); // Stores image paths and metadata

            // Theme override (optional - if client wants page-specific theme)
            // Use unsignedBigInteger to avoid adding a foreign key constraint
            // before the `themes` table exists.
            $table->unsignedBigInteger('theme_id')->nullable();

            // SEO and Publishing
            $table->string('og_image')->nullable(); // Open Graph image for social sharing
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Analytics
            $table->integer('view_count')->default(0);

            // Version control
            $table->integer('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('client_id');
            $table->index('status');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
