<?php
/*
 * file: bookInsert.php
 * description: handles both api search submissions (from bookSearch.php) and the manual form
 * (from bookForm.php). uses prepared statements to prevent sql injection.
 * if the book already exists (by google_books_id), skips insertion and redirects to it.
 * on success redirects to viewBook.php.
 */

require_once 'includes/auth.php';
require_once 'includes/db.php';

$db = getDB();

/* collect and sanitize post data */
$title    = trim($_POST['title']    ?? $_POST['Title']    ?? '');
$author   = trim($_POST['author']   ?? $_POST['Author']   ?? '');
$pub      = trim($_POST['publisher']?? $_POST['Publisher']?? '');
$year     = trim($_POST['yearPublished'] ?? $_POST['YearPublished'] ?? '');
$cover    = trim($_POST['cover_url']       ?? '');
$gbId     = trim($_POST['google_books_id'] ?? '');
$desc     = trim($_POST['description']     ?? '');
$genre    = trim($_POST['genre']           ?? '');

/*  basic validation  */
if (empty($title) || empty($author)) {
    mysqli_close($db);
    header('Location: bookSearch.php');
    exit;
}

/* check for existing book by google books id  */
if (!empty($gbId)) {
    $stmt = mysqli_prepare($db, 'SELECT id FROM Books WHERE google_books_id = ?');
    mysqli_stmt_bind_param($stmt, 's', $gbId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        /* book already in library — get its id and redirect */
        $stmt->bind_result($existingId);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        header('Location: viewBook.php?id=' . $existingId);
        exit;
    }
    mysqli_stmt_close($stmt);
}

/*  sanitize year to a valid 4-digit value  */
$yearVal = preg_match('/^\d{4}$/', $year) ? (int)$year : null;

/*  insert book  */
$stmt = mysqli_prepare($db, '
    INSERT INTO Books (title, author, publisher, yearPublished, cover_url, google_books_id, description, genre)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'sssissss',
    $title, $author, $pub, $yearVal, $cover, $gbId, $desc, $genre);

if (mysqli_stmt_execute($stmt)) {
    $newId = mysqli_insert_id($db);
    mysqli_stmt_close($stmt);
    mysqli_close($db);
    header('Location: viewBook.php?id=' . $newId);
    exit;
}

/*  fallback on failure  */
mysqli_stmt_close($stmt);
mysqli_close($db);
header('Location: displayBooks.php');
exit;
