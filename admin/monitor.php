<?php
/*
 * file: admin/monitor.php
 * description: site health monitoring page. checks database connectivity, table status,
 * php environment, session status, and the google books api endpoint.
 * satisfies the rubric's "monitoring page" backend requirement.
 */

$base        = '../';
$pageTitle   = 'SITE MONITOR';
$pageDesc    = 'Personal Library system health and status monitoring.';
$currentPage = 'admin';
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin('../');

/* run all health checks  */
$checks = [];

/* check 1: database connection */
$db = @getDB();
if ($db) {
    $checks[] = ['label' => 'DATABASE CONNECTION', 'ok' => true,  'detail' => 'connected to ' . DB_HOST];
} else {
    $checks[] = ['label' => 'DATABASE CONNECTION', 'ok' => false, 'detail' => 'connection failed'];
    $db = null;
}

/* check 2: required tables */
if ($db) {
    foreach (['Books', 'Users', 'Reviews'] as $table) {
        $res = mysqli_query($db, "SHOW TABLES LIKE '$table'");
        $exists = $res && mysqli_num_rows($res) > 0;
        $checks[] = ['label' => "TABLE: $table", 'ok' => $exists, 'detail' => $exists ? 'found' : 'missing — run setup'];
    }
}

/* check 3: record counts */
if ($db) {
    foreach (['Books', 'Users', 'Reviews'] as $table) {
        $res = mysqli_query($db, "SELECT COUNT(*) AS c FROM $table");
        if ($res) {
            $cnt = mysqli_fetch_assoc($res)['c'];
            $checks[] = ['label' => "RECORDS IN $table", 'ok' => true, 'detail' => "$cnt record(s)"];
        }
    }
}

/* check 4: php version */
$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks[] = ['label' => 'PHP VERSION', 'ok' => $phpOk, 'detail' => PHP_VERSION . ($phpOk ? '' : ' (8.0+ required)')];

/* check 5: session support */
$sessionOk = function_exists('session_start');
$checks[] = ['label' => 'SESSION SUPPORT', 'ok' => $sessionOk, 'detail' => $sessionOk ? 'available' : 'not available'];

/* check 6: images directory readable */
$imgDir = dirname(__DIR__) . '/images';
$imgOk  = is_readable($imgDir);
$checks[] = ['label' => 'IMAGES DIRECTORY', 'ok' => $imgOk, 'detail' => $imgOk ? 'readable' : 'not readable'];

/* check 7: google books api reachability */
$apiUrl  = 'https://www.googleapis.com/books/v1/volumes?q=test&maxResults=1';
$apiResp = @file_get_contents($apiUrl, false, stream_context_create(['http' => ['timeout' => 5]]));
$apiOk   = $apiResp !== false;
$checks[] = ['label' => 'GOOGLE BOOKS API', 'ok' => $apiOk, 'detail' => $apiOk ? 'reachable' : 'unreachable (check server firewall or allow_url_fopen)'];

/* check 8: mysqli extension */
$mysqliOk = extension_loaded('mysqli');
$checks[] = ['label' => 'MYSQLI EXTENSION', 'ok' => $mysqliOk, 'detail' => $mysqliOk ? 'loaded' : 'not loaded'];

if ($db) mysqli_close($db);

$passCount = count(array_filter($checks, fn($c) => $c['ok']));
$totalChecks = count($checks);

require_once '../includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">SITE MONITOR</h1>
    <p class="page-subtitle">
      <?= $passCount === $totalChecks ? 'ALL SYSTEMS OPERATIONAL' : ($passCount . ' / ' . $totalChecks . ' CHECKS PASSING') ?>
    </p>
  </div>

  <div class="panel" style="max-width:780px;margin:0 auto;">
    <p class="text-muted" style="font-size:.8rem;margin-bottom:1.25rem;">
      CHECKED AT <?= date('F j, Y \a\t g:i:s A') ?> &nbsp;|&nbsp; SERVER: <?= htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'localhost') ?>
    </p>

    <table class="data-table">
      <thead>
        <tr><th>CHECK</th><th>STATUS</th><th>DETAIL</th></tr>
      </thead>
      <tbody>
        <?php foreach ($checks as $c): ?>
        <tr>
          <td style="font-weight:500;"><?= htmlspecialchars($c['label']) ?></td>
          <td>
            <?php if ($c['ok']): ?>
              <span style="color:#2ecc71;font-weight:600;">✅ PASS</span>
            <?php else: ?>
              <span style="color:#e74c3c;font-weight:600;">❌ FAIL</span>
            <?php endif; ?>
          </td>
          <td class="text-muted"><?= htmlspecialchars($c['detail']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="panel" style="max-width:780px;margin:1.5rem auto 0;">
    <h2 class="section-title">SERVER ENVIRONMENT</h2>
    <table class="data-table">
      <tbody>
        <tr><td>PHP VERSION</td><td><?= PHP_VERSION ?></td></tr>
        <tr><td>SERVER SOFTWARE</td><td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') ?></td></tr>
        <tr><td>DOCUMENT ROOT</td><td><?= htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') ?></td></tr>
        <tr><td>MAX EXECUTION TIME</td><td><?= ini_get('max_execution_time') ?>s</td></tr>
        <tr><td>MEMORY LIMIT</td><td><?= ini_get('memory_limit') ?></td></tr>
        <tr><td>UPLOAD MAX FILESIZE</td><td><?= ini_get('upload_max_filesize') ?></td></tr>
      </tbody>
    </table>
  </div>

  <div class="action-links">
    <a href="index.php">← DASHBOARD</a>
    <a href="../createBooksTable.php">RUN DB SETUP</a>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>
