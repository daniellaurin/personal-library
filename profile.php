<?php
/*
 * file: profile.php
 * description: logged-in user's profile — shows account info, reading stats, and recent reviews.
 */

$base        = '';
$pageTitle   = 'MY PROFILE';
$pageDesc    = 'Your Personal Library reading profile and review history.';
$currentPage = 'profile';
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireLogin();
require_once 'includes/header.php';

$user = currentUser();
$db   = getDB();

/* user's review stats  */
$statsRes = mysqli_query($db, "
    SELECT COUNT(*) AS total,
           AVG(rating) AS avg_rating,
           SUM(status = 'Read') AS read_count,
           SUM(status = 'Currently Reading') AS reading_count,
           SUM(status = 'Want to Read') AS want_count
    FROM Reviews WHERE user_id = {$user['id']}");
$stats = mysqli_fetch_assoc($statsRes);

/* user's reviews with book info  */
$stmt = mysqli_prepare($db, "
    SELECT r.id AS review_id, r.rating, r.review_text, r.format, r.status, r.created_at,
           b.id AS book_id, b.title, b.author, b.cover_url
    FROM Reviews r
    JOIN Books b ON b.id = r.book_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $user['id']);
mysqli_stmt_execute($stmt);
$reviews = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
mysqli_close($db);

/*  helper: render star rating  */
function renderStars(int $n): string {
    $out = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<span class="star ' . ($i <= $n ? 'filled' : '') . '">★</span>';
    }
    return $out . '</span>';
}
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">MY PROFILE</h1>
    <p class="page-subtitle"><?= htmlspecialchars(strtoupper($user['username'])) ?></p>
  </div>

  <div class="profile-grid">

    <aside class="panel profile-sidebar">
      <div class="avatar-block">
        <div class="avatar">📖</div>
        <h2 class="avatar-name"><?= htmlspecialchars($user['username']) ?></h2>
        <p class="avatar-email"><?= htmlspecialchars($user['email']) ?></p>
        <?php if (isAdmin()): ?>
          <span class="badge badge-admin">ADMIN</span>
        <?php endif; ?>
      </div>

      <ul class="stat-list">
        <li><span class="stat-list__label">BOOKS REVIEWED</span><span class="stat-list__val"><?= $stats['total'] ?></span></li>
        <li><span class="stat-list__label">AVG RATING</span><span class="stat-list__val"><?= $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '—' ?></span></li>
        <li><span class="stat-list__label">READ</span><span class="stat-list__val"><?= $stats['read_count'] ?></span></li>
        <li><span class="stat-list__label">READING NOW</span><span class="stat-list__val"><?= $stats['reading_count'] ?></span></li>
        <li><span class="stat-list__label">WANT TO READ</span><span class="stat-list__val"><?= $stats['want_count'] ?></span></li>
      </ul>

      <div style="margin-top:1rem;">
        <a href="bookSearch.php" class="btn btn-primary" style="width:100%;justify-content:center;">ADD A BOOK</a>
      </div>
    </aside>

    <section class="profile-reviews">
      <h2 class="section-title">MY REVIEWS</h2>

      <?php if (mysqli_num_rows($reviews) === 0): ?>
        <div class="empty-state">
          <p class="empty-icon">📚</p>
          <p class="empty-text">NO REVIEWS YET</p>
          <a href="bookSearch.php" class="btn btn-primary">FIND YOUR FIRST BOOK</a>
        </div>
      <?php else: ?>
        <?php while ($r = mysqli_fetch_assoc($reviews)): ?>
        <div class="review-card">
          <a href="viewBook.php?id=<?= $r['book_id'] ?>" class="review-card__cover-link">
            <?php if (!empty($r['cover_url'])): ?>
              <img src="<?= htmlspecialchars($r['cover_url']) ?>" alt="<?= htmlspecialchars($r['title']) ?>" class="review-card__cover">
            <?php else: ?>
              <div class="review-card__cover-placeholder">📖</div>
            <?php endif; ?>
          </a>
          <div class="review-card__body">
            <a href="viewBook.php?id=<?= $r['book_id'] ?>" class="review-card__title"><?= htmlspecialchars($r['title']) ?></a>
            <p class="review-card__author"><?= htmlspecialchars($r['author']) ?></p>
            <div class="review-card__meta">
              <?= renderStars((int)$r['rating']) ?>
              <span class="badge"><?= htmlspecialchars($r['format']) ?></span>
              <span class="badge"><?= htmlspecialchars($r['status']) ?></span>
            </div>
            <?php if (!empty($r['review_text'])): ?>
              <p class="review-card__text"><?= nl2br(htmlspecialchars($r['review_text'])) ?></p>
            <?php endif; ?>
            <p class="review-card__date"><?= date('F j, Y', strtotime($r['created_at'])) ?></p>
            <a href="addReview.php?book_id=<?= $r['book_id'] ?>" class="btn btn-secondary" style="font-size:.8rem;padding:.4rem .9rem;">EDIT REVIEW</a>
          </div>
        </div>
        <?php endwhile; ?>
      <?php endif; ?>
    </section>

  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
