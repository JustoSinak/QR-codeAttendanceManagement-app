<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\AuditLog;

class EncryptionService
{
    /**
     * Encrypt sensitive data using AES-256 encryption
     */
    public function encryptSensitiveData(array $data, string $context = 'general'): array
    {
        $sensitiveFields = $this->getSensitiveFields($context);
        $encryptedData = $data;
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $encryptedData[$field] = Crypt::encrypt($data[$field]);
                } catch (\Exception $e) {
                    // Log encryption failure
                    AuditLog::logActivity(
                        'system',
                        'encryption_service',
                        'encryption_failed',
                        "Failed to encrypt field: {$field} in context: {$context}",
                        null,
                        null,
                        null,
                        ['error' => $e->getMessage()]
                    );
                    
                    // Keep original value if encryption fails
                    $encryptedData[$field] = $data[$field];
                }
            }
        }
        
        return $encryptedData;
    }
    
    /**
     * Decrypt sensitive data
     */
    public function decryptSensitiveData(array $data, string $context = 'general'): array
    {
        $sensitiveFields = $this->getSensitiveFields($context);
        $decryptedData = $data;
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                try {
                    $decryptedData[$field] = Crypt::decrypt($data[$field]);
                } catch (DecryptException $e) {
                    // If decryption fails, assume data is not encrypted
                    $decryptedData[$field] = $data[$field];
                } catch (\Exception $e) {
                    // Log decryption failure
                    AuditLog::logActivity(
                        'system',
                        'encryption_service',
                        'decryption_failed',
                        "Failed to decrypt field: {$field} in context: {$context}",
                        null,
                        null,
                        null,
                        ['error' => $e->getMessage()]
                    );
                    
                    $decryptedData[$field] = '[DECRYPTION_FAILED]';
                }
            }
        }
        
        return $decryptedData;
    }
    
    /**
     * Get sensitive fields for a given context
     */
    private function getSensitiveFields(string $context): array
    {
        $sensitiveFields = config('security.encryption.sensitive_fields', []);
        
        return $sensitiveFields[$context] ?? [];
    }
    
    /**
     * Encrypt file content
     */
    public function encryptFile(string $filePath): bool
    {
        try {
            if (!file_exists($filePath)) {
                return false;
            }
            
            $content = file_get_contents($filePath);
            $encryptedContent = Crypt::encrypt($content);
            
            return file_put_contents($filePath . '.encrypted', $encryptedContent) !== false;
        } catch (\Exception $e) {
            AuditLog::logActivity(
                'system',
                'encryption_service',
                'file_encryption_failed',
                "Failed to encrypt file: {$filePath}",
                null,
                null,
                null,
                ['error' => $e->getMessage()]
            );
            
            return false;
        }
    }
    
    /**
     * Decrypt file content
     */
    public function decryptFile(string $encryptedFilePath): ?string
    {
        try {
            if (!file_exists($encryptedFilePath)) {
                return null;
            }
            
            $encryptedContent = file_get_contents($encryptedFilePath);
            return Crypt::decrypt($encryptedContent);
        } catch (\Exception $e) {
            AuditLog::logActivity(
                'system',
                'encryption_service',
                'file_decryption_failed',
                "Failed to decrypt file: {$encryptedFilePath}",
                null,
                null,
                null,
                ['error' => $e->getMessage()]
            );
            
            return null;
        }
    }
    
    /**
     * Generate secure hash for data integrity
     */
    public function generateSecureHash(string $data): string
    {
        return hash('sha256', $data . config('app.key'));
    }
    
    /**
     * Verify data integrity using hash
     */
    public function verifyDataIntegrity(string $data, string $hash): bool
    {
        return hash_equals($hash, $this->generateSecureHash($data));
    }
    
    /**
     * Encrypt database backup
     */
    public function encryptDatabaseBackup(string $backupPath): bool
    {
        if (!config('security.data_protection.backup_encryption')) {
            return true; // Encryption not required
        }
        
        return $this->encryptFile($backupPath);
    }
    
    /**
     * Sanitize input data to prevent injection attacks
     */
    public function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters
                $value = strip_tags($value);
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                
                // Check for SQL injection patterns
                $sqlPatterns = [
                    '/(\bunion\b.*\bselect\b)/i',
                    '/(\bselect\b.*\bfrom\b)/i',
                    '/(\binsert\b.*\binto\b)/i',
                    '/(\bupdate\b.*\bset\b)/i',
                    '/(\bdelete\b.*\bfrom\b)/i',
                    '/(\bdrop\b.*\btable\b)/i',
                ];
                
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        AuditLog::logActivity(
                            'system',
                            'encryption_service',
                            'sql_injection_attempt',
                            "Potential SQL injection detected in input",
                            null,
                            null,
                            null,
                            ['field' => $key, 'value' => $value]
                        );
                        
                        $value = ''; // Clear potentially malicious input
                        break;
                    }
                }
                
                // Check for XSS patterns
                $xssPatterns = [
                    '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
                    '/javascript:/i',
                    '/on\w+\s*=/i',
                ];
                
                foreach ($xssPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        AuditLog::logActivity(
                            'system',
                            'encryption_service',
                            'xss_attempt',
                            "Potential XSS attack detected in input",
                            null,
                            null,
                            null,
                            ['field' => $key, 'value' => $value]
                        );
                        
                        $value = ''; // Clear potentially malicious input
                        break;
                    }
                }
            }
            
            $sanitized[$key] = $value;
        }
        
        return $sanitized;
    }
    
    /**
     * Generate secure random token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Mask sensitive data for logging
     */
    public function maskSensitiveData(array $data, string $context = 'general'): array
    {
        $sensitiveFields = $this->getSensitiveFields($context);
        $maskedData = $data;
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $value = $data[$field];
                if (strlen($value) > 4) {
                    $maskedData[$field] = substr($value, 0, 2) . str_repeat('*', strlen($value) - 4) . substr($value, -2);
                } else {
                    $maskedData[$field] = str_repeat('*', strlen($value));
                }
            }
        }
        
        return $maskedData;
    }
}
