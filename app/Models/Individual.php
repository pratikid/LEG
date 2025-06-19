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
     * Sex constants for better code readability
     */
    public const SEX_MALE = 'M';
    public const SEX_FEMALE = 'F';
    public const SEX_UNKNOWN = 'U';

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
     * Scope to filter males only
     */
    public function scopeWhereMale($query)
    {
        return $query->whereSex(self::SEX_MALE);
    }

    /**
     * Scope to filter females only
     */
    public function scopeWhereFemale($query)
    {
        return $query->whereSex(self::SEX_FEMALE);
    }

    /**
     * Scope to filter unknown sex only
     */
    public function scopeWhereUnknownSex($query)
    {
        return $query->whereSex(self::SEX_UNKNOWN);
    }

    /**
     * Scope to filter known sex (male or female, excluding unknown)
     */
    public function scopeWhereKnownSex($query)
    {
        return $query->whereSexIn([self::SEX_MALE, self::SEX_FEMALE]);
    }

    /**
     * Check if sex value is valid
     */
    public static function isValidSex(string $sex): bool
    {
        return static::isValidEnumValue('sex', $sex);
    }

    /**
     * Check if individual is male
     */
    public function isMale(): bool
    {
        return $this->sex === self::SEX_MALE;
    }

    /**
     * Check if individual is female
     */
    public function isFemale(): bool
    {
        return $this->sex === self::SEX_FEMALE;
    }

    /**
     * Check if individual's sex is unknown
     */
    public function isUnknownSex(): bool
    {
        return $this->sex === self::SEX_UNKNOWN;
    }

    /**
     * Check if individual has known sex (male or female)
     */
    public function hasKnownSex(): bool
    {
        return in_array($this->sex, [self::SEX_MALE, self::SEX_FEMALE], true);
    }

    /**
     * Get human-readable sex label
     */
    public function getSexLabel(): string
    {
        return match ($this->sex) {
            self::SEX_MALE => 'Male',
            self::SEX_FEMALE => 'Female',
            self::SEX_UNKNOWN => 'Unknown',
            default => 'Unknown',
        };
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
