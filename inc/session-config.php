<?php
/**
 * Session Configuration
 * Include this file before starting sessions in your pages
 */

// Set session name
session_name('TIIR_ADMIN_SESSION');

// Only configure if session hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    // Determine if connection is secure
    $secure_cookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

    // Configure session cookie parameters for HTTPS domain
    session_set_cookie_params([
        'path' => '/',
        'secure' => $secure_cookie,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

