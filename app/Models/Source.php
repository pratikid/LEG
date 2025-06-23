<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $gedcom_xref
 * @property string $title
 * @property string|null $author
 * @property string|null $publication
 * @property int|null $repository_id
 * @property string|null $call_number
 * @property int $data_quality
 * @property string|null $citation
 * @property int $user_id
 * @property int|null $tree_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Tree $tree
 * @property-read \App\Models\Repository|null $repository
 *
 * @method static \Database\Factories\SourceFactory factory()
 */
class Source extends Model
{
    use HasFactory;

    protected $fillable = [
        'gedcom_xref',
        'title',
        'author',
        'publication',
        'repository_id',
        'call_number',
        'data_quality',
        'citation',
        'user_id',
        'tree_id',
    ];

    protected $casts = [
        'data_quality' => 'integer',
    ];

    /**
     * Get the repository associated with this source
     */
    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    /**
     * Get the user who created this source
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tree this source belongs to
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    /**
     * Scope to filter by GEDCOM xref
     */
    public function scopeByGedcomXref($query, string $xref)
    {
        return $query->where('gedcom_xref', $xref);
    }

    /**
     * Scope to filter by tree
     */
    public function scopeForTree($query, int $treeId)
    {
        return $query->where('tree_id', $treeId);
    }

    /**
     * Scope to filter by repository
     */
    public function scopeByRepository($query, int $repositoryId)
    {
        return $query->where('repository_id', $repositoryId);
    }

    /**
     * Scope to filter by data quality
     */
    public function scopeByDataQuality($query, int $quality)
    {
        return $query->where('data_quality', $quality);
    }

    /**
     * Scope to filter by minimum data quality
     */
    public function scopeByMinDataQuality($query, int $minQuality)
    {
        return $query->where('data_quality', '>=', $minQuality);
    }

    /**
     * Get the full citation text
     */
    public function getFullCitationAttribute(): string
    {
        $parts = array_filter([
            $this->author,
            $this->title,
            $this->publication,
            $this->call_number,
        ]);

        return implode('. ', $parts);
    }

    /**
     * Get the data quality label
     */
    public function getDataQualityLabelAttribute(): string
    {
        return match ($this->data_quality) {
            0 => 'Unreliable',
            1 => 'Questionable',
            2 => 'Secondary',
            3 => 'Primary',
            4 => 'Direct',
            default => 'Unknown',
        };
    }
}
