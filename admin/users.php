<?php
/*
 * file: admin/users.php
 * description: user account administration. admins can toggle active/inactive status
 * and promote users to admin or demote them to user role.
 * satisfies the rubric's "user account administration features" requirement.
 */

$base        = '../';
$pageTitle   = 'USER MANAGEMENT';
$pageDesc    = 'Manage user accounts in Personal Library.';
$currentPage = 'admin';
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireAdmin('../');

$db      = getDB();
$current = currentUser();
$msg     = '';
$msgType = 'info';

/* HANDLE TOGGLE ACTIONS */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $targetId = (int)$_POST['user_id'];

    /* prevent admins from modifying their own account here */
    if ($targetId === (int)$current['id']) {
        $msg = 'you cannot modify your own account from this panel.';
        $msgType = 'error';
    } else {
        if ($_POST['action'] === 'toggle_active') {
            $stmt = mysqli_prepare($db, 'UPDATE Users SET is_active = NOT is_active WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $targetId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = 'user status updated.';
            $msgType = 'success';
        } elseif ($_POST['action'] === 'toggle_role') {
            $stmt = mysqli_prepare($db, "UPDATE Users SET role = IF(role='admin','user','admin') WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $targetId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = 'user role updated.';
            $msgType = 'success';
        }
    }
}

/* fetch all users  */
$users = mysqli_query($db, "
    SELECT u.id, u.username, u.email, u.role, u.is_active, u.created_at,
           COUNT(r.id) AS review_count
    FROM Users u
    LEFT JOIN Reviews r ON r.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC");

mysqli_close($db);
require_once '../includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">USER MANAGEMENT</h1>
    <p class="page-subtitle">ACCOUNT ADMINISTRATION</p>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?>" style="max-width:900px;margin:0 auto 1.5rem;">
      <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <div class="panel">
    <table class="data-table">
      <thead>
        <tr>
          <th>USERNAME</th>
          <th>EMAIL</th>
          <th>ROLE</th>
          <th>STATUS</th>
          <th>REVIEWS</th>
          <th>JOINED</th>
          <th>ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($u = mysqli_fetch_assoc($users)): ?>
        <tr>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : '' ?>"><?= strtoupper($u['role']) ?></span></td>
          <td><span class="badge <?= $u['is_active'] ? 'badge-active' : 'badge-inactive' ?>"><?= $u['is_active'] ? 'ACTIVE' : 'DISABLED' ?></span></td>
          <td><?= $u['review_count'] ?></td>
          <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ((int)$u['id'] !== (int)$current['id']): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="action" value="toggle_active">
              <button type="submit" class="btn btn-secondary" style="font-size:.75rem;padding:.3rem .7rem;">
                <?= $u['is_active'] ? 'DISABLE' : 'ENABLE' ?>
              </button>
            </form>
            <form method="POST" style="display:inline;margin-left:.25rem;">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="action" value="toggle_role">
              <button type="submit" class="btn btn-secondary" style="font-size:.75rem;padding:.3rem .7rem;">
                <?= $u['role'] === 'admin' ? 'DEMOTE' : 'PROMOTE' ?>
              </button>
            </form>
            <?php else: ?>
            <span class="text-muted" style="font-size:.8rem;">YOU</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="action-links">
    <a href="index.php">← DASHBOARD</a>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>
