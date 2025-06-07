<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $file_path
 * @property string|null $description
 * @property int $user_id
 * @property int $tree_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Tree $tree
 * 
 * @method static \Database\Factories\MediaFactory factory()
 */
class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'description',
        'user_id',
        'tree_id',
    ];
}
