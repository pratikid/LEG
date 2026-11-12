<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Tree;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait for models that belong to a tree
 */
trait HasTreeRelationship
{
    /**
     * Get the tree that owns this model.
     *
     * @return BelongsTo<Tree, self>
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }
}
