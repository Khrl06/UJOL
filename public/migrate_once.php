<?php
header("Content-Type: text/plain");
$db = require __DIR__ . "/../app/Config/Database.php";
try {
    $dsn = "mysql:host=" . $db["host"] . ";port=" . $db["port"] . ";dbname=" . $db["database"] . ";charset=utf8mb4";
    $pdo = new PDO($dsn, $db["username"], $db["password"], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Connected successfully to " . $db["database"] . "\n";
    
    $sql = file_get_contents(__DIR__ . "/../database/schema.sql");
    // strip create database / use smartexam_db
    $lines = explode("\n", $sql);
    $clean = [];
    $skip = false;
    foreach ($lines as $line) {
        if (stripos(trim($line), "CREATE DATABASE") === 0) { $skip = true; continue; }
        if ($skip) { if (strpos($line, ";") !== false) { $skip = false; } continue; }
        if (stripos(trim($line), "USE `smartexam_db`") === 0) { continue; }
        $clean[] = $line;
    }
    $cleanSql = implode("\n", $clean);
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $stmt = $pdo->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($allTables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Dropped all old tables.\\n";

    $pdo->exec($cleanSql);
    echo "Schema executed successfully!\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in DB: " . implode(", ", $tables) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
