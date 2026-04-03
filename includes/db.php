<?php
/*
 * file: includes/db.php
 * description: returns an open mysqli database connection.
 * call getDB() wherever a db connection is needed; caller is responsible for closing it.
 */

require_once __DIR__ . '/../databaseConnectionVariables.php';

function getDB() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$conn) {
        die('database connection failed: ' . mysqli_connect_error());
    }
    mysqli_set_charset($conn, 'utf8');
    return $conn;
}
