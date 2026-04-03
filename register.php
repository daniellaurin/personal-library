<?php
/*
 * file: register.php
 * description: user registration form and handler.
 * validates input, hashes the password, then inserts a new user into the Users table.
 */

$base        = '';
$pageTitle   = 'REGISTER';
$pageDesc    = 'Create your Personal Library account.';
$currentPage = 'register';
require_once 'includes/auth.php';
require_once 'includes/db.php';

/* redirect if already logged in */
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$values = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    /* validate username */
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'username must be between 3 and 50 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'username may only contain letters, numbers, and underscores.';
    }

    /* validate email */
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'please enter a valid email address.';
    }

    /* validate password */
    if (strlen($password) < 8) {
        $errors[] = 'password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'passwords do not match.';
    }

    $values = ['username' => $username, 'email' => $email];

    if (empty($errors)) {
        $db   = getDB();
        $hash = password_hash($password, PASSWORD_BCRYPT);

        /* check for duplicate username or email */
        $stmt = mysqli_prepare($db, 'SELECT id FROM Users WHERE username = ? OR email = ?');
        mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'that username or email is already taken.';
        } else {
            mysqli_stmt_close($stmt);

            /* insert new user */
            $stmt = mysqli_prepare($db, 'INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hash);

            if (mysqli_stmt_execute($stmt)) {
                /* auto-login after registration */
                $newId = mysqli_insert_id($db);
                $_SESSION['user_id']  = $newId;
                $_SESSION['username'] = $username;
                $_SESSION['email']    = $email;
                $_SESSION['role']     = 'user';
                mysqli_stmt_close($stmt);
                mysqli_close($db);
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'registration failed. please try again.';
            }
        }
        mysqli_close($db);
    }
}

require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">CREATE AN ACCOUNT</h1>
    <p class="page-subtitle">JOIN THE LIBRARY</p>
  </div>

  <div class="form-card">
    <?php foreach ($errors as $e): ?>
      <div class="alert alert-error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="register.php" novalidate>
      <div class="form-group">
        <label class="form-label" for="username">USERNAME</label>
        <input class="form-input" type="text" id="username" name="username"
               value="<?= htmlspecialchars($values['username']) ?>"
               placeholder="e.g. booklover42" required autocomplete="username">
      </div>

      <div class="form-group">
        <label class="form-label" for="email">EMAIL</label>
        <input class="form-input" type="email" id="email" name="email"
               value="<?= htmlspecialchars($values['email']) ?>"
               placeholder="you@example.com" required autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">PASSWORD</label>
        <input class="form-input" type="password" id="password" name="password"
               placeholder="minimum 8 characters" required autocomplete="new-password">
      </div>

      <div class="form-group">
        <label class="form-label" for="confirm">CONFIRM PASSWORD</label>
        <input class="form-input" type="password" id="confirm" name="confirm"
               placeholder="repeat your password" required autocomplete="new-password">
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem;">
        CREATE ACCOUNT
      </button>
    </form>

    <p class="form-footer">ALREADY HAVE AN ACCOUNT? <a href="login.php">SIGN IN</a></p>
  </div>
</main>

<?php require_once 'includes/footer.php'; ?>
