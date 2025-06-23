<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Neo4jIndividualService;
use App\Traits\HasPostgresEnums;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string|null $gedcom_xref
 * @property string $first_name
 * @property string $last_name
 * @property string|null $name_prefix
 * @property string|null $name_suffix
 * @property string|null $nickname
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property \Illuminate\Support\Carbon|null $death_date
 * @property string|null $birth_place
 * @property string|null $death_place
 * @property string|null $death_cause
 * @property string|null $pedigree_type
 * @property string|null $sex
 * @property int $tree_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Tree $tree
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Family> $familiesAsHusband
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Family> $familiesAsWife
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Family> $familiesAsChild
 *
 * @method static \Database\Factories\IndividualFactory factory()
 */
class Individual extends Model
{
    use HasFactory;
    use HasPostgresEnums;
    use LogsActivity;

    protected $fillable = [
        'tree_id',
        'gedcom_xref',
        'first_name',
        'last_name',
        'name_prefix',
        'name_suffix',
        'nickname',
        'sex',
        'birth_date',
        'birth_year',
        'birth_date_raw',
        'death_date',
        'death_year',
        'death_date_raw',
        'birth_place',
        'death_place',
        'death_cause',
        'pedigree_type',
        'user_id',
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
     * Get the full name of the individual
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->name_prefix,
            $this->first_name,
            $this->last_name,
            $this->name_suffix,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get the display name (with nickname if available)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->nickname) {
            return "{$this->nickname} ({$this->full_name})";
        }

        return $this->full_name;
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

    /**
     * Get families where this individual is the husband
     */
    public function familiesAsHusband(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'families', 'husband_id', 'id');
    }

    /**
     * Get families where this individual is the wife
     */
    public function familiesAsWife(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'families', 'wife_id', 'id');
    }

    /**
     * Get families where this individual is a child
     */
    public function familiesAsChild(): BelongsToMany
    {
        return $this->belongsToMany(Family::class, 'family_children', 'child_id', 'family_id')
            ->withPivot('child_order')
            ->orderBy('family_children.child_order');
    }

    /**
     * Get all families associated with this individual
     */
    public function allFamilies(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->familiesAsHusband
            ->merge($this->familiesAsWife)
            ->merge($this->familiesAsChild);
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
     * Scope to filter by name (search in first_name and last_name)
     */
    public function scopeByName($query, string $name)
    {
        return $query->where(function ($q) use ($name) {
            $q->where('first_name', 'like', "%{$name}%")
              ->orWhere('last_name', 'like', "%{$name}%")
              ->orWhere('nickname', 'like', "%{$name}%");
        });
    }

    /**
     * Get a display-friendly birth date (full date, year, or raw value).
     */
    public function getBirthDateDisplayAttribute(): ?string
    {
        if ($this->birth_date) {
            return (string) $this->birth_date;
        }
        if ($this->birth_year) {
            return (string) $this->birth_year;
        }
        if ($this->birth_date_raw) {
            return $this->birth_date_raw;
        }
        return null;
    }

    /**
     * Get a display-friendly death date (full date, year, or raw value).
     */
    public function getDeathDateDisplayAttribute(): ?string
    {
        if ($this->death_date) {
            return (string) $this->death_date;
        }
        if ($this->death_year) {
            return (string) $this->death_year;
        }
        if ($this->death_date_raw) {
            return $this->death_date_raw;
        }
        return null;
    }

    protected static function booted(): void
    {
        static::created(function (Individual $individual) {
            app(Neo4jIndividualService::class)->createIndividualNode([
                'id' => $individual->id,
                'gedcom_xref' => $individual->gedcom_xref,
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
                'gedcom_xref' => $individual->gedcom_xref,
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
