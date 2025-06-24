<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tree_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->text('error_message')->nullable();
            $table->string('status_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['tree_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_progress');
    }
};
