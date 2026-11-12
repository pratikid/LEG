<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * API usage logging for monitoring and analytics
 */
final class ApiUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key',
        'user_id',
        'endpoint',
        'method',
        'request_data',
        'response_status',
        'response_time',
        'ip_address',
        'user_agent',
        'referer',
        'consumed_quota',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'request_data' => 'array',
        'metadata' => 'array',
        'response_time' => 'integer',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(ApiLicense::class, 'license_key', 'license_key');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this was a successful request
     */
    public function wasSuccessful(): bool
    {
        return $this->response_status >= 200 && $this->response_status < 300;
    }

    /**
     * Check if this was a client error
     */
    public function wasClientError(): bool
    {
        return $this->response_status >= 400 && $this->response_status < 500;
    }

    /**
     * Check if this was a server error
     */
    public function wasServerError(): bool
    {
        return $this->response_status >= 500;
    }

    /**
     * Check if response was slow
     */
    public function wasSlow(int $thresholdMs = 1000): bool
    {
        return $this->response_time > $thresholdMs;
    }
}
