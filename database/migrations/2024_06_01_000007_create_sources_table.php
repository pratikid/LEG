<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('gedcom_xref', 50)->unique()->nullable();
            $table->string('title');
            $table->string('author', 255)->nullable();
            $table->text('publication')->nullable();
            $table->unsignedBigInteger('repository_id')->nullable();
            $table->string('call_number', 255)->nullable();
            $table->integer('data_quality')->default(0);
            $table->text('citation')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tree_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['tree_id'], 'idx_sources_tree');
            $table->index(['gedcom_xref'], 'idx_sources_gedcom_xref');
            $table->index(['repository_id'], 'idx_sources_repository');

            // Add foreign key constraint for repository_id
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
