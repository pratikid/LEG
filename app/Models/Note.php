<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Note extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'gedcom_xref',
        'text',
        'tree_id',
        'user_id'
    ];

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}