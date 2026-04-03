<?php
/*
 * file: bookSearch.php
 * description: google books api search page.
 * search results are fetched client-side via the fetch api.
 * clicking "add to library" submits a hidden form to bookInsert.php.
 * replace the apiKey value below with your own google books api key.
 */

$base        = '';
$pageTitle   = 'SEARCH BOOKS';
$pageDesc    = 'Search millions of books using the Google Books API and add them to your library.';
$currentPage = 'search';
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">SEARCH BOOKS</h1>
    <p class="page-subtitle">POWERED BY GOOGLE BOOKS</p>
  </div>

  <div class="search-bar">
    <input type="search" id="searchInput" placeholder="TITLE, AUTHOR, ISBN..." autocomplete="off" aria-label="book search">
    <button class="btn btn-primary" onclick="searchBooks()">SEARCH</button>
  </div>

  <div id="search-status" aria-live="polite"></div>
  <div id="results" class="book-grid"></div>
</main>

<?php require_once 'includes/footer.php'; ?>

<script>
/*  google books api config  */
/* replace the empty string below with your google books api key */
const API_KEY = 'AIzaSyBX80CBQ-NU10yoDlcJoPepwnJTIvyrD-I';
const API_URL = 'https://www.googleapis.com/books/v1/volumes';

/*  search handler  */
async function searchBooks() {
  const query   = document.getElementById('searchInput').value.trim();
  const status  = document.getElementById('search-status');
  const results = document.getElementById('results');

  if (!query) return;

  status.innerHTML  = '<p class="text-muted text-center" style="padding:1rem;">SEARCHING...</p>';
  results.innerHTML = '';

  try {
    const url  = `${API_URL}?q=${encodeURIComponent(query)}&maxResults=20${API_KEY ? '&key=' + API_KEY : ''}`;
    const resp = await fetch(url);
    const data = await resp.json();

    if (!data.items || data.items.length === 0) {
      status.innerHTML = '<p class="text-muted text-center" style="padding:1rem;">NO RESULTS FOUND.</p>';
      return;
    }

    status.innerHTML = '';

    data.items.forEach(item => {
      const info     = item.volumeInfo;
      const title    = info.title || 'UNTITLED';
      const authors  = info.authors ? info.authors.join(', ') : 'UNKNOWN AUTHOR';
      const publisher= info.publisher || '';
      const year     = info.publishedDate ? info.publishedDate.substring(0, 4) : '';
      const cover    = info.imageLinks ? info.imageLinks.thumbnail.replace('http://', 'https://') : '';
      const gbId     = item.id || '';
      const desc     = info.description ? info.description.substring(0, 300) : '';
      const genre    = info.categories ? info.categories[0] : '';

      /* build result card */
      const card = document.createElement('div');
      card.className = 'book-card search-result';

      card.innerHTML = `
        ${cover
          ? `<img src="${escHtml(cover)}" alt="${escHtml(title)}" class="book-card__cover">`
          : '<div class="book-card__cover-placeholder">📖</div>'}
        <span class="book-card__title">${escHtml(title)}</span>
        <span class="book-card__author">${escHtml(authors)}</span>
        ${year ? `<span class="book-card__year">${escHtml(year)}</span>` : ''}
        <form method="POST" action="bookInsert.php" style="margin-top:auto;padding-top:.75rem;">
          <input type="hidden" name="title"           value="${escHtml(title)}">
          <input type="hidden" name="author"          value="${escHtml(authors)}">
          <input type="hidden" name="publisher"       value="${escHtml(publisher)}">
          <input type="hidden" name="yearPublished"   value="${escHtml(year)}">
          <input type="hidden" name="cover_url"       value="${escHtml(cover)}">
          <input type="hidden" name="google_books_id" value="${escHtml(gbId)}">
          <input type="hidden" name="description"     value="${escHtml(desc)}">
          <input type="hidden" name="genre"           value="${escHtml(genre)}">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:.8rem;padding:.5rem;">
            + ADD TO LIBRARY
          </button>
        </form>`;

      results.appendChild(card);
    });

  } catch (err) {
    status.innerHTML = '<p class="alert alert-error">SEARCH FAILED. CHECK YOUR API KEY OR CONNECTION.</p>';
    console.error('search error:', err);
  }
}

/*  html escape helper  */
function escHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/*  allow enter key to trigger search  */
document.getElementById('searchInput').addEventListener('keydown', e => {
  if (e.key === 'Enter') searchBooks();
});
</script>
