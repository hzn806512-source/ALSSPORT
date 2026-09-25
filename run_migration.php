<?php
// Run Database Migration
require_once 'db.php';

echo "Starting database migration...\n";

$sql_file = __DIR__ . '/migrations_2026.sql';

if (!file_exists($sql_file)) {
    die("Migration file not found: $sql_file\n");
}

$sql = file_get_contents($sql_file);
$queries = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

foreach ($queries as $query) {
    if (empty($query) || strpos($query, '--') === 0) continue;

    if ($conn->query($query)) {
        $success_count++;
        echo "✓ Query executed\n";
    } else {
        $error_count++;
        echo "✗ Error: " . $conn->error . "\n";
        echo "  Query: " . substr($query, 0, 50) . "...\n";
    }
}

echo "\n=== Migration Complete ===\n";
echo "Successful: $success_count\n";
echo "Errors: $error_count\n";

$conn->close();
?>
