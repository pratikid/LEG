<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
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

    public function logActivity(string $action, array $oldValues = null, array $newValues = null)
    {
        if (!Auth::check()) {
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

    public function activities()
    {
        return ActivityLog::where('model_type', get_class($this))
            ->where('model_id', $this->getKey())
            ->orderBy('created_at', 'desc');
    }
} 