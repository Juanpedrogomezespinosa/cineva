<?php
require_once 'includes/db.php';

$db = new Database();
$conn = $db->getConnection();

echo "✅ Conexión exitosa a la base de datos.";
