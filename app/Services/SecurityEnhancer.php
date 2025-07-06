<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Security enhancement service for LEG platform
 */
final class SecurityEnhancer
{
    private const MIN_PASSWORD_LENGTH = 12;
    private const PASSWORD_COMPLEXITY_RULES = [
        'min:12',
        'regex:/[a-z]/', // lowercase
        'regex:/[A-Z]/', // uppercase
        'regex:/[0-9]/', // numbers
        'regex:/[@$!%*?&]/', // special characters
    ];

    /**
     * Validate and sanitize GEDCOM content
     */
    public function validateGedcomContent(string $gedcomContent): array
    {
        $errors = [];
        
        // Check file size (max 50MB)
        if (strlen($gedcomContent) > 50 * 1024 * 1024) {
            $errors[] = 'GEDCOM file size exceeds 50MB limit';
        }
        
        // Check for malicious content
        $suspiciousPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $gedcomContent)) {
                $errors[] = 'GEDCOM contains potentially malicious content';
                break;
            }
        }
        
        // Validate GEDCOM structure
        if (!str_contains($gedcomContent, '0 HEAD') || !str_contains($gedcomContent, '0 TRLR')) {
            $errors[] = 'Invalid GEDCOM format: Missing required HEAD or TRLR tags';
        }
        
        // Check for null bytes
        if (str_contains($gedcomContent, "\0")) {
            $errors[] = 'GEDCOM contains null bytes';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'sanitized_content' => $this->sanitizeGedcomContent($gedcomContent),
        ];
    }

    /**
     * Sanitize GEDCOM content
     */
    public function sanitizeGedcomContent(string $gedcomContent): string
    {
        // Remove null bytes
        $gedcomContent = str_replace("\0", '', $gedcomContent);
        
        // Remove control characters except newlines and tabs
        $gedcomContent = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $gedcomContent);
        
        // Normalize line endings
        $gedcomContent = str_replace(["\r\n", "\r"], "\n", $gedcomContent);
        
        // Remove excessive whitespace
        $gedcomContent = preg_replace('/[ \t]+/', ' ', $gedcomContent);
        
        return $gedcomContent;
    }

    /**
     * Validate password strength
     */
    public function validatePasswordStrength(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $errors[] = "Password must be at least " . self::MIN_PASSWORD_LENGTH . " characters long";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[@$!%*?&]/', $password)) {
            $errors[] = 'Password must contain at least one special character (@$!%*?&)';
        }
        
        // Check for common passwords
        $commonPasswords = [
            'password', '123456', 'qwerty', 'admin', 'letmein',
            'welcome', 'monkey', 'dragon', 'master', 'football'
        ];
        
        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = 'Password is too common';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength_score' => $this->calculatePasswordStrength($password),
        ];
    }

    /**
     * Calculate password strength score (0-100)
     */
    private function calculatePasswordStrength(string $password): int
    {
        $score = 0;
        
        // Length contribution
        $score += min(25, strlen($password) * 2);
        
        // Character variety contribution
        $variety = 0;
        if (preg_match('/[a-z]/', $password)) $variety++;
        if (preg_match('/[A-Z]/', $password)) $variety++;
        if (preg_match('/[0-9]/', $password)) $variety++;
        if (preg_match('/[@$!%*?&]/', $password)) $variety++;
        
        $score += $variety * 15;
        
        // Entropy contribution
        $entropy = 0;
        $charSet = 0;
        if (preg_match('/[a-z]/', $password)) $charSet += 26;
        if (preg_match('/[A-Z]/', $password)) $charSet += 26;
        if (preg_match('/[0-9]/', $password)) $charSet += 10;
        if (preg_match('/[@$!%*?&]/', $password)) $charSet += 8;
        
        if ($charSet > 0) {
            $entropy = strlen($password) * log($charSet, 2);
            $score += min(30, $entropy / 2);
        }
        
        return min(100, $score);
    }

    /**
     * Generate secure password
     */
    public function generateSecurePassword(): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '@$!%*?&';
        
        $password = '';
        
        // Ensure at least one of each type
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        // Fill remaining length with random characters
        $allChars = $lowercase . $uppercase . $numbers . $special;
        for ($i = 4; $i < self::MIN_PASSWORD_LENGTH; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Validate user input for XSS prevention
     */
    public function validateUserInput(array $data, array $rules): array
    {
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
            ];
        }
        
        // Additional XSS checks
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = $this->sanitizeString($value);
            } else {
                $sanitizedData[$key] = $value;
            }
        }
        
        return [
            'valid' => true,
            'data' => $sanitizedData,
        ];
    }

    /**
     * Sanitize string input
     */
    public function sanitizeString(string $input): string
    {
        // Remove HTML tags
        $input = strip_tags($input);
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        // Remove control characters
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        return trim($input);
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload($file, array $allowedTypes = [], int $maxSize = 5242880): array
    {
        $errors = [];
        
        if (!$file || !$file->isValid()) {
            $errors[] = 'Invalid file upload';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size (default 5MB)
        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds limit of ' . ($maxSize / 1024 / 1024) . 'MB';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
            }
        }
        
        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        $dangerousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'sh'];
        if (in_array($extension, $dangerousExtensions)) {
            $errors[] = 'File extension not allowed';
        }
        
        // Check for null bytes in filename
        if (str_contains($file->getClientOriginalName(), "\0")) {
            $errors[] = 'Invalid filename';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Generate secure random token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Validate email address
     */
    public function validateEmail(string $email): array
    {
        $errors = [];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Check for disposable email domains
        $disposableDomains = [
            'tempmail.org', 'guerrillamail.com', 'mailinator.com',
            '10minutemail.com', 'throwaway.email'
        ];
        
        $domain = substr(strrchr($email, "@"), 1);
        if (in_array($domain, $disposableDomains)) {
            $errors[] = 'Disposable email addresses are not allowed';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        Log::channel('security')->warning($event, array_merge($context, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ]));
    }

    /**
     * Check for suspicious activity
     */
    public function detectSuspiciousActivity(User $user): array
    {
        $suspicious = [];
        
        // Check for multiple failed login attempts
        $failedAttempts = $user->failed_login_attempts ?? 0;
        if ($failedAttempts > 5) {
            $suspicious[] = 'Multiple failed login attempts';
        }
        
        // Check for unusual login times
        $lastLogin = $user->last_login_at;
        if ($lastLogin) {
            $hour = $lastLogin->hour;
            if ($hour < 6 || $hour > 23) {
                $suspicious[] = 'Unusual login time';
            }
        }
        
        // Check for rapid successive actions
        $recentActions = $user->activityLogs()
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();
        
        if ($recentActions > 50) {
            $suspicious[] = 'Unusual activity volume';
        }
        
        return [
            'suspicious' => !empty($suspicious),
            'indicators' => $suspicious,
        ];
    }

    /**
     * Encrypt sensitive data
     */
    public function encryptData(string $data): string
    {
        return encrypt($data);
    }

    /**
     * Decrypt sensitive data
     */
    public function decryptData(string $encryptedData): string
    {
        return decrypt($encryptedData);
    }

    /**
     * Hash sensitive data (one-way)
     */
    public function hashData(string $data): string
    {
        return Hash::make($data);
    }

    /**
     * Verify hashed data
     */
    public function verifyHash(string $data, string $hash): bool
    {
        return Hash::check($data, $hash);
    }
} 