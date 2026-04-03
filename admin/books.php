<?php
/*
 * file: admin/books.php
 * description: admin book management. shows all books with edit and delete links.
 * deletion cascades to reviews via the foreign key constraint.
 */

$base        = '../';
$pageTitle   = 'BOOK MANAGEMENT';
$pageDesc    = 'Manage books in the Personal Library catalogue.';
$currentPage = 'admin';
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin('../');

$db  = getDB();
$msg = '';

/* handle delete */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = mysqli_prepare($db, 'DELETE FROM Books WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $deleteId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $msg = 'BOOK DELETED.';
}

/* fetch all books with stats */
$books = mysqli_query($db, "
    SELECT b.id, b.title, b.author, b.yearPublished, b.genre,
           COUNT(r.id) AS review_count,
           ROUND(AVG(r.rating),1) AS avg_rating
    FROM Books b
    LEFT JOIN Reviews r ON r.book_id = b.id
    GROUP BY b.id
    ORDER BY b.created_at DESC");

mysqli_close($db);
require_once '../includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">BOOK MANAGEMENT</h1>
    <p class="page-subtitle">CATALOGUE ADMINISTRATION</p>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-success" style="max-width:900px;margin:0 auto 1.5rem;"><?= $msg ?></div>
  <?php endif; ?>

  <div class="action-links" style="justify-content:flex-start;margin-bottom:1.5rem;">
    <a href="../bookSearch.php">+ ADD VIA SEARCH</a>
    <a href="../bookForm.php">+ MANUAL ENTRY</a>
  </div>

  <div class="panel">
    <table class="data-table">
      <thead>
        <tr>
          <th>TITLE</th>
          <th>AUTHOR</th>
          <th>YEAR</th>
          <th>GENRE</th>
          <th>REVIEWS</th>
          <th>AVG RATING</th>
          <th>ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($books) === 0): ?>
        <tr><td colspan="7" class="text-muted text-center">NO BOOKS IN THE LIBRARY.</td></tr>
        <?php endif; ?>
        <?php while ($book = mysqli_fetch_assoc($books)): ?>
        <tr>
          <td><a href="../viewBook.php?id=<?= $book['id'] ?>"><?= htmlspecialchars($book['title']) ?></a></td>
          <td><?= htmlspecialchars($book['author']) ?></td>
          <td><?= $book['yearPublished'] ?: '—' ?></td>
          <td><?= htmlspecialchars($book['genre'] ?: '—') ?></td>
          <td><?= $book['review_count'] ?></td>
          <td><?= $book['avg_rating'] ? $book['avg_rating'] . ' / 5' : '—' ?></td>
          <td>
            <form method="POST" style="display:inline;"
                  onsubmit="return confirm('DELETE THIS BOOK AND ALL ITS REVIEWS?');">
              <input type="hidden" name="delete_id" value="<?= $book['id'] ?>">
              <button type="submit" class="btn btn-danger" style="font-size:.75rem;padding:.3rem .7rem;">DELETE</button>
            </form>
          </td>
        </tr>
        <?php /* same thing as } closing */ endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="action-links">
    <a href="index.php">← DASHBOARD</a>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>
