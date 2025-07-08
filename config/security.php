<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security settings for the QR Attendance System
    | as specified in the PRD requirements.
    |
    */

    'password_policy' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'max_age_days' => 90, // Force password change after 90 days
        'prevent_reuse_count' => 5, // Prevent reusing last 5 passwords
    ],

    'session_config' => [
        'timeout_minutes' => 30, // 30 minutes session timeout
        'secure_cookies' => env('SESSION_SECURE_COOKIES', true),
        'http_only' => true,
        'same_site' => 'strict',
        'regenerate_on_login' => true,
    ],

    'rate_limiting' => [
        'login_attempts' => [
            'max_attempts' => 5,
            'lockout_duration_minutes' => 5,
            'decay_minutes' => 15, // Reset attempts after 15 minutes
        ],
        'api_calls' => [
            'per_minute' => 60,
            'per_hour' => 1000,
        ],
        'qr_scans' => [
            'per_minute' => 10, // Max 10 QR scans per minute per user
            'duplicate_prevention_seconds' => 60, // Prevent duplicate scans within 1 minute
        ],
    ],

    'encryption' => [
        'algorithm' => 'AES-256-CBC',
        'key_rotation_days' => 365, // Rotate encryption keys annually
        'sensitive_fields' => [
            'employees' => ['emp_mail', 'emp_number', 'salary', 'address', 'emergency_contact', 'emergency_phone'],
            'users' => ['email'],
            'attendance' => ['photo_in', 'photo_out', 'location_in', 'location_out'],
        ],
    ],

    'audit_logging' => [
        'enabled' => true,
        'log_all_actions' => true,
        'retention_days' => 2555, // 7 years retention for compliance
        'sensitive_actions' => [
            'login',
            'logout',
            'password_change',
            'user_created',
            'user_updated',
            'user_deleted',
            'employee_created',
            'employee_updated',
            'employee_deleted',
            'attendance_scan',
            'leave_approved',
            'leave_rejected',
        ],
    ],

    'data_protection' => [
        'anonymize_after_days' => 2555, // 7 years
        'backup_encryption' => true,
        'secure_file_uploads' => true,
        'max_file_size_mb' => 5,
        'allowed_file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],

    'access_control' => [
        'enforce_2fa' => env('ENFORCE_2FA', false),
        'ip_whitelist_enabled' => env('IP_WHITELIST_ENABLED', false),
        'ip_whitelist' => env('IP_WHITELIST', ''),
        'max_concurrent_sessions' => 3,
    ],

    'monitoring' => [
        'failed_login_threshold' => 10, // Alert after 10 failed logins in 1 hour
        'suspicious_activity_threshold' => 50, // Alert after 50 suspicious activities
        'alert_email' => env('SECURITY_ALERT_EMAIL', 'admin@myattendance.com'),
    ],

    'compliance' => [
        'gdpr_enabled' => true,
        'data_retention_policy' => true,
        'privacy_policy_version' => '1.0',
        'terms_of_service_version' => '1.0',
    ],
];
