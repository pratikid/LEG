<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Individual> $individuals
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Group> $groups
 * @property-read int $individual_count
 * @property-read int $group_count
 * @property-read int $generation_count
 *
 * @method static \Database\Factories\TreeFactory factory()
 * @method static \Illuminate\Database\Eloquent\Builder forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder byName(string $name)
 */
class Tree extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function individuals(): HasMany
    {
        return $this->hasMany(Individual::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get the total number of individuals in this tree
     */
    public function getIndividualCountAttribute(): int
    {
        return $this->individuals()->count();
    }

    /**
     * Get the total number of groups in this tree
     */
    public function getGroupCountAttribute(): int
    {
        return $this->groups()->count();
    }

    /**
     * Get the number of generations in this tree
     */
    public function getGenerationCountAttribute(): int
    {
        return $this->individuals()
            ->whereNotNull('birth_date')
            ->selectRaw('EXTRACT(YEAR FROM birth_date) as birth_year')
            ->distinct()
            ->count();
    }

    /**
     * Scope to filter trees by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter trees by name
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }
}
