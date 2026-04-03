<?php
/*
 * file: updateBookForm.php
 * description: form to update a book's author name. looks up all books from the db
 * to populate the datalist autocomplete for the title field.
 */

$base        = '';
$pageTitle   = 'EDIT BOOK';
$pageDesc    = 'Update a book record in the Personal Library.';
$currentPage = 'library';
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireAdmin();

$db     = getDB();
$result = mysqli_query($db, 'SELECT id, title FROM Books ORDER BY title');
mysqli_close($db);

$message = '';
if (isset($_GET['updated'])) {
    $message = 'BOOK UPDATED SUCCESSFULLY.';
}

require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">EDIT BOOK</h1>
    <p class="page-subtitle">UPDATE AUTHOR NAME</p>
  </div>

  <div class="form-card" style="max-width:580px;">
    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form action="updateBook.php" method="POST">
      <div class="form-group">
        <label class="form-label" for="titleInput">BOOK TITLE (EXISTING) <span style="color:var(--accent)">*</span></label>
        <input class="form-input" list="book-list" id="titleInput" name="titleInput"
               placeholder="start typing a title..." required autocomplete="off">
        <datalist id="book-list">
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <option value="<?= htmlspecialchars($row['title']) ?>"></option>
          <?php endwhile; ?>
        </datalist>
      </div>

      <div class="form-group">
        <label class="form-label" for="authorInput">NEW AUTHOR NAME <span style="color:var(--accent)">*</span></label>
        <input class="form-input" type="text" id="authorInput" name="authorInput"
               placeholder="e.g. F. Scott Fitzgerald" required>
      </div>

      <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">UPDATE BOOK</button>
        <a href="displayBooks.php" class="btn btn-secondary">CANCEL</a>
      </div>
    </form>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
