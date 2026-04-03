<?php
/*
 * file: admin/index.php
 * description: admin dashboard. shows site-wide stats and quick action links.
 * restricted to users with role = 'admin'.
 */

$base        = '../';
$pageTitle   = 'ADMIN DASHBOARD';
$pageDesc    = 'Personal Library admin control panel.';
$currentPage = 'admin';
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin('../');
require_once '../includes/header.php';

$db = getDB();

/* aggregate stats */
$books   = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) AS c FROM Books'))['c'];
$users   = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) AS c FROM Users'))['c'];
$reviews = mysqli_fetch_assoc(mysqli_query($db, 'SELECT COUNT(*) AS c FROM Reviews'))['c'];
$admins  = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) AS c FROM Users WHERE role='admin'"))['c'];

/* recent activity  */
$recentReviews = mysqli_query($db, "
    SELECT r.created_at, r.rating, b.title AS book_title, u.username
    FROM Reviews r
    JOIN Books b ON b.id = r.book_id
    JOIN Users u ON u.id = r.user_id
    ORDER BY r.created_at DESC LIMIT 5");

$recentBooks = mysqli_query($db, "
    SELECT id, title, author, created_at FROM Books ORDER BY created_at DESC LIMIT 5");

mysqli_close($db);
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">ADMIN DASHBOARD</h1>
    <p class="page-subtitle">SITE MANAGEMENT</p>
  </div>

  <div class="admin-stats">
    <div class="stat-card"><span class="stat-number"><?= $books ?></span><span class="stat-label">BOOKS</span></div>
    <div class="stat-card"><span class="stat-number"><?= $users ?></span><span class="stat-label">USERS</span></div>
    <div class="stat-card"><span class="stat-number"><?= $reviews ?></span><span class="stat-label">REVIEWS</span></div>
    <div class="stat-card"><span class="stat-number"><?= $admins ?></span><span class="stat-label">ADMINS</span></div>
  </div>

  <div class="admin-grid">

    <div class="panel">
      <h2 class="section-title">QUICK ACTIONS</h2>
      <div class="action-links" style="justify-content:flex-start;">
        <a href="users.php">MANAGE USERS</a>
        <a href="books.php">MANAGE BOOKS</a>
        <a href="monitor.php">SITE MONITOR</a>
        <a href="../createBooksTable.php">RUN DB SETUP</a>
        <a href="../bookSearch.php">ADD A BOOK</a>
      </div>
    </div>

    <div class="panel">
      <h2 class="section-title">RECENT REVIEWS</h2>
      <?php if (mysqli_num_rows($recentReviews) === 0): ?>
        <p class="text-muted">NO REVIEWS YET.</p>
      <?php else: ?>
        <table class="data-table">
          <thead><tr><th>USER</th><th>BOOK</th><th>RATING</th><th>DATE</th></tr></thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($recentReviews)): ?>
            <tr>
              <td><?= htmlspecialchars($row['username']) ?></td>
              <td><?= htmlspecialchars($row['book_title']) ?></td>
              <td><?= str_repeat('★', (int)$row['rating']) ?><?= str_repeat('☆', 5 - (int)$row['rating']) ?></td>
              <td><?= date('M j, Y', strtotime($row['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h2 class="section-title">RECENTLY ADDED BOOKS</h2>
      <?php if (mysqli_num_rows($recentBooks) === 0): ?>
        <p class="text-muted">NO BOOKS YET.</p>
      <?php else: ?>
        <table class="data-table">
          <thead><tr><th>TITLE</th><th>AUTHOR</th><th>ADDED</th></tr></thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($recentBooks)): ?>
            <tr>
              <td><a href="../viewBook.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
              <td><?= htmlspecialchars($row['author']) ?></td>
              <td><?= date('M j', strtotime($row['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php require_once '../includes/footer.php'; ?>
