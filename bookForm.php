<?php
/*
 * file: bookForm.php
 * description: manual book entry form for adding a book without the google books api.
 * satisfies rubric requirement 4 (dynamic html form #2 alongside addReview.php).
 */

$base        = '';
$pageTitle   = 'ADD A BOOK';
$pageDesc    = 'Manually add a book to the Personal Library.';
$currentPage = 'library';
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">ADD A BOOK</h1>
    <p class="page-subtitle">MANUAL ENTRY</p>
  </div>

  <div class="form-card" style="max-width:580px;">
    <p class="text-muted" style="margin-bottom:1.25rem;font-size:.875rem;">
      PREFER TO USE THE <a href="bookSearch.php">SEARCH PAGE</a> TO ADD BOOKS WITH COVER IMAGES AND AUTO-FILLED DETAILS.
    </p>

    <form action="bookInsert.php" method="POST">
      <div class="form-group">
        <label class="form-label" for="title">BOOK TITLE <span style="color:var(--accent)">*</span></label>
        <input class="form-input" type="text" id="title" name="title" placeholder="e.g. The Great Gatsby" required autocomplete="off">
      </div>

      <div class="form-group">
        <label class="form-label" for="author">AUTHOR <span style="color:var(--accent)">*</span></label>
        <input class="form-input" type="text" id="author" name="author" placeholder="e.g. F. Scott Fitzgerald" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="publisher">PUBLISHER</label>
        <input class="form-input" type="text" id="publisher" name="publisher" placeholder="e.g. Scribner">
      </div>

      <div class="form-group">
        <label class="form-label" for="yearPublished">YEAR PUBLISHED</label>
        <input class="form-input" type="number" id="yearPublished" name="yearPublished"
               placeholder="e.g. 1925" min="1000" max="<?= date('Y') ?>" style="max-width:180px;">
      </div>

      <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">ADD TO LIBRARY</button>
        <a href="displayBooks.php" class="btn btn-secondary">CANCEL</a>
      </div>
    </form>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
