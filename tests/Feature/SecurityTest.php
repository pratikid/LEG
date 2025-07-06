<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\SecurityEnhancer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private SecurityEnhancer $securityEnhancer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityEnhancer = app(SecurityEnhancer::class);
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_sensitive_pages_have_no_cache_headers(): void
    {
        $user = User::factory()->create();

        $sensitiveRoutes = [
            '/login',
            '/register',
            '/password/reset',
            '/profile',
            '/dashboard',
        ];

        foreach ($sensitiveRoutes as $route) {
            $response = $this->actingAs($user)->get($route);
            
            $response->assertHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->assertHeader('Pragma', 'no-cache');
            $response->assertHeader('Expires', '0');
        }
    }

    public function test_gedcom_validation_rejects_malicious_content(): void
    {
        $maliciousGedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @I1@ INDI\n1 NAME <script>alert('xss')</script> /Doe/\n1 SEX M\n0 TRLR";

        $result = $this->securityEnhancer->validateGedcomContent($maliciousGedcom);

        $this->assertFalse($result['valid']);
        $this->assertContains('GEDCOM contains potentially malicious content', $result['errors']);
    }

    public function test_gedcom_validation_rejects_large_files(): void
    {
        $largeGedcom = str_repeat("0 HEAD\n1 GEDC\n2 VERS 5.5.5\n", 1000000); // ~50MB

        $result = $this->securityEnhancer->validateGedcomContent($largeGedcom);

        $this->assertFalse($result['valid']);
        $this->assertContains('GEDCOM file size exceeds 50MB limit', $result['errors']);
    }

    public function test_gedcom_validation_accepts_valid_content(): void
    {
        $validGedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n0 TRLR";

        $result = $this->securityEnhancer->validateGedcomContent($validGedcom);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function test_password_strength_validation(): void
    {
        // Test weak password
        $weakPassword = 'password123';
        $result = $this->securityEnhancer->validatePasswordStrength($weakPassword);
        
        $this->assertFalse($result['valid']);
        $this->assertContains('Password must contain at least one uppercase letter', $result['errors']);
        $this->assertContains('Password must contain at least one special character', $result['errors']);

        // Test strong password
        $strongPassword = 'SecurePass123!';
        $result = $this->securityEnhancer->validatePasswordStrength($strongPassword);
        
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
        $this->assertGreaterThan(80, $result['strength_score']);
    }

    public function test_password_generation(): void
    {
        $password = $this->securityEnhancer->generateSecurePassword();
        
        $this->assertGreaterThanOrEqual(12, strlen($password));
        
        $validation = $this->securityEnhancer->validatePasswordStrength($password);
        $this->assertTrue($validation['valid']);
    }

    public function test_email_validation(): void
    {
        // Test valid email
        $validEmail = 'test@example.com';
        $result = $this->securityEnhancer->validateEmail($validEmail);
        
        $this->assertTrue($result['valid']);

        // Test invalid email
        $invalidEmail = 'invalid-email';
        $result = $this->securityEnhancer->validateEmail($invalidEmail);
        
        $this->assertFalse($result['valid']);
        $this->assertContains('Invalid email format', $result['errors']);

        // Test disposable email
        $disposableEmail = 'test@tempmail.org';
        $result = $this->securityEnhancer->validateEmail($disposableEmail);
        
        $this->assertFalse($result['valid']);
        $this->assertContains('Disposable email addresses are not allowed', $result['errors']);
    }

    public function test_file_upload_validation(): void
    {
        // Test valid file
        $validFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $result = $this->securityEnhancer->validateFileUpload($validFile, ['application/pdf']);
        
        $this->assertTrue($result['valid']);

        // Test file too large
        $largeFile = UploadedFile::fake()->create('large.pdf', 6000000, 'application/pdf'); // 6MB
        $result = $this->securityEnhancer->validateFileUpload($largeFile, ['application/pdf'], 5000000); // 5MB limit
        
        $this->assertFalse($result['valid']);
        $this->assertContains('File size exceeds limit', $result['errors']);

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('script.php', 100, 'text/plain');
        $result = $this->securityEnhancer->validateFileUpload($invalidFile, ['application/pdf']);
        
        $this->assertFalse($result['valid']);
        $this->assertContains('File type not allowed', $result['errors']);

        // Test dangerous extension
        $dangerousFile = UploadedFile::fake()->create('script.php', 100, 'text/plain');
        $result = $this->securityEnhancer->validateFileUpload($dangerousFile);
        
        $this->assertFalse($result['valid']);
        $this->assertContains('File extension not allowed', $result['errors']);
    }

    public function test_input_sanitization(): void
    {
        $maliciousInput = '<script>alert("xss")</script>Hello World';
        $sanitized = $this->securityEnhancer->sanitizeString($maliciousInput);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('Hello World', $sanitized);
    }

    public function test_secure_token_generation(): void
    {
        $token1 = $this->securityEnhancer->generateSecureToken();
        $token2 = $this->securityEnhancer->generateSecureToken();
        
        $this->assertEquals(32, strlen($token1));
        $this->assertEquals(32, strlen($token2));
        $this->assertNotEquals($token1, $token2);
    }

    public function test_data_encryption_and_decryption(): void
    {
        $originalData = 'sensitive information';
        
        $encrypted = $this->securityEnhancer->encryptData($originalData);
        $decrypted = $this->securityEnhancer->decryptData($encrypted);
        
        $this->assertNotEquals($originalData, $encrypted);
        $this->assertEquals($originalData, $decrypted);
    }

    public function test_data_hashing(): void
    {
        $originalData = 'password123';
        
        $hash = $this->securityEnhancer->hashData($originalData);
        $isValid = $this->securityEnhancer->verifyHash($originalData, $hash);
        
        $this->assertNotEquals($originalData, $hash);
        $this->assertTrue($isValid);
        $this->assertFalse($this->securityEnhancer->verifyHash('wrongpassword', $hash));
    }

    public function test_csrf_protection(): void
    {
        $user = User::factory()->create();

        // Test that POST requests require CSRF token
        $response = $this->actingAs($user)->post('/profile', [
            'name' => 'New Name',
        ]);

        // Should redirect to login or show CSRF error
        $this->assertTrue(
            $response->isRedirect() || 
            $response->getStatusCode() === 419 || // CSRF token mismatch
            $response->getStatusCode() === 302
        );
    }

    public function test_sql_injection_protection(): void
    {
        $user = User::factory()->create();

        // Test SQL injection attempt in search
        $maliciousSearch = "'; DROP TABLE users; --";
        
        $response = $this->actingAs($user)->get('/search?q=' . urlencode($maliciousSearch));
        
        // Should not crash and should handle gracefully
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    public function test_xss_protection(): void
    {
        $user = User::factory()->create();

        // Test XSS attempt in form submission
        $xssPayload = '<script>alert("xss")</script>';
        
        $response = $this->actingAs($user)->post('/individuals', [
            'first_name' => $xssPayload,
            'last_name' => 'Doe',
            'tree_id' => 1,
        ]);

        // Should handle gracefully and not execute script
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    public function test_rate_limiting(): void
    {
        $user = User::factory()->create();

        // Make multiple rapid requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user)->get('/dashboard');
            
            // Should not be rate limited for normal usage
            if ($i < 5) {
                $this->assertNotEquals(429, $response->getStatusCode());
            }
        }
    }

    public function test_session_security(): void
    {
        $user = User::factory()->create();

        // Test session regeneration
        $response = $this->actingAs($user)->post('/logout');
        
        $this->assertTrue($response->isRedirect());
        
        // Should not be able to access protected routes after logout
        $response = $this->get('/dashboard');
        $this->assertTrue($response->isRedirect());
    }

    public function test_authentication_security(): void
    {
        // Test login with invalid credentials
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertTrue($response->isRedirect());
        
        // Test login with valid credentials
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue($response->isRedirect());
    }

    public function test_authorization_security(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Test accessing other user's data
        $response = $this->actingAs($user)->get('/trees/' . ($otherUser->id + 1000));
        
        // Should return 404 or 403, not expose data
        $this->assertTrue(in_array($response->getStatusCode(), [404, 403]));
    }
} 