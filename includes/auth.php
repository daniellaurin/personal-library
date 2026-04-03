<?php
/*
 * file: includes/auth.php
 * description: session management and authentication helper functions.
 * included by header.php — call requireLogin() or requireAdmin() at top of protected pages.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* returns true if a user is currently logged in */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/* redirects to login if the visitor is not authenticated */
function requireLogin(string $base = '') {
    if (!isLoggedIn()) {
        header('Location: ' . $base . 'login.php');
        exit;
    }
}

/* returns true if the logged-in user has the admin role */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/* redirects non-admins away from restricted pages */
function requireAdmin(string $base = '') {
    requireLogin($base);
    if (!isAdmin()) {
        header('Location: ' . $base . 'index.php');
        exit;
    }
}

/* returns an array with the current user's session data */
function currentUser(): array {
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email'    => $_SESSION['email'] ?? null,
        'role'     => $_SESSION['role'] ?? null,
    ];
}
