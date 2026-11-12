<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Analytics Dimension Tables

        // Time dimension for temporal analytics
        Schema::create('dim_time', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('year');
            $table->integer('month');
            $table->integer('day');
            $table->integer('quarter');
            $table->integer('week_of_year');
            $table->integer('day_of_week');
            $table->string('month_name');
            $table->string('day_name');
            $table->boolean('is_weekend');
            $table->boolean('is_holiday')->default(false);
            $table->integer('decade');
            $table->integer('century');
            $table->timestamps();
            
            $table->unique('date');
            $table->index(['year', 'month']);
            $table->index(['decade']);
            $table->index(['century']);
        });

        // Location dimension for geographic analytics  
        Schema::create('dim_location', function (Blueprint $table) {
            $table->id();
            $table->string('location_key')->unique(); // Hash of normalized location
            $table->string('raw_location'); // Original location string
            $table->string('normalized_location'); // Cleaned/standardized
            $table->string('city')->nullable();
            $table->string('state')->nullable(); 
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('confidence_score')->default(0); // Geocoding confidence
            $table->timestamps();
            
            $table->index(['country', 'state']);
            $table->index(['city']);
            $table->spatialIndex(['latitude', 'longitude']);
        });

        // Analytics Fact Tables

        // Individual facts for demographic analysis
        Schema::create('fact_individuals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('individual_id');
            $table->unsignedBigInteger('tree_id');
            $table->unsignedBigInteger('birth_date_key')->nullable();
            $table->unsignedBigInteger('death_date_key')->nullable();
            $table->unsignedBigInteger('birth_location_key')->nullable();
            $table->unsignedBigInteger('death_location_key')->nullable();
            
            // Computed fields for analytics
            $table->integer('lifespan_days')->nullable();
            $table->integer('birth_year')->nullable();
            $table->integer('death_year')->nullable();
            $table->integer('birth_decade')->nullable();
            $table->integer('death_decade')->nullable();
            $table->integer('birth_century')->nullable();
            $table->integer('death_century')->nullable();
            $table->enum('sex', ['M', 'F', 'U']);
            $table->integer('generation')->nullable(); // Relative to tree root
            $table->decimal('migration_distance_km', 8, 2)->nullable();
            
            // Name analytics
            $table->string('first_name_key')->nullable(); // Normalized first name
            $table->string('last_name_key')->nullable(); // Normalized last name
            $table->integer('name_length')->nullable();
            $table->integer('name_syllables')->nullable();
            
            $table->timestamps();
            
            $table->foreign('individual_id')->references('id')->on('individuals');
            $table->foreign('tree_id')->references('id')->on('trees');
            $table->foreign('birth_date_key')->references('id')->on('dim_time');
            $table->foreign('death_date_key')->references('id')->on('dim_time');
            $table->foreign('birth_location_key')->references('id')->on('dim_location');
            $table->foreign('death_location_key')->references('id')->on('dim_location');
            
            $table->index(['tree_id', 'birth_decade']);
            $table->index(['sex', 'birth_century']);
            $table->index(['generation']);
            $table->index(['first_name_key']);
            $table->index(['last_name_key']);
        });

        // Relationship facts for network analytics
        Schema::create('fact_relationships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tree_id');
            $table->unsignedBigInteger('individual_a_id');
            $table->unsignedBigInteger('individual_b_id');
            $table->enum('relationship_type', ['PARENT_OF', 'SPOUSE_OF', 'SIBLING_OF']);
            $table->integer('degree_of_separation')->nullable();
            $table->integer('generation_diff')->nullable(); // A's generation - B's generation
            $table->decimal('age_diff_years', 5, 2)->nullable(); // A's age - B's age
            $table->boolean('is_direct_relationship')->default(true);
            $table->json('relationship_path')->nullable(); // For indirect relationships
            $table->timestamps();
            
            $table->foreign('tree_id')->references('id')->on('trees');
            $table->foreign('individual_a_id')->references('id')->on('individuals');
            $table->foreign('individual_b_id')->references('id')->on('individuals');
            
            $table->index(['tree_id', 'relationship_type']);
            $table->index(['degree_of_separation']);
            $table->index(['generation_diff']);
            $table->unique(['individual_a_id', 'individual_b_id', 'relationship_type'], 'uk_relationship');
        });

        // Tree-level aggregated metrics
        Schema::create('fact_tree_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tree_id');
            $table->date('snapshot_date'); // Daily snapshots
            
            // Size metrics
            $table->integer('individual_count');
            $table->integer('family_count');
            $table->integer('generation_count');
            $table->integer('relationship_count');
            
            // Demographic metrics
            $table->integer('male_count');
            $table->integer('female_count');
            $table->integer('unknown_sex_count');
            $table->decimal('avg_lifespan_years', 5, 2)->nullable();
            $table->integer('birth_year_span')->nullable(); // Latest - earliest birth year
            
            // Geographic metrics
            $table->integer('unique_locations');
            $table->decimal('max_migration_distance_km', 8, 2)->nullable();
            $table->decimal('avg_migration_distance_km', 8, 2)->nullable();
            
            // Completeness metrics
            $table->decimal('birth_date_completeness', 5, 2); // % with birth dates
            $table->decimal('death_date_completeness', 5, 2); // % with death dates  
            $table->decimal('location_completeness', 5, 2); // % with locations
            
            // Network metrics
            $table->decimal('avg_degree_centrality', 8, 6)->nullable();
            $table->decimal('network_density', 8, 6)->nullable();
            $table->integer('connected_components')->nullable();
            
            $table->timestamps();
            
            $table->foreign('tree_id')->references('id')->on('trees');
            $table->unique(['tree_id', 'snapshot_date']);
            $table->index(['snapshot_date']);
        });

        // Analytics job tracking
        Schema::create('analytics_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_type'); // etl, aggregation, export
            $table->string('job_name');
            $table->enum('status', ['pending', 'running', 'completed', 'failed']);
            $table->json('parameters')->nullable();
            $table->text('output')->nullable();
            $table->text('error_message')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->integer('records_processed')->nullable();
            $table->timestamps();
            
            $table->index(['job_type', 'status']);
            $table->index(['started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_jobs');
        Schema::dropIfExists('fact_tree_metrics');
        Schema::dropIfExists('fact_relationships');
        Schema::dropIfExists('fact_individuals');
        Schema::dropIfExists('dim_location');
        Schema::dropIfExists('dim_time');
    }
};
