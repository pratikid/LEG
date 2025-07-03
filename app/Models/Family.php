<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $tree_id
 * @property string|null $gedcom_xref
 * @property int|null $husband_id
 * @property int|null $wife_id
 * @property \Illuminate\Support\Carbon|null $marriage_date
 * @property string|null $marriage_place
 * @property string|null $marriage_type
 * @property \Illuminate\Support\Carbon|null $divorce_date
 * @property string|null $divorce_place
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Tree $tree
 * @property-read Individual|null $husband
 * @property-read Individual|null $wife
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Individual> $children
 */
final class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'tree_id',
        'gedcom_xref',
        'husband_id',
        'wife_id',
        'marriage_date',
        'marriage_place',
        'marriage_type',
        'divorce_date',
        'divorce_place',
    ];

    protected $casts = [
        'marriage_date' => 'date',
        'divorce_date' => 'date',
    ];

    /**
     * Get the tree that owns the family.
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    /**
     * Get the husband individual.
     */
    public function husband(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'husband_id');
    }

    /**
     * Get the wife individual.
     */
    public function wife(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'wife_id');
    }

    /**
     * Get the children of this family.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'family_children', 'family_id', 'child_id')
            ->withPivot('child_order')
            ->orderBy('family_children.child_order');
    }

    /**
     * Get all spouses in this family.
     */
    public function spouses(): \Illuminate\Database\Eloquent\Collection
    {
        $spouses = collect();

        if ($this->husband) {
            $spouses->push($this->husband);
        }

        if ($this->wife) {
            $spouses->push($this->wife);
        }

        return $spouses;
    }

    /**
     * Get all family members (spouses + children).
     */
    public function allMembers(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->spouses()->merge($this->children);
    }

    /**
     * Check if the family has any children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if the family has any spouses.
     */
    public function hasSpouses(): bool
    {
        return $this->husband_id !== null || $this->wife_id !== null;
    }

    /**
     * Get the marriage status of the family.
     */
    public function getMarriageStatus(): string
    {
        if ($this->divorce_date) {
            return 'divorced';
        }

        if ($this->marriage_date) {
            return 'married';
        }

        return 'unknown';
    }

    /**
     * Scope to filter by tree.
     */
    public function scopeForTree($query, int $treeId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tree_id', $treeId);
    }

    /**
     * Scope to filter families with children.
     */
    public function scopeWithChildren($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereHas('children');
    }

    /**
     * Scope to filter families without children.
     */
    public function scopeWithoutChildren($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereDoesntHave('children');
    }

    /**
     * Scope to filter by marriage date range.
     */
    public function scopeMarriedBetween($query, string $startDate, string $endDate): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereBetween('marriage_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by marriage place.
     */
    public function scopeMarriedAt($query, string $place): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('marriage_place', 'like', "%{$place}%");
    }
}
