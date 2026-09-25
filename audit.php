<?php
header("Content-Type: text/plain; charset=utf-8");
echo "===== FILE TREE =====\n";
function listFiles($dir, $prefix = "") {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === "." || $item === "..") continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            echo $prefix . $item . "/\n";
            listFiles($path, $prefix . "  ");
        } else {
            echo $prefix . $item . " (" . filesize($path) . " bytes)\n";
        }
    }
}
listFiles(__DIR__);

$filesToShow = ["db.php", "config.php", "header.php", "footer.php", "index.php"];
foreach ($filesToShow as $f) {
    echo "\n===== " . $f . " =====\n";
    $p = __DIR__ . DIRECTORY_SEPARATOR . $f;
    echo file_exists($p) ? file_get_contents($p) : "NOT FOUND\n";
}

echo "\n===== DATABASE STRUCTURE =====\n";
try {
    if (file_exists(__DIR__ . "/db.php")) {
        include __DIR__ . "/db.php";
    }
    if (isset($conn) && $conn) {
        $res = $conn->query("SHOW TABLES");
        while ($row = $res->fetch_array()) {
            $t = $row[0];
            echo "\n--- Table: $t ---\n";
            $c = $conn->query("SHOW CREATE TABLE `$t`")->fetch_assoc();
            echo $c["Create Table"] . "\n";
            $cnt = $conn->query("SELECT COUNT(*) as c FROM `$t`")->fetch_assoc();
            echo "Row count: " . $cnt["c"] . "\n";
        }
    } else {
        echo "اتصال دیتابیس برقرار نشد یا متغیر \$conn در db.php تعریف نشده.\n";
    }
} catch (Throwable $e) {
    echo "خطا: " . $e->getMessage() . "\n";
}
?>
