<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $tree_id
 * @property string|null $gedcom_xref
 * @property string $name
 * @property string|null $address_line1
 * @property string|null $address_line2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $website
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Tree $tree
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Source> $sources
 */
final class Repository extends Model
{
    use HasFactory;

    protected $fillable = [
        'tree_id',
        'gedcom_xref',
        'name',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'website',
    ];

    /**
     * Get the tree that owns the repository.
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    /**
     * Get the sources associated with this repository.
     */
    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    /**
     * Get the full address as a formatted string.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the contact information as a formatted string.
     */
    public function getContactInfoAttribute(): string
    {
        $parts = array_filter([
            $this->phone,
            $this->email,
            $this->website,
        ]);

        return implode(' | ', $parts);
    }

    /**
     * Check if the repository has contact information.
     */
    public function hasContactInfo(): bool
    {
        return ! empty($this->phone) || ! empty($this->email) || ! empty($this->website);
    }

    /**
     * Check if the repository has address information.
     */
    public function hasAddress(): bool
    {
        return ! empty($this->address_line1) || ! empty($this->city);
    }

    /**
     * Scope to filter by tree.
     */
    public function scopeForTree($query, int $treeId)
    {
        return $query->where('tree_id', $treeId);
    }

    /**
     * Scope to filter by name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope to filter by country.
     */
    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', 'like', "%{$country}%");
    }

    /**
     * Scope to filter by city.
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Scope to filter repositories with contact information.
     */
    public function scopeWithContactInfo($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('phone')
                ->orWhereNotNull('email')
                ->orWhereNotNull('website');
        });
    }

    /**
     * Scope to filter repositories with address information.
     */
    public function scopeWithAddress($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('address_line1')
                ->orWhereNotNull('city');
        });
    }
}
