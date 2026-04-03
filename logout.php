<?php
/*
 * file: logout.php
 * description: destroys the session and redirects to the home page.
 */

require_once 'includes/auth.php';
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
