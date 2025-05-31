<?php

declare(strict_types=1);

namespace App\Models;

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
} 