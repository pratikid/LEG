<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the PostgreSQL ENUM type first
        DB::statement("CREATE TYPE sex_enum AS ENUM ('M', 'F', 'U')");

        Schema::create('individuals', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->string('sex')->nullable(); // We'll modify this to use the enum type
            $table->foreignId('tree_id')->constrained()->onDelete('cascade');
            $table->timestamps();
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
