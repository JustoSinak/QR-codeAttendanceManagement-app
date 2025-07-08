<?php

/**
 * QR Attendance System - Installation Verification Script
 * 
 * This script verifies that the QR Attendance System is properly installed
 * and all components are working correctly.
 */

echo "🚀 QR Attendance System - Installation Verification\n";
echo "==================================================\n\n";

// Check if we're in the correct directory
if (!file_exists('artisan')) {
    echo "❌ Error: Please run this script from the Laravel project root directory.\n";
    exit(1);
}

// Bootstrap Laravel
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$errors = [];
$warnings = [];

// 1. Check PHP Version
echo "1. 🐘 Checking PHP Version...\n";
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '8.1.0', '>=')) {
    echo "   ✅ PHP Version: $phpVersion (OK)\n";
} else {
    $errors[] = "PHP 8.1+ required, found: $phpVersion";
    echo "   ❌ PHP Version: $phpVersion (Requires 8.1+)\n";
}

// 2. Check Required PHP Extensions
echo "\n2. 🔧 Checking PHP Extensions...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'gd'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext: Loaded\n";
    } else {
        $errors[] = "Missing PHP extension: $ext";
        echo "   ❌ $ext: Missing\n";
    }
}

// Check optional extensions
$optionalExtensions = ['imagick', 'zip', 'curl'];
foreach ($optionalExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext: Loaded (Optional)\n";
    } else {
        $warnings[] = "Optional PHP extension missing: $ext";
        echo "   ⚠️  $ext: Missing (Optional)\n";
    }
}

// 3. Check Environment Configuration
echo "\n3. ⚙️  Checking Environment Configuration...\n";
if (file_exists('.env')) {
    echo "   ✅ .env file exists\n";
    
    // Check key environment variables
    $requiredEnvVars = ['APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
    foreach ($requiredEnvVars as $var) {
        if (env($var)) {
            echo "   ✅ $var: Set\n";
        } else {
            $errors[] = "Environment variable not set: $var";
            echo "   ❌ $var: Not set\n";
        }
    }
} else {
    $errors[] = ".env file not found";
    echo "   ❌ .env file not found\n";
}

// 4. Check Database Connection
echo "\n4. 🗄️  Checking Database Connection...\n";
try {
    DB::connection()->getPdo();
    echo "   ✅ Database connection successful\n";
    
    // Check if migrations have been run
    try {
        $userCount = DB::table('users')->count();
        $employeeCount = DB::table('employees')->count();
        echo "   ✅ Database tables exist (Users: $userCount, Employees: $employeeCount)\n";
    } catch (Exception $e) {
        $warnings[] = "Database tables not found - run 'php artisan migrate'";
        echo "   ⚠️  Database tables not found - run migrations\n";
    }
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
    echo "   ❌ Database connection failed\n";
}

// 5. Check Storage Directories
echo "\n5. 📁 Checking Storage Directories...\n";
$storageDirectories = [
    'storage/app/public/qr_codes',
    'storage/app/public/employee_photos',
    'storage/app/public/attendance_photos',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($storageDirectories as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "   ✅ $dir: Exists and writable\n";
    } else {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "   ✅ $dir: Created\n";
        } else {
            $warnings[] = "Directory not writable: $dir";
            echo "   ⚠️  $dir: Not writable\n";
        }
    }
}

// 6. Check Composer Dependencies
echo "\n6. 📦 Checking Composer Dependencies...\n";
if (file_exists('vendor/autoload.php')) {
    echo "   ✅ Composer dependencies installed\n";
    
    // Check specific packages
    $requiredPackages = [
        'laravel/framework',
        'simplesoftwareio/simple-qrcode',
        'intervention/image'
    ];
    
    foreach ($requiredPackages as $package) {
        if (file_exists("vendor/$package") || class_exists(str_replace('/', '\\', $package))) {
            echo "   ✅ $package: Installed\n";
        } else {
            $warnings[] = "Package may not be installed: $package";
            echo "   ⚠️  $package: Check installation\n";
        }
    }
} else {
    $errors[] = "Composer dependencies not installed";
    echo "   ❌ Composer dependencies not installed\n";
}

// 7. Test QR Code Generation
echo "\n7. 📱 Testing QR Code Generation...\n";
try {
    $testData = json_encode(['test' => 'data', 'timestamp' => time()]);
    $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(200)->generate($testData);

    if ($qrCode) {
        echo "   ✅ QR Code generation working\n";
    } else {
        $warnings[] = "QR Code generation may have issues";
        echo "   ⚠️  QR Code generation test failed\n";
    }
} catch (Exception $e) {
    $warnings[] = "QR Code generation error: " . $e->getMessage();
    echo "   ⚠️  QR Code generation error\n";
}

// 8. Check File Permissions
echo "\n8. 🔐 Checking File Permissions...\n";
$permissionChecks = [
    'storage' => 0755,
    'bootstrap/cache' => 0755,
    '.env' => 0644
];

foreach ($permissionChecks as $path => $expectedPerm) {
    if (file_exists($path)) {
        $actualPerm = fileperms($path) & 0777;
        if ($actualPerm >= $expectedPerm) {
            echo "   ✅ $path: Permissions OK (" . decoct($actualPerm) . ")\n";
        } else {
            $warnings[] = "Insufficient permissions for: $path";
            echo "   ⚠️  $path: Permissions may be insufficient\n";
        }
    }
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 VERIFICATION SUMMARY\n";
echo str_repeat("=", 50) . "\n";

if (empty($errors)) {
    echo "🎉 SUCCESS: Installation verification completed successfully!\n";
    echo "✅ All critical components are working correctly.\n";
} else {
    echo "❌ ERRORS FOUND: " . count($errors) . " critical issues need to be resolved:\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  WARNINGS: " . count($warnings) . " non-critical issues found:\n";
    foreach ($warnings as $warning) {
        echo "   • $warning\n";
    }
}

echo "\n🚀 NEXT STEPS:\n";
if (empty($errors)) {
    echo "1. Start the development server: php artisan serve\n";
    echo "2. Access the application: http://localhost:8000\n";
    echo "3. Login with default credentials (see README.md)\n";
    echo "4. Change default passwords in production!\n";
} else {
    echo "1. Fix the errors listed above\n";
    echo "2. Run this verification script again\n";
    echo "3. Check the installation guide in README.md\n";
}

echo "\n📚 For detailed installation instructions, see README.md\n";
echo "🆘 For support, check the documentation or create an issue\n";

exit(empty($errors) ? 0 : 1);
