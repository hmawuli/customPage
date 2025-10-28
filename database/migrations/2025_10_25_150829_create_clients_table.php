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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('slug')->unique(); // For SEO-friendly URLs
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            // Use unsignedBigInteger here to avoid adding a foreign key constraint
            // before the `themes` table exists. We'll add the constraint in a
            // later migration if needed.
            $table->unsignedBigInteger('theme_id')->nullable();
            $table->enum('role', ['client', 'admin'])->default('client');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
