<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * API licensing and usage tracking model
 */
final class ApiLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_key',
        'license_type',
        'permissions',
        'rate_limit',
        'monthly_quota',
        'current_usage',
        'valid_from',
        'valid_until',
        'is_active',
        'allowed_domains',
        'allowed_ips',
        'metadata'
    ];

    protected $casts = [
        'permissions' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'allowed_domains' => 'array',
        'allowed_ips' => 'array',
        'metadata' => 'array'
    ];

    // License types
    public const TYPE_PERSONAL = 'personal';
    public const TYPE_RESEARCH = 'research';
    public const TYPE_COMMERCIAL = 'commercial';
    public const TYPE_ACADEMIC = 'academic';
    public const TYPE_NON_PROFIT = 'non_profit';

    // Permission levels
    public const PERMISSION_READ = 'read';
    public const PERMISSION_WRITE = 'write';
    public const PERMISSION_EXPORT = 'export';
    public const PERMISSION_ANALYTICS = 'analytics';
    public const PERMISSION_PREMIUM = 'premium';
    public const PERMISSION_RESEARCH = 'research';
    public const PERMISSION_BULK = 'bulk';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(ApiUsageLog::class, 'license_key', 'license_key');
    }

    /**
     * Check if license is valid and active
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->valid_from && $now->isBefore($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->isAfter($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if license has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Check if license has exceeded usage quota
     */
    public function hasExceededQuota(): bool
    {
        if ($this->monthly_quota === null) {
            return false; // No quota limit
        }

        return $this->current_usage >= $this->monthly_quota;
    }

    /**
     * Check if request is within rate limit
     */
    public function isWithinRateLimit(string $ipAddress): bool
    {
        if ($this->rate_limit === null) {
            return true; // No rate limit
        }

        // Check recent usage within the last minute
        $recentUsage = $this->usageLogs()
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subMinute())
            ->count();

        return $recentUsage < $this->rate_limit;
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(int $requests = 1): void
    {
        $this->increment('current_usage', $requests);
    }

    /**
     * Reset monthly usage counter
     */
    public function resetMonthlyUsage(): void
    {
        $this->update(['current_usage' => 0]);
    }

    /**
     * Check if domain is allowed
     */
    public function isDomainAllowed(string $domain): bool
    {
        if (empty($this->allowed_domains)) {
            return true; // No domain restrictions
        }

        return in_array($domain, $this->allowed_domains);
    }

    /**
     * Check if IP address is allowed
     */
    public function isIpAllowed(string $ipAddress): bool
    {
        if (empty($this->allowed_ips)) {
            return true; // No IP restrictions
        }

        foreach ($this->allowed_ips as $allowedIp) {
            if ($this->ipMatches($ipAddress, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    private function ipMatches(string $ipAddress, string $allowedPattern): bool
    {
        // Support CIDR notation and wildcards
        if (strpos($allowedPattern, '/') !== false) {
            // CIDR notation
            return $this->ipInCidr($ipAddress, $allowedPattern);
        }

        if (strpos($allowedPattern, '*') !== false) {
            // Wildcard matching
            $pattern = str_replace('*', '.*', preg_quote($allowedPattern, '/'));
            return preg_match('/^' . $pattern . '$/', $ipAddress) === 1;
        }

        // Exact match
        return $ipAddress === $allowedPattern;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);
        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
    }
}
