<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\TreeFactory>
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

    /**
     * @return BelongsTo<User, Tree>
     */
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
