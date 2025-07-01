<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $user_id
 * @property int $tree_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read Tree $tree
 *
 * @method static \Database\Factories\StoryFactory factory()
 */
final class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'tree_id',
    ];

    /**
     * Get the tree that owns the story.
     */
    public function tree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    /**
     * The individuals that belong to the story.
     */
    public function individuals(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'individual_story', 'story_id', 'individual_id');
    }
}
