<?php
/**
 * Laravel Subfolder Bootstrapper for Hostinger
 * This file captures requests to /rudrapgwebsite/ and forwards them
 * to Laravel, tricking Laravel into thinking it is running at the root.
 */

// Strip the subfolder from the URI so Laravel's router matches '/'
$uri = $_SERVER['REQUEST_URI'];
if (strpos($uri, '/rudrapgwebsite') === 0) {
    $_SERVER['REQUEST_URI'] = substr($uri, strlen('/rudrapgwebsite'));
    if ($_SERVER['REQUEST_URI'] === '') {
        $_SERVER['REQUEST_URI'] = '/';
    }
}

// Adjust SCRIPT_NAME to prevent Laravel from calculating an incorrect base path
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Bootstrap the Laravel application
require __DIR__ . '/adminlaravel/public/index.php';
