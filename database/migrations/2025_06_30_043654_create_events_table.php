<?php

declare(strict_types=1);

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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->onDelete('cascade');
            $table->foreignId('individual_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('family_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // birth, death, marriage, divorce, baptism, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date')->nullable();
            $table->string('event_place')->nullable();
            $table->string('event_city')->nullable();
            $table->string('event_state')->nullable();
            $table->string('event_country')->nullable();
            $table->string('event_latitude')->nullable();
            $table->string('event_longitude')->nullable();
            $table->json('additional_data')->nullable(); // For GEDCOM-specific data
            $table->string('gedcom_xref')->nullable(); // GEDCOM reference
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['tree_id', 'type']);
            $table->index(['individual_id', 'type']);
            $table->index(['family_id', 'type']);
            $table->index('event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
