<?php
/*
 * file: displayBooks.php
 * description: shows all books in the library as a responsive card grid.
 * each card links to the book's detail page (viewBook.php).
 * admin users see management links below the grid.
 */

$base        = '';
$pageTitle   = 'LIBRARY';
$pageDesc    = 'Browse all books in the Personal Library collection.';
$currentPage = 'library';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/header.php';

$db = getDB();

/* ── fetch all books with average rating ────────────────────── */
$result = mysqli_query($db, "
    SELECT b.id, b.title, b.author, b.yearPublished, b.cover_url, b.genre,
           ROUND(AVG(r.rating), 1) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM Books b
    LEFT JOIN Reviews r ON r.book_id = b.id
    GROUP BY b.id
    ORDER BY b.created_at DESC");

$totalBooks = mysqli_num_rows($result);
mysqli_close($db);
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">THE LIBRARY</h1>
    <p class="page-subtitle"><?= $totalBooks ?> BOOK<?= $totalBooks !== 1 ? 'S' : '' ?> IN THE COLLECTION</p>
  </div>

  <?php if (isLoggedIn() || isAdmin()): ?>
  <div class="action-links">
    <a href="bookSearch.php">+ ADD VIA SEARCH</a>
    <a href="bookForm.php">+ MANUAL ENTRY</a>
    <?php if (isAdmin()): ?>
    <a href="deleteBookForm.php">REMOVE BOOK</a>
    <a href="updateBookForm.php">EDIT BOOK</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if ($totalBooks === 0): ?>
  <div class="empty-state">
    <p class="empty-icon">📚</p>
    <p class="empty-text">THE LIBRARY IS EMPTY</p>
    <a href="bookSearch.php" class="btn btn-primary">ADD THE FIRST BOOK</a>
  </div>
  <?php else: ?>
  <div class="book-grid">
    <?php while ($book = mysqli_fetch_assoc($result)): ?>
    <a href="viewBook.php?id=<?= $book['id'] ?>" class="book-card">
      <?php if (!empty($book['cover_url'])): ?>
        <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="book-card__cover">
      <?php else: ?>
        <div class="book-card__cover-placeholder">📖</div>
      <?php endif; ?>

      <span class="book-card__title"><?= htmlspecialchars($book['title']) ?></span>
      <span class="book-card__author"><?= htmlspecialchars($book['author']) ?></span>
      <?php if (!empty($book['yearPublished'])): ?>
        <span class="book-card__year"><?= $book['yearPublished'] ?></span>
      <?php endif; ?>

      <?php if ($book['review_count'] > 0): ?>
        <div class="stars" style="margin-top:auto;">
          <?php
          $avg = (float)$book['avg_rating'];
          for ($i = 1; $i <= 5; $i++) {
              echo '<span class="star ' . ($i <= round($avg) ? 'filled' : '') . '">★</span>';
          }
          ?>
          <span style="font-size:.75rem;color:var(--text-muted);margin-left:4px;">(<?= $book['review_count'] ?>)</span>
        </div>
      <?php endif; ?>
    </a>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>
