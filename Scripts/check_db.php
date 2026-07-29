<?php
// check_db.php
// Script independiente para probar la conexión a MySQL usando las variables de entorno.

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
} else {
    echo "ERROR: No se encontro el archivo .env en la raiz del proyecto. Cree uno basado en .env.example.\n";
    exit(1);
}

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$db   = $_ENV['DB_DATABASE'] ?? 'medicontrol_db';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "SUCCESS: Conexion a la base de datos '$db' establecida correctamente en $host:$port.\n";
} catch (PDOException $e) {
    echo "ERROR: Fallo la conexion a la base de datos. Detalle:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
