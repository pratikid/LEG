<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the PostgreSQL ENUM type first if it doesn't exist
        if (! DB::select("SELECT 1 FROM pg_type WHERE typname = 'sex_enum'")) {
            DB::statement("CREATE TYPE sex_enum AS ENUM ('M', 'F', 'U')");
        }

        Schema::create('individuals', function (Blueprint $table) {
            $table->id();
            $table->text('gedcom_xref')->nullable(); // Original GEDCOM reference
            $table->text('first_name')->nullable(); // First name
            $table->text('last_name')->nullable(); // Last name
            $table->text('name_prefix')->nullable(); // Name prefix (e.g., Dr., Sir)
            $table->text('name_suffix')->nullable(); // Name suffix (e.g., Jr., Sr.)
            $table->text('nickname')->nullable(); // Nickname
            $table->text('sex')->nullable(); // We'll modify this to use the enum type
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            // Add for partial/unknown dates
            $table->integer('birth_year')->nullable();
            $table->integer('death_year')->nullable();
            $table->text('birth_date_raw')->nullable();
            $table->text('death_date_raw')->nullable();
            $table->text('birth_place')->nullable(); // Birth place
            $table->text('death_place')->nullable(); // Death place
            $table->text('death_cause')->nullable(); // Death cause
            $table->text('pedigree_type')->nullable(); // From FAMC.PEDI
            $table->foreignId('tree_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            // Composite unique constraint on gedcom_xref and tree_id
            $table->unique(['gedcom_xref', 'tree_id'], 'individuals_gedcom_xref_tree_unique');

            // Indexes for performance
            $table->index(['tree_id', 'last_name'], 'pref_idx_individuals_tree_last_name');
            $table->index(['tree_id', 'birth_date'], 'pref_idx_individuals_tree_birth_date');
            $table->index(['gedcom_xref'], 'pref_idx_individuals_gedcom_xref');
            $table->index(['sex'], 'pref_idx_individuals_sex');
            $table->index(['birth_year'], 'pref_idx_individuals_birth_year');
            $table->index(['death_year'], 'pref_idx_individuals_death_year');
        });

        // Alter the column to use the ENUM type
        DB::statement('ALTER TABLE individuals ALTER COLUMN sex TYPE sex_enum USING sex::sex_enum');
    }

    public function down(): void
    {
        Schema::dropIfExists('individuals');

        // Drop the ENUM type
        DB::statement('DROP TYPE IF EXISTS sex_enum');
    }
};
