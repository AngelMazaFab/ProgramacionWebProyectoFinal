<?php
// Scripts/update_schema.php

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$db   = $_ENV['DB_DATABASE'] ?? 'medicontrol_db';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
    
    $pdo->exec("USE `$db`");
    $pdo->exec("ALTER TABLE Cobros MODIFY COLUMN metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'meses') NOT NULL DEFAULT 'efectivo'");
    echo "SUCCESS: Base de datos y tablas creadas/actualizadas correctamente.\n";
    echo "SUCCESS: Base de datos y tablas creadas/actualizadas correctamente.\n";

    $pdo->exec("USE `$db`");
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas actuales en '$db':\n";
    foreach ($tables as $t) {
        echo " - $t\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
