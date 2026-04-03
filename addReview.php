<?php
/*
 * file: addReview.php
 * description: add or edit a review for a book. requires login.
 * accepts ?book_id= to pre-select the book.
 * satisfies rubric requirement 4 (dynamic form) and supports the two-option-per-book requirement
 * (format + status are the two configurable options per book per user).
 */

$base        = '';
$pageTitle   = 'WRITE A REVIEW';
$pageDesc    = 'Rate and review a book in your Personal Library.';
$currentPage = 'library';
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();

$user   = currentUser();
$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
if ($bookId <= 0) {
    header('Location: displayBooks.php');
    exit;
}

$db = getDB();

/* fetch book */
$stmt = mysqli_prepare($db, 'SELECT id, title, author, cover_url FROM Books WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $bookId);
mysqli_stmt_execute($stmt);
$book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$book) {
    mysqli_close($db);
    header('Location: displayBooks.php');
    exit;
}

/*  check for existing review to pre-fill the form = */
$stmt = mysqli_prepare($db, 'SELECT * FROM Reviews WHERE user_id = ? AND book_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $user['id'], $bookId);
mysqli_stmt_execute($stmt);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$defaults = [
    'rating'      => $existing['rating']      ?? 0,
    'format'      => $existing['format']      ?? 'Paperback',
    'status'      => $existing['status']      ?? 'Read',
    'review_text' => $existing['review_text'] ?? '',
];

$errors = [];

/*  handle form submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating  = (int)($_POST['rating'] ?? 0);
    $format  = $_POST['format']  ?? 'Paperback';
    $status  = $_POST['status']  ?? 'Read';
    $text    = trim($_POST['review_text'] ?? '');

    $validFormats  = ['Hardcover', 'Paperback', 'Ebook', 'Audiobook'];
    $validStatuses = ['Want to Read', 'Currently Reading', 'Read'];


    #if ($rating < 1 || $rating > 5) {
        #$errors[] = 'please select a star rating between 1 and 5.';
    #}
    if (!in_array($format, $validFormats)) {
        $errors[] = 'please select a valid reading format.';
    }
    if (!in_array($status, $validStatuses)) {
        $errors[] = 'please select a valid reading status.';
    }

    $defaults = compact('rating', 'format', 'status') + ['review_text' => $text];

    if (empty($errors)) {
        if ($existing) {
            /* update existing review */
            $stmt = mysqli_prepare($db, '
                UPDATE Reviews SET rating = ?, format = ?, status = ?, review_text = ?
                WHERE user_id = ? AND book_id = ?');
            mysqli_stmt_bind_param($stmt, 'isssii', $rating, $format, $status, $text, $user['id'], $bookId);
        } else {
            /* insert new review */
            $stmt = mysqli_prepare($db, '
                INSERT INTO Reviews (user_id, book_id, rating, format, status, review_text)
                VALUES (?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iiisss', $user['id'], $bookId, $rating, $format, $status, $text);
        }

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            mysqli_close($db);
            header('Location: viewBook.php?id=' . $bookId);
            exit;
        }
        $errors[] = 'could not save your review. please try again.';
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($db);

$pageTitle = 'REVIEW: ' . strtoupper($book['title']);
require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title"><?= $existing ? 'EDIT YOUR REVIEW' : 'WRITE A REVIEW' ?></h1>
    <p class="page-subtitle"><?= htmlspecialchars(strtoupper($book['title'])) ?></p>
  </div>

  <div class="form-card" style="max-width:640px;">
    <div class="review-book-preview">
      <?php if (!empty($book['cover_url'])): ?>
        <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="" class="review-book-preview__cover">
      <?php endif; ?>
      <div>
        <p class="review-book-preview__title"><?= htmlspecialchars($book['title']) ?></p>
        <p class="review-book-preview__author"><?= htmlspecialchars($book['author']) ?></p>
      </div>
    </div>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="addReview.php?book_id=<?= $bookId ?>">

      <div class="form-group">
        <label class="form-label">YOUR RATING</label>
        <div class="star-rating" role="radiogroup" aria-label="star rating">
          <?php for ($i = 5; $i >= 1; $i--): ?>
            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                   <?= (int)$defaults['rating'] === $i ? 'checked' : '' ?>>
            <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label>
          <?php endfor; ?>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="format">READING FORMAT</label>
        <select class="form-select" id="format" name="format">
          <?php foreach (['Hardcover','Paperback','Ebook','Audiobook'] as $f): ?>
            <option value="<?= $f ?>" <?= $defaults['format'] === $f ? 'selected' : '' ?>><?= $f ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="status">READING STATUS</label>
        <select class="form-select" id="status" name="status">
          <?php foreach (['Want to Read','Currently Reading','Read'] as $s): ?>
            <option value="<?= $s ?>" <?= $defaults['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="review_text">YOUR REVIEW <span style="font-weight:300;">(OPTIONAL)</span></label>
        <textarea class="form-textarea" id="review_text" name="review_text"
                  placeholder="What did you think?" rows="5"><?= htmlspecialchars($defaults['review_text']) ?></textarea>
      </div>

      <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">SAVE REVIEW</button>
        <a href="viewBook.php?id=<?= $bookId ?>" class="btn btn-secondary">CANCEL</a>
      </div>
    </form>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
