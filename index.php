<?php
declare(strict_types=1);
session_start();

// Redirigir directamente al login.php
require_once __DIR__ . '/includes/config.php';

header('Location: ' . APP_URL . 'usuarios/login.php');
exit;
