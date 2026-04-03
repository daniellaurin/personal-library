<?php
/*
 * file: includes/header.php
 * description: shared html <head> and navigation bar for every page.
 * set $pageTitle, $pageDesc, and $base before including this file.
 * $base: '' for root-level pages, '../' for subdirectory pages (admin/, wiki/).
 */

if (!isset($base))     $base     = '';
if (!isset($pageTitle)) $pageTitle = 'PERSONAL LIBRARY';
if (!isset($pageDesc))  $pageDesc  = 'Your personal book collection, search, and review platform.';
if (!isset($currentPage)) $currentPage = '';

require_once __DIR__ . '/auth.php';
$_user = currentUser();

/* helper to mark active nav links */
function navClass(string $page, string $current): string {
    return $page === $current ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
  <meta name="keywords" content="books, library, reading, reviews, ratings, literature, personal collection, book tracker">
  <meta name="author" content="Daniel Laurin">
  <meta name="robots" content="index, follow">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?> | PERSONAL LIBRARY">
  <meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
  <meta property="og:image" content="<?= $base ?>images/library.jpg">
  <meta property="og:type" content="website">
  <link rel="icon" href="<?= $base ?>images/book.png" type="image/png">
  <title><?= htmlspecialchars($pageTitle) ?> | PERSONAL LIBRARY</title>
  <link rel="stylesheet" href="<?= $base ?>style.css">
  <!-- data-base tells themes.js where the project root is so audio paths resolve correctly -->
  <script src="<?= $base ?>themes.js" data-base="<?= $base ?>" defer></script>
</head>
<body>

<nav class="site-nav" role="navigation" aria-label="main navigation">
  <div class="nav-inner">

    <a href="<?= $base ?>index.php" class="nav-logo">📚 PERSONAL LIBRARY</a>

    <ul class="nav-links" id="nav-links">
      <li><a href="<?= $base ?>index.php"<?= navClass('home', $currentPage) ?>>HOME</a></li>
      <li><a href="<?= $base ?>bookSearch.php"<?= navClass('search', $currentPage) ?>>SEARCH</a></li>
      <li><a href="<?= $base ?>displayBooks.php"<?= navClass('library', $currentPage) ?>>LIBRARY</a></li>
      <li><a href="<?= $base ?>about.php"<?= navClass('about', $currentPage) ?>>ABOUT</a></li>
      <li><a href="<?= $base ?>wiki/index.html"<?= navClass('wiki', $currentPage) ?>>HELP</a></li>
      <li><a href="<?= $base ?>media.php"<?= navClass('media', $currentPage) ?>>AMBIANCE</a></li>
      <?php if (isAdmin()): ?>
      <li><a href="<?= $base ?>admin/index.php"<?= navClass('admin', $currentPage) ?>>ADMIN</a></li>
      <?php endif; ?>
      <?php if (isLoggedIn()): ?>
      <li><a href="<?= $base ?>profile.php"<?= navClass('profile', $currentPage) ?>>PROFILE</a></li>
      <li><a href="<?= $base ?>logout.php">LOGOUT</a></li>
      <?php else: ?>
      <li><a href="<?= $base ?>login.php"<?= navClass('login', $currentPage) ?>>LOGIN</a></li>
      <li><a href="<?= $base ?>register.php"<?= navClass('register', $currentPage) ?>>REGISTER</a></li>
      <?php endif; ?>
    </ul>

    <div class="season-switcher" aria-label="theme switcher"></div>

    <button class="nav-toggle" id="nav-toggle" aria-label="toggle navigation" aria-expanded="false" aria-controls="nav-links">
      <span></span><span></span><span></span>
    </button>

  </div>
</nav>
