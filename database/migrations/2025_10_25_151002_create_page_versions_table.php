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
        Schema::create('page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            $table->integer('version_number');
            $table->string('title');
            $table->json('content'); // Snapshot of content at this version
            $table->json('images')->nullable(); // Snapshot of images
            $table->foreignId('theme_id')->nullable()->constrained('themes')->nullOnDelete();

            // Change tracking
            $table->text('change_summary')->nullable();
            $table->json('changes')->nullable(); // Detailed diff

            $table->timestamps();

            $table->index(['page_id', 'version_number']);
            $table->unique(['page_id', 'version_number']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_versions');
    }
};
