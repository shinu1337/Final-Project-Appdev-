<?php
// filename: login.php
require_once 'db.php';
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            
            header("Location: browse.php");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill out all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShareNest</title>
    <!-- LINK YOUR EXACT CSS FILES HERE -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-hero">
            <div class="auth-hero-copy">
                <h1>Share<span>Nest</span></h1>
                <p>Log in to manage your community rentals and connect with your neighborhood.</p>
            </div>
        </div>

        <div class="auth-form-wrap">
            <div class="auth-card">
                <h2>Welcome Back</h2>
                <p class="muted">Sign in to access your dashboard</p>

                <?php if($error): ?> 
                    <div class="badge badge-danger" style="display:block; margin-bottom:1rem; padding: 0.5rem 1rem;">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div> 
                <?php endif; ?>

                <div class="auth-toggle">
                    <a href="login.php" class="active">Log In</a>
                    <a href="register.php">Sign Up</a>
                </div>

                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="input" placeholder="you@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="password" class="input" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Log In</button>
                </form>

                <div class="divider-or">OR</div>

                <div class="auth-foot">
                    <p>Don't have an account yet? <a href="register.php">Sign up here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>