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
        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tree_id');
            $table->string('gedcom_xref', 50)->unique()->nullable();
            $table->string('name', 255)->notNull();
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('tree_id')->references('id')->on('trees')->onDelete('cascade');

            // Indexes for performance
            $table->index(['tree_id'], 'idx_repositories_tree');
            $table->index(['name'], 'idx_repositories_name');
            $table->index(['gedcom_xref'], 'idx_repositories_gedcom_xref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
}; 