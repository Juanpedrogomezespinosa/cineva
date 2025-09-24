<?php
// includes/funciones.php

/**
 * Limpia una cadena para prevenir XSS
 */
function limpiarCadena($cadena) {
    return htmlspecialchars(trim($cadena), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida un email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Redirige a una página dada
 */
function redirigir($url) {
    header("Location: $url");
    exit;
}
