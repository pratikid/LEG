<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\GroupFactory>
 */
class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'tree_id',
    ];

    /**
     * @return BelongsTo<Tree, Group>
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }
}
