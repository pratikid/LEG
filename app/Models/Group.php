<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $tree_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Tree $tree
 *
 * @method static \Database\Factories\GroupFactory factory()
 */
final class Group extends Model
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
