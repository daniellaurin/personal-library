<?php
/*
 * file: login.php
 * description: login form and handler.
 * verifies credentials against the Users table and creates a session on success.
 */

$base        = '';
$pageTitle   = 'LOGIN';
$pageDesc    = 'Sign in to your Personal Library account.';
$currentPage = 'login';
require_once 'includes/auth.php';
require_once 'includes/db.php';

/* redirect if already logged in */
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error  = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = mysqli_prepare($db, 'SELECT id, username, email, password_hash, role, is_active FROM Users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($db);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'invalid email or password.';
        } elseif (!$user['is_active']) {
            $error = 'this account has been disabled. please contact an administrator.';
        } else {
            /* successful login — store session data */
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'];

            $redirect = isset($_GET['next']) ? $_GET['next'] : 'index.php';
            header('Location: ' . $redirect);
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">WELCOME BACK</h1>
    <p class="page-subtitle">SIGN IN TO YOUR LIBRARY</p>
  </div>

  <div class="form-card">
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="form-group">
        <label class="form-label" for="email">EMAIL</label>
        <input class="form-input" type="email" id="email" name="email"
               value="<?= htmlspecialchars($email) ?>"
               placeholder="you@example.com" required autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">PASSWORD</label>
        <input class="form-input" type="password" id="password" name="password"
               placeholder="your password" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem;">
        SIGN IN
      </button>
    </form>

    <p class="form-footer">NEW HERE? <a href="register.php">CREATE AN ACCOUNT</a></p>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
