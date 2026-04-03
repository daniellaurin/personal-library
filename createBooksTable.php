<?php
/*
 * file: createBooksTable.php
 * description: one-time database setup script.
 * creates Books, Users, and Reviews tables if they do not exist.
 * also adds any missing columns to Books for the updated schema.
 * visit this page once in the browser when deploying to a new environment.
 */
ini_set('display_errors', 1);
  error_reporting(E_ALL);

$base        = '';
$pageTitle   = 'DATABASE SETUP';
$pageDesc    = 'Initialize the Personal Library database tables.';
$currentPage = '';
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/header.php';

$db  = getDB();
$log = [];

/*  BOOK TABLES  */
$ok = mysqli_query($db, "CREATE TABLE IF NOT EXISTS Books (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    author          VARCHAR(200) NOT NULL,
    publisher       VARCHAR(150),
    yearPublished   YEAR,
    cover_url       VARCHAR(500),
    google_books_id VARCHAR(100) UNIQUE,
    description     TEXT,
    genre           VARCHAR(100),
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
$log[] = $ok ? '✅ books table ready.' : '❌ books table: ' . mysqli_error($db);

/* USER TABLES */
$ok = mysqli_query($db, "CREATE TABLE IF NOT EXISTS Users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('user','admin') DEFAULT 'user',
    is_active     TINYINT(1)   DEFAULT 1,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
$log[] = $ok ? '✅ users table ready.' : '❌ users table: ' . mysqli_error($db);

/* REVIEWS TABLES */
$ok = mysqli_query($db, "CREATE TABLE IF NOT EXISTS Reviews (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    book_id     INT UNSIGNED NOT NULL,
    rating      TINYINT UNSIGNED NOT NULL,
    review_text TEXT,
    format      ENUM('Hardcover','Paperback','Ebook','Audiobook') DEFAULT 'Paperback',
    status      ENUM('Want to Read','Currently Reading','Read')   DEFAULT 'Read',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY  uq_user_book (user_id, book_id),
    FOREIGN KEY (user_id) REFERENCES Users(id)  ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES Books(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8");
$log[] = $ok ? '✅ reviews table ready.' : '❌ reviews table: ' . mysqli_error($db);

/*  migrate old schema: add missing columns to Books  */
$existingCols = [];
$res = mysqli_query($db, 'SHOW COLUMNS FROM Books');
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $existingCols[] = $row['Field'];
    }
}

$newCols = [
    'cover_url'       => "ADD COLUMN cover_url VARCHAR(500)",
    'google_books_id' => "ADD COLUMN google_books_id VARCHAR(100)",
    'description'     => "ADD COLUMN description TEXT",
    'genre'           => "ADD COLUMN genre VARCHAR(100)",
    'created_at'      => "ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
];

foreach ($newCols as $col => $def) {
    if (!in_array($col, $existingCols)) {
        $ok = mysqli_query($db, "ALTER TABLE Books $def");
        $log[] = $ok ? "✅ added column '{$col}' to Books." : "❌ add '{$col}': " . mysqli_error($db);
    }
}

/* counts  */
foreach (['Books', 'Users', 'Reviews'] as $table) {
    $res = mysqli_query($db, "SELECT COUNT(*) as cnt FROM $table");
    if ($res) {
        $cnt   = mysqli_fetch_assoc($res)['cnt'];
        $log[] = "📊 $table: $cnt record(s).";
    }
}

mysqli_close($db);
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">DATABASE SETUP</h1>
    <p class="page-subtitle">INITIALIZATION RESULTS</p>
  </div>

  <div class="panel" style="max-width:640px;margin:0 auto;">
    <ul style="list-style:none;display:flex;flex-direction:column;gap:.75rem;font-family:'Inter',sans-serif;font-size:.9rem;line-height:1.6;">
      <?php foreach ($log as $line): ?>
        <li><?= htmlspecialchars($line) ?></li>
      <?php endforeach; ?>
    </ul>

    <div class="action-links" style="margin-top:1.5rem;">
      <a href="index.php">GO TO HOME</a>
      <a href="admin/monitor.php">VIEW MONITOR</a>
    </div>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
