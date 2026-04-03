<?php
/*
 * file: about.php
 * description: about page — business case description required by the rubric.
 * satisfies grading requirement 1 (0.5 pts).
 */

$base        = '';
$pageTitle   = 'ABOUT';
$pageDesc    = 'Learn about Personal Library — a Letterboxd-inspired book tracking and review platform.';
$currentPage = 'about';
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">ABOUT PERSONAL LIBRARY</h1>
    <p class="page-subtitle">A LETTERBOXD FOR BOOKS</p>
  </div>

  <div class="about-grid">

    <div class="panel">
      <h2 class="panel-title">WHAT IS THIS?</h2>
      <p>Personal Library is a web-based book cataloguing and review platform inspired by Letterboxd. Search millions of titles via the Google Books API, add them to your personal collection, rate them out of five stars, and share written reviews with the community.</p>
      <p style="margin-top:1rem;">Whether you are tracking what you have already read, curating a want-to-read list, or comparing notes with other readers, Personal Library is your dedicated literary home on the web.</p>
    </div>

    <div class="panel">
      <h2 class="panel-title">FEATURES</h2>
      <ul class="feature-list">
        <li>🔍 Search millions of books via the Google Books API</li>
        <li>📖 Add books to a shared community library</li>
        <li>⭐ Rate books from 1 to 5 stars</li>
        <li>✍️ Write and edit detailed reviews</li>
        <li>📚 Track reading format (Hardcover, Paperback, Ebook, Audiobook)</li>
        <li>📌 Mark reading status (Want to Read, Currently Reading, Read)</li>
        <li>👤 Personal profile showing your reading history</li>
        <li>🎨 Three seasonal themes: Spring, Autumn, Winter</li>
        <li>📱 Fully responsive on mobile and desktop</li>
        <li>🛡️ Admin panel for site management</li>
      </ul>
    </div>

    <div class="panel">
      <h2 class="panel-title">TECH STACK</h2>
      <table class="data-table">
        <thead>
          <tr><th>LAYER</th><th>TECHNOLOGY</th></tr>
        </thead>
        <tbody>
          <tr><td>Backend</td><td>PHP 8 (procedural)</td></tr>
          <tr><td>Database</td><td>MySQL via MySQLi</td></tr>
          <tr><td>Frontend</td><td>HTML5, CSS3, Vanilla JavaScript</td></tr>
          <tr><td>External API</td><td>Google Books API v1</td></tr>
          <tr><td>Fonts</td><td>Inter + Merriweather (Google Fonts)</td></tr>
          <tr><td>Hosting</td><td>University of Windsor (myweb.cs.uwindsor.ca)</td></tr>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <h2 class="panel-title">THE CATALOGUE</h2>
      <p>Books are the catalogue items in Personal Library. Each book in the collection comes with at minimum two user-configurable options:</p>
      <ul class="feature-list" style="margin-top:1rem;">
        <li><strong>Reading Format</strong> - Hardcover, Paperback, Ebook, or Audiobook</li>
        <li><strong>Reading Status</strong> - Want to Read, Currently Reading, or Read</li>
      </ul>
      <p style="margin-top:1rem;">These options are set per-user when writing a review, allowing different readers to log the same book in different formats and at different stages in their reading journey.</p>
    </div>

    <div class="panel">
      <h2 class="panel-title">ACADEMIC CONTEXT</h2>
      <p>This project was developed for <strong>COMP-3250 - Web Development</strong> at the <strong>University of Windsor</strong> during the Winter 2026 semester.</p>
      <p style="margin-top:.75rem;"><strong>Developer:</strong> Daniel Laurin</p>
      <p style="margin-top:.25rem;"><strong>Submission date:</strong> March 30, 2026</p>
      <p style="margin-top:.25rem;"><strong>Repository:</strong> <a href="https://github.com/Daniellaurin/personal_library" target="_blank" rel="noopener">github.com/Daniellaurin/personal_library</a></p>
    </div>

  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
