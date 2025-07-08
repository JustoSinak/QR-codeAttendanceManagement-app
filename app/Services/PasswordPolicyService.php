<?php

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;

class PasswordPolicyService
{
    /**
     * Validate password against security policy
     */
    public function validatePassword(string $password, ?User $user = null): array
    {
        $errors = [];
        $policy = config('security.password_policy');

        // Check minimum length
        if (strlen($password) < $policy['min_length']) {
            $errors[] = "Password must be at least {$policy['min_length']} characters long.";
        }

        // Check for uppercase letter
        if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        // Check for lowercase letter
        if ($policy['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        // Check for numbers
        if ($policy['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        // Check for special characters
        if ($policy['require_symbols'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        // Check against common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = 'Password is too common. Please choose a more secure password.';
        }

        // Check password reuse if user is provided
        if ($user && $this->isPasswordReused($password, $user)) {
            $errors[] = "Password cannot be one of your last {$policy['prevent_reuse_count']} passwords.";
        }

        // Check for user information in password
        if ($user && $this->containsUserInfo($password, $user)) {
            $errors[] = 'Password cannot contain your username or email.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'strength' => $this->calculatePasswordStrength($password)
        ];
    }

    /**
     * Check if password is commonly used
     */
    private function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123', 'password123',
            'admin', 'letmein', 'welcome', 'monkey', '1234567890', 'password1',
            'qwerty123', 'admin123', 'root', 'toor', 'pass', 'test', 'guest',
            'user', 'demo', 'sample', 'temp', 'default', 'changeme'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Check if password was recently used
     */
    private function isPasswordReused(string $password, User $user): bool
    {
        $preventReuseCount = config('security.password_policy.prevent_reuse_count', 5);
        
        // Get recent password changes from audit log
        $recentPasswordChanges = AuditLog::where('user_type', 'user')
                                        ->where('user_id', $user->id)
                                        ->where('action', 'password_changed')
                                        ->orderBy('created_at', 'desc')
                                        ->limit($preventReuseCount)
                                        ->get();

        foreach ($recentPasswordChanges as $change) {
            if (isset($change->old_values['password']) && 
                Hash::check($password, $change->old_values['password'])) {
                return true;
            }
        }

        // Also check current password
        return Hash::check($password, $user->password);
    }

    /**
     * Check if password contains user information
     */
    private function containsUserInfo(string $password, User $user): bool
    {
        $password = strtolower($password);
        $username = strtolower($user->username);
        $email = strtolower(explode('@', $user->email)[0]);

        return strpos($password, $username) !== false || 
               strpos($password, $email) !== false;
    }

    /**
     * Calculate password strength score (0-100)
     */
    private function calculatePasswordStrength(string $password): int
    {
        $score = 0;
        $length = strlen($password);

        // Length scoring
        if ($length >= 8) $score += 20;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;

        // Character variety scoring
        if (preg_match('/[a-z]/', $password)) $score += 10;
        if (preg_match('/[A-Z]/', $password)) $score += 10;
        if (preg_match('/[0-9]/', $password)) $score += 10;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score += 15;

        // Pattern scoring
        if (preg_match('/[a-z]{2,}/', $password)) $score += 5;
        if (preg_match('/[A-Z]{2,}/', $password)) $score += 5;
        if (preg_match('/[0-9]{2,}/', $password)) $score += 5;

        // Deduct points for common patterns
        if (preg_match('/123|abc|qwe|asd|zxc/i', $password)) $score -= 10;
        if (preg_match('/(.)\1{2,}/', $password)) $score -= 10; // Repeated characters

        return max(0, min(100, $score));
    }

    /**
     * Check if user needs to change password
     */
    public function needsPasswordChange(User $user): bool
    {
        if ($user->force_password_change) {
            return true;
        }

        $maxAgeDays = config('security.password_policy.max_age_days', 90);
        
        if ($user->password_changed_at) {
            return $user->password_changed_at->diffInDays(now()) >= $maxAgeDays;
        }

        return false;
    }

    /**
     * Generate secure password suggestion
     */
    public function generateSecurePassword(int $length = 12): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $password = '';
        
        // Ensure at least one character from each required set
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        // Fill the rest randomly
        $allChars = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Get password policy requirements as array
     */
    public function getPolicyRequirements(): array
    {
        $policy = config('security.password_policy');
        
        return [
            'min_length' => $policy['min_length'],
            'require_uppercase' => $policy['require_uppercase'],
            'require_lowercase' => $policy['require_lowercase'],
            'require_numbers' => $policy['require_numbers'],
            'require_symbols' => $policy['require_symbols'],
            'max_age_days' => $policy['max_age_days'],
            'prevent_reuse_count' => $policy['prevent_reuse_count'],
        ];
    }

    /**
     * Log password policy violation
     */
    public function logPolicyViolation(string $violation, ?User $user = null): void
    {
        AuditLog::logActivity(
            'user',
            $user ? $user->id : 'anonymous',
            'password_policy_violation',
            "Password policy violation: {$violation}",
            null,
            null,
            null,
            ['violation' => $violation]
        );
    }

    /**
     * Get password strength description
     */
    public function getStrengthDescription(int $strength): array
    {
        if ($strength < 30) {
            return ['level' => 'weak', 'color' => 'danger', 'text' => 'Weak'];
        } elseif ($strength < 60) {
            return ['level' => 'fair', 'color' => 'warning', 'text' => 'Fair'];
        } elseif ($strength < 80) {
            return ['level' => 'good', 'color' => 'info', 'text' => 'Good'];
        } else {
            return ['level' => 'strong', 'color' => 'success', 'text' => 'Strong'];
        }
    }
}
