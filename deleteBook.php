<?php
/*
 * file: deleteBook.php
 * description: handler for deleteBookForm.php. uses a prepared statement to delete
 * a book by title. cascades to Reviews due to the foreign key constraint. admin only.
 */

require_once 'includes/auth.php';
require_once 'includes/db.php';
requireAdmin();

$title = trim($_POST['title'] ?? '');

if (empty($title)) {
    header('Location: deleteBookForm.php');
    exit;
}

$db   = getDB();
$stmt = mysqli_prepare($db, 'DELETE FROM Books WHERE title = ?');
mysqli_stmt_bind_param($stmt, 's', $title);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($db);

header('Location: deleteBookForm.php?deleted=1');
exit;
