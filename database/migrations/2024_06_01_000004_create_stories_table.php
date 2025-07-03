<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tree_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            // Indexes for performance
            $table->index(['tree_id'], 'pref_idx_stories_tree_id');
            $table->index(['title'], 'pref_idx_stories_title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
