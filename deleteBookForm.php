<?php
/*
 * file: deleteBookForm.php
 * description: form to select and delete a book from the library. admin only.
 * uses a datalist for autocomplete and shows the current book list.
 */

$base        = '';
$pageTitle   = 'REMOVE BOOK';
$pageDesc    = 'Remove a book from the Personal Library.';
$currentPage = 'library';
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireAdmin();

$db     = getDB();
$result = mysqli_query($db, 'SELECT id, title, author FROM Books ORDER BY title');
mysqli_close($db);

$message = '';
if (isset($_GET['deleted'])) {
    $message = 'BOOK DELETED SUCCESSFULLY.';
}

require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">REMOVE A BOOK</h1>
    <p class="page-subtitle">DELETE FROM LIBRARY</p>
  </div>

  <div class="form-card" style="max-width:580px;">
    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) === 0): ?>
      <p class="text-muted">THE LIBRARY IS EMPTY — NOTHING TO DELETE.</p>
    <?php else: ?>
    <form action="deleteBook.php" method="POST"
          onsubmit="return confirm('ARE YOU SURE YOU WANT TO DELETE THIS BOOK AND ALL ITS REVIEWS?');">
      <div class="form-group">
        <label class="form-label" for="title">BOOK TITLE <span style="color:var(--accent)">*</span></label>
        <input class="form-input" list="book-list" id="title" name="title"
               placeholder="start typing a title..." required autocomplete="off">
        <datalist id="book-list">
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <option value="<?= htmlspecialchars($row['title']) ?>">
              <?= htmlspecialchars($row['author']) ?>
            </option>
          <?php endwhile; ?>
        </datalist>
      </div>

      <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <button type="submit" class="btn btn-danger">DELETE BOOK</button>
        <a href="displayBooks.php" class="btn btn-secondary">CANCEL</a>
      </div>
    </form>
    <?php endif; ?>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
