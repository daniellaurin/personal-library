<?php
/*
 * file: updateBook.php
 * description: handler for updateBookForm.php. uses a prepared statement to update
 * the author field for the given book title. admin only.
 */

require_once 'includes/auth.php';
require_once 'includes/db.php';
requireAdmin();

$title  = trim($_POST['titleInput']  ?? '');
$author = trim($_POST['authorInput'] ?? '');

if (empty($title) || empty($author)) {
    header('Location: updateBookForm.php');
    exit;
}

$db   = getDB();
$stmt = mysqli_prepare($db, 'UPDATE Books SET author = ? WHERE title = ?');
mysqli_stmt_bind_param($stmt, 'ss', $author, $title);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($db);

header('Location: updateBookForm.php?updated=1');
exit;
