<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasCommonModelAttributes;
use App\Traits\HasTreeRelationship;
use App\Traits\HasUserRelationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    use HasCommonModelAttributes;
    use HasFactory;
    use HasTreeRelationship;
    use HasUserRelationship;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'tree_id',
    ];

    // Tree relationship is now provided by the HasTreeRelationship trait

    /**
     * The individuals that belong to the story.
     */
    public function individuals(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'individual_story', 'story_id', 'individual_id');
    }
}
