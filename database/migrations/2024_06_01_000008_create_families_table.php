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
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tree_id');
            $table->string('gedcom_xref', 50)->unique()->nullable();
            $table->unsignedBigInteger('husband_id')->nullable();
            $table->unsignedBigInteger('wife_id')->nullable();
            $table->date('marriage_date')->nullable();
            $table->integer('marriage_year')->nullable();
            $table->text('marriage_date_raw')->nullable();
            $table->string('marriage_place', 255)->nullable();
            $table->string('marriage_type', 50)->nullable();
            $table->date('divorce_date')->nullable();
            $table->integer('divorce_year')->nullable();
            $table->text('divorce_date_raw')->nullable();
            $table->string('divorce_place', 255)->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('tree_id')->references('id')->on('trees')->onDelete('cascade');
            $table->foreign('husband_id')->references('id')->on('individuals')->onDelete('set null');
            $table->foreign('wife_id')->references('id')->on('individuals')->onDelete('set null');

            // Indexes for performance
            $table->index(['tree_id'], 'idx_families_tree');
            $table->index(['husband_id'], 'idx_families_husband');
            $table->index(['wife_id'], 'idx_families_wife');
            $table->index(['gedcom_xref'], 'idx_families_gedcom_xref');
        });

        // Create family_children pivot table
        Schema::create('family_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('family_id');
            $table->unsignedBigInteger('child_id');
            $table->integer('child_order')->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('family_id')->references('id')->on('families')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('individuals')->onDelete('cascade');

            // Unique constraint
            $table->unique(['family_id', 'child_id'], 'uk_family_child');

            // Indexes
            $table->index(['family_id'], 'idx_family_children_family');
            $table->index(['child_id'], 'idx_family_children_child');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_children');
        Schema::dropIfExists('families');
    }
}; 