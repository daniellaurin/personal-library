<?php
/*
 * file: index.php
 * description: landing page. shows site stats and recent books from the library.
 */

$base        = '';
$pageTitle   = 'HOME';
$pageDesc    = 'Personal Library — your Letterboxd for books. Search, collect, and review.';
$currentPage = 'home';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/header.php';

$db = getDB();

/*  site stats  */
$bookCount   = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) AS c FROM Books'))['c'];
$userCount   = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) AS c FROM Users'))['c'];
$reviewCount = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) AS c FROM Reviews'))['c'];

/*  six most recently added books */
$recent = mysqli_query($db, 'SELECT id, title, author, cover_url FROM Books ORDER BY created_at DESC LIMIT 6');
mysqli_close($db);
?>

<main class="page-wrapper">

  <section class="hero">
    <div class="hero-text">
      <h1 class="hero-title">YOUR PERSONAL LIBRARY</h1>
      <p class="hero-sub">SEARCH MILLIONS OF BOOKS &middot; BUILD YOUR COLLECTION &middot; SHARE YOUR REVIEWS</p>
      <div class="hero-cta">
        <a href="bookSearch.php" class="btn btn-primary">🔍 SEARCH BOOKS</a>
        <?php if (!isLoggedIn()): ?>
        <a href="register.php" class="btn btn-secondary">CREATE ACCOUNT</a>
        <?php else: ?>
        <a href="displayBooks.php" class="btn btn-secondary">VIEW LIBRARY</a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="stats-bar">
    <div class="stat">
      <span class="stat-number"><?= $bookCount ?></span>
      <span class="stat-label">BOOKS</span>
    </div>
    <div class="stat">
      <span class="stat-number"><?= $userCount ?></span>
      <span class="stat-label">READERS</span>
    </div>
    <div class="stat">
      <span class="stat-number"><?= $reviewCount ?></span>
      <span class="stat-label">REVIEWS</span>
    </div>
  </section>

  <?php if (mysqli_num_rows($recent) > 0): ?>
  <section>
    <h2 class="section-title">RECENTLY ADDED</h2>
    <div class="book-grid">
      <?php while ($book = mysqli_fetch_assoc($recent)): ?>
      <a href="viewBook.php?id=<?= $book['id'] ?>" class="book-card">
        <?php if (!empty($book['cover_url'])): ?>
          <img src="<?= htmlspecialchars($book['cover_url']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="book-card__cover">
        <?php else: ?>
          <div class="book-card__cover-placeholder">📖</div>
        <?php endif; ?>
        <span class="book-card__title"><?= htmlspecialchars($book['title']) ?></span>
        <span class="book-card__author"><?= htmlspecialchars($book['author']) ?></span>
      </a>
      <?php endwhile; ?>
    </div>
  </section>
  <?php else: ?>
  <section class="empty-state">
    <p class="empty-icon">📚</p>
    <p class="empty-text">THE LIBRARY IS EMPTY</p>
    <a href="bookSearch.php" class="btn btn-primary">ADD THE FIRST BOOK</a>
  </section>
  <?php endif; ?>

</main>

<?php require_once 'includes/footer.php'; ?>
