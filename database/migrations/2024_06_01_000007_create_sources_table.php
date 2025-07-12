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
            $table->string('gedcom_xref', 50)->nullable();
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

            // Composite unique constraint on gedcom_xref and tree_id
            // $table->unique(['gedcom_xref', 'tree_id'], 'sources_gedcom_xref_tree_unique');

            $table->index(['tree_id'], 'idx_sources_tree');
            $table->index(['gedcom_xref'], 'idx_sources_gedcom_xref');
            $table->index(['repository_id'], 'idx_sources_repository');

            // Add foreign key constraint for repository_id
            $table->foreign('repository_id')->references('id')->on('repositories')->onDelete('set null');

            // Indexes for performance
            $table->index(['tree_id'], 'pref_idx_sources_tree_id');
            $table->index(['gedcom_xref'], 'pref_idx_sources_gedcom_xref');
            $table->index(['title'], 'pref_idx_sources_title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
