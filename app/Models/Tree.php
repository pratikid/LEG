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
 *
 * @method static \Database\Factories\TreeFactory factory()
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
}
