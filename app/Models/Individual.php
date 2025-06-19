<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Neo4jIndividualService;
use App\Traits\HasPostgresEnums;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property \Illuminate\Support\Carbon|null $death_date
 * @property string|null $sex
 * @property int $tree_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Tree $tree
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\IndividualFactory factory()
 */
class Individual extends Model
{
    use HasFactory;
    use HasPostgresEnums;
    use LogsActivity;

    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'death_date',
        'tree_id',
        'user_id',
        'sex',
    ];

    /**
     * @var array<int, string>
     */
    protected array $dates = [
        'birth_date',
        'death_date',
    ];

    /**
     * Get all possible sex values
     */
    public static function getSexValues(): array
    {
        return static::getEnumValues('sex');
    }

    /**
     * Scope to filter by sex
     */
    public function scopeWhereSex($query, string $sex)
    {
        return $query->whereEnum('sex', $sex);
    }

    /**
     * Scope to filter by multiple sex values
     */
    public function scopeWhereSexIn($query, array $sexes)
    {
        return $query->whereEnumIn('sex', $sexes);
    }

    /**
     * Check if sex value is valid
     */
    public static function isValidSex(string $sex): bool
    {
        return static::isValidEnumValue('sex', $sex);
    }

    /**
     * @return BelongsTo<Tree, Individual>
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    /**
     * @return BelongsTo<User, Individual>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::created(function (Individual $individual) {
            app(Neo4jIndividualService::class)->createIndividualNode([
                'id' => $individual->id,
                'first_name' => $individual->first_name,
                'last_name' => $individual->last_name,
                'birth_date' => $individual->birth_date,
                'death_date' => $individual->death_date,
                'tree_id' => $individual->tree_id,
                'sex' => $individual->sex,
            ]);
        });
        static::updated(function (Individual $individual) {
            app(Neo4jIndividualService::class)->updateIndividualNode([
                'id' => $individual->id,
                'first_name' => $individual->first_name,
                'last_name' => $individual->last_name,
                'birth_date' => $individual->birth_date,
                'death_date' => $individual->death_date,
                'tree_id' => $individual->tree_id,
                'sex' => $individual->sex,
            ]);
        });
        static::deleted(function (Individual $individual) {
            app(Neo4jIndividualService::class)->deleteIndividualNode($individual->id);
        });
    }
}
