<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\MediaFactory>
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
