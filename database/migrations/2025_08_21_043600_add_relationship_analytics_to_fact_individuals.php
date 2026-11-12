<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fact_individuals', function (Blueprint $table) {
            // Network centrality metrics
            $table->decimal('degree_centrality', 8, 6)->nullable()->after('name_syllables');
            $table->decimal('betweenness_centrality', 8, 6)->nullable();
            $table->decimal('closeness_centrality', 8, 6)->nullable();
            $table->decimal('pagerank_score', 8, 6)->nullable();

            // Relationship counts
            $table->integer('ancestor_count')->nullable();
            $table->integer('descendant_count')->nullable();
            $table->integer('sibling_count')->nullable();
            $table->integer('spouse_count')->nullable();
            $table->integer('child_count')->nullable();

            // Network position metrics
            $table->integer('generation_depth')->nullable(); // Distance from tree root
            $table->integer('max_ancestor_depth')->nullable(); // Deepest known ancestor
            $table->integer('max_descendant_depth')->nullable(); // Deepest known descendant
            $table->decimal('clustering_coefficient', 8, 6)->nullable();

            // Relationship quality metrics
            $table->decimal('relationship_completeness', 5, 2)->nullable(); // % of expected relationships present
            $table->integer('bridge_score')->nullable(); // How much this person connects different parts
            $table->boolean('is_isolated')->default(false); // Has no relationships

            // Community/cluster membership
            $table->integer('community_id')->nullable();
            $table->string('surname_cluster')->nullable();
            $table->string('geographic_cluster')->nullable();

            // Indexes for analytics queries
            $table->index(['degree_centrality']);
            $table->index(['ancestor_count', 'descendant_count']);
            $table->index(['generation_depth']);
            $table->index(['community_id']);
            $table->index(['is_isolated']);
        });
    }

    public function down(): void
    {
        Schema::table('fact_individuals', function (Blueprint $table) {
            $table->dropColumn([
                'degree_centrality',
                'betweenness_centrality',
                'closeness_centrality',
                'pagerank_score',
                'ancestor_count',
                'descendant_count',
                'sibling_count',
                'spouse_count',
                'child_count',
                'generation_depth',
                'max_ancestor_depth',
                'max_descendant_depth',
                'clustering_coefficient',
                'relationship_completeness',
                'bridge_score',
                'is_isolated',
                'community_id',
                'surname_cluster',
                'geographic_cluster',
            ]);
        });
    }
};
