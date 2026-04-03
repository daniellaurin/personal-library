<?php
/*
 * file: viewBook.php
 * description: book detail page. shows cover, metadata, and all user reviews.
 * accepts ?id= query parameter for the book id.
 */

$base        = '';
$pageTitle   = 'BOOK DETAIL';
$pageDesc    = 'View book details and reviews.';
$currentPage = 'library';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($bookId <= 0) {
    header('Location: displayBooks.php');
    exit;
}

$db = getDB();

/* fetch book  */
$stmt = mysqli_prepare($db, 'SELECT * FROM Books WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $bookId);
mysqli_stmt_execute($stmt);
$book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$book) {
    mysqli_close($db);
    header('Location: displayBooks.php');
    exit;
}

$pageTitle = strtoupper($book['title']);
$pageDesc  = 'Reviews and details for ' . $book['title'] . ' by ' . $book['author'];

/*  check if current user has already reviewed this book  */
$userReview = null;
if (isLoggedIn()) {
    $user = currentUser();
    $stmt = mysqli_prepare($db, 'SELECT * FROM Reviews WHERE user_id = ? AND book_id = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $user['id'], $bookId);
    mysqli_stmt_execute($stmt);
    $userReview = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

/* fetch all reviews for this book  */
$stmt = mysqli_prepare($db, "
    SELECT r.rating, r.review_text, r.format, r.status, r.created_at, u.username
    FROM Reviews r
    JOIN Users u ON u.id = r.user_id
    WHERE r.book_id = ?
    ORDER BY r.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $bookId);
mysqli_stmt_execute($stmt);
$reviewsRes = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

/*  average rating  */
$avgRes = mysqli_prepare($db, 'SELECT AVG(rating) AS avg, COUNT(*) AS cnt FROM Reviews WHERE book_id = ?');
mysqli_stmt_bind_param($avgRes, 'i', $bookId);
mysqli_stmt_execute($avgRes);
$avgData = mysqli_fetch_assoc(mysqli_stmt_get_result($avgRes));
mysqli_stmt_close($avgRes);
mysqli_close($db);

$avgRating   = $avgData['avg'] ? round($avgData['avg'], 1) : null;
$reviewCount = $avgData['cnt'];

/* star render helper  */
function stars(float $n): string {
    $out = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<span class="star ' . ($i <= round($n) ? 'filled' : '') . '">★</span>';
    }
    return $out . '</span>';
}

require_once 'includes/header.php';
?>

<main class="page-wrapper">

  <section class="book-detail">
    <div class="book-detail__cover-col">
      <?php if (!empty($book['cover_url'])): ?>
        <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="book-detail__cover">
      <?php else: ?>
        <div class="book-detail__cover-placeholder">📖</div>
      <?php endif; ?>
    </div>

    <div class="book-detail__info">
      <h1 class="page-title" style="text-align:left;"><?= htmlspecialchars(strtoupper($book['title'])) ?></h1>
      <p class="book-detail__author">BY <?= htmlspecialchars(strtoupper($book['author'])) ?></p>

      <div class="book-detail__meta">
        <?php if (!empty($book['genre'])): ?>
          <span class="badge"><?= htmlspecialchars($book['genre']) ?></span>
        <?php endif; ?>
        <?php if (!empty($book['yearPublished'])): ?>
          <span class="badge"><?= $book['yearPublished'] ?></span>
        <?php endif; ?>
        <?php if (!empty($book['publisher'])): ?>
          <span class="badge"><?= htmlspecialchars($book['publisher']) ?></span>
        <?php endif; ?>
      </div>

      <?php if ($avgRating): ?>
      <div class="book-detail__rating">
        <?= stars($avgRating) ?>
        <span class="rating-label"><?= $avgRating ?> / 5 &nbsp;&middot;&nbsp; <?= $reviewCount ?> REVIEW<?= $reviewCount !== 1 ? 'S' : '' ?></span>
      </div>
      <?php endif; ?>

      <?php if (!empty($book['description'])): ?>
        <p class="book-detail__desc"><?= nl2br(htmlspecialchars(substr($book['description'], 0, 500))) ?>...</p>
      <?php endif; ?>

      <div class="book-detail__actions">
        <?php if (isLoggedIn()): ?>
          <a href="addReview.php?book_id=<?= $bookId ?>" class="btn btn-primary">
            <?= $userReview ? '✏️ EDIT YOUR REVIEW' : '⭐ WRITE A REVIEW' ?>
          </a>
        <?php else: ?>
          <a href="login.php" class="btn btn-primary">SIGN IN TO REVIEW</a>
        <?php endif; ?>
        <a href="displayBooks.php" class="btn btn-secondary">← LIBRARY</a>
        <?php if (isAdmin()): ?>
          <a href="admin/books.php" class="btn btn-danger">MANAGE</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section>
    <h2 class="section-title">REVIEWS <?php if ($reviewCount > 0): ?>(<?= $reviewCount ?>)<?php endif; ?></h2>

    <?php if ($reviewCount === 0): ?>
      <p class="text-muted text-center">NO REVIEWS YET. BE THE FIRST TO WRITE ONE.</p>
    <?php else: ?>
      <?php while ($rev = mysqli_fetch_assoc($reviewsRes)): ?>
      <div class="review">
        <div class="review__header">
          <span class="review__username"><?= htmlspecialchars(strtoupper($rev['username'])) ?></span>
          <?= stars((int)$rev['rating']) ?>
          <span class="badge"><?= htmlspecialchars($rev['format']) ?></span>
          <span class="badge"><?= htmlspecialchars($rev['status']) ?></span>
          <span class="review__date"><?= date('F j, Y', strtotime($rev['created_at'])) ?></span>
        </div>
        <?php if (!empty($rev['review_text'])): ?>
          <p class="review__body"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </section>

</main>

<?php require_once 'includes/footer.php'; ?>
