<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Neo4jIndividualService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Individual extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'death_date',
        'tree_id',
    ];

    protected $dates = [
        'birth_date',
        'death_date',
    ];

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    protected static function booted()
    {
        static::created(function ($individual) {
            app(Neo4jIndividualService::class)->createIndividualNode([
                'id' => $individual->id,
                'first_name' => $individual->first_name,
                'last_name' => $individual->last_name,
                'birth_date' => $individual->birth_date,
                'death_date' => $individual->death_date,
                'tree_id' => $individual->tree_id,
            ]);
        });
        static::updated(function ($individual) {
            app(Neo4jIndividualService::class)->updateIndividualNode([
                'id' => $individual->id,
                'first_name' => $individual->first_name,
                'last_name' => $individual->last_name,
                'birth_date' => $individual->birth_date,
                'death_date' => $individual->death_date,
                'tree_id' => $individual->tree_id,
            ]);
        });
        static::deleted(function ($individual) {
            app(Neo4jIndividualService::class)->deleteIndividualNode($individual->id);
        });
    }
}
