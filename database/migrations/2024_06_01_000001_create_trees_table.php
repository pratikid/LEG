<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            // Indexes for performance
            $table->index(['user_id'], 'pref_idx_trees_user_id');
            $table->index(['name'], 'pref_idx_trees_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trees');
    }
};
