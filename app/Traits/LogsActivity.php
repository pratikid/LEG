<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logActivity('create');
        });

        static::updated(function ($model) {
            $model->logActivity('update');
        });

        static::deleted(function ($model) {
            $model->logActivity('delete');
        });
    }

    /**
     * Log an activity for the model.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function logActivity(string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        if (! Auth::check()) {
            return;
        }

        $modelType = get_class($this);
        $modelId = $this->getKey();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get all activity logs for this model.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'model_id')
            ->where('model_type', get_class($this))
            ->orderBy('created_at', 'desc');
    }
}
