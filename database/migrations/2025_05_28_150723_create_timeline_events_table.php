<?php

declare(strict_types=1);

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
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->dateTime('event_date');
            $table->string('event_type');
            $table->string('location')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            // Indexes for performance
            $table->index(['user_id'], 'pref_idx_timeline_events_user_id');
            $table->index(['event_date'], 'pref_idx_timeline_events_date');
            $table->index(['event_type'], 'pref_idx_timeline_events_event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
