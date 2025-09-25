<?php
require_once 'includes/db.php';

$dbInstance = new Database();
$connection = $dbInstance->getConnection();

try {
    $stmt = $connection->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);

    echo "<h1>Conexión correcta ✅</h1>";
    echo "<p>Tablas encontradas en la base de datos:</p>";
    echo "<pre>";
    foreach ($tables as $table) {
        echo $table[0] . PHP_EOL;
    }
    echo "</pre>";
} catch (PDOException $e) {
    echo "<h1>Error al listar tablas ❌</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
