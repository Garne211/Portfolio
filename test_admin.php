<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test 1: Current directory = " . __DIR__ . "\n";
echo "Test 2: Checking includes directory...\n";

$includes_dir = __DIR__ . '/includes';
echo "Includes dir: " . $includes_dir . "\n";
echo "Exists: " . (is_dir($includes_dir) ? 'YES' : 'NO') . "\n";

echo "\nTest 3: Checking db.php...\n";
$db_file = $includes_dir . '/db.php';
echo "DB file: " . $db_file . "\n";
echo "Exists: " . (file_exists($db_file) ? 'YES' : 'NO') . "\n";
echo "Readable: " . (is_readable($db_file) ? 'YES' : 'NO') . "\n";

echo "\nTest 4: Trying to include db.php...\n";
try {
    require_once $db_file;
    echo "✓ db.php included successfully\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\nTest 5: Testing PDO connection...\n";
try {
    $pdo = new PDO(
        'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . (getenv('DB_NAME') ?: 'portfolio_db'),
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: ''
    );
    echo "✓ Database connection successful\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

echo "\nAll tests completed!\n";
?>
