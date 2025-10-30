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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            // Visitor Information
            $table->string('visitor_ip', 45); // IPv4 and IPv6 support
            $table->string('session_id')->index();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); // OS

            // Geographic Data (optional)
            $table->string('country')->nullable();
            $table->string('city')->nullable();

            // Referrer Information
            $table->string('referrer')->nullable();
            $table->string('referrer_domain')->nullable();

            // Engagement Metrics
            $table->integer('time_on_page')->default(0); // seconds
            $table->boolean('is_unique_visitor')->default(false);
            $table->boolean('is_bounce')->default(false);

            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->index('page_id');
            $table->index('client_id');
            $table->index('visitor_ip');
            $table->index('viewed_at');
            $table->index(['page_id', 'viewed_at']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
