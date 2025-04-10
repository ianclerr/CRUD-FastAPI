<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'prueba_fastapi');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'root');

// Establecer la conexión a la base de datos
try {
    $pdo = new PDO("pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}
?>
