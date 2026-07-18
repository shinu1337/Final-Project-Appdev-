<?php
// filename: register.php
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['firstName'] ?? '');
    $last_name = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email is already registered.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
                
                header("Location: login.php?msg=registered");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ShareNest</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>

<div style="max-width: 450px; margin: 4rem auto; padding: 0 1rem; min-height: 70vh;">
    <div class="card" style="padding: 2.5rem 2rem;">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="color: #fff; margin-bottom: 0.5rem; font-size: 1.75rem;">Create Account</h2>
            <p class="muted" style="font-size: 0.9rem; margin: 0;">Get started with a free membership account today</p>
        </div>

        <?php if ($error): ?>
            <div class="badge badge-danger" style="display: block; margin-bottom: 1.5rem; padding: 0.75rem 1rem; font-weight: 500;">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" style="display: flex; flex-direction: column; gap: 1.25rem; margin: 0;">
            
            <!-- Name Row Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label for="firstName" style="font-size: 0.85rem; font-weight: 500;" class="muted">First Name</label>
                    <input type="text" id="firstName" name="firstName" placeholder="John" autocomplete="off" required
                           value="<?= isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName'], ENT_QUOTES, 'UTF-8') : '' ?>"
                           style="padding: 0.75rem 1rem; background: #252525; border: 1px solid #3c3c3c; border-radius: 6px; color: #fff; font-size: 0.95rem; outline: none; width: 100%;">
                </div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label for="lastName" style="font-size: 0.85rem; font-weight: 500;" class="muted">Last Name</label>
                    <input type="text" id="lastName" name="lastName" placeholder="Doe" autocomplete="off" required
                           value="<?= isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName'], ENT_QUOTES, 'UTF-8') : '' ?>"
                           style="padding: 0.75rem 1rem; background: #252525; border: 1px solid #3c3c3c; border-radius: 6px; color: #fff; font-size: 0.95rem; outline: none; width: 100%;">
                </div>
            </div>

            <!-- Email Address field -->
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label for="email" style="font-size: 0.85rem; font-weight: 500;" class="muted">Email Address</label>
                <input type="email" id="email" name="email" placeholder="john@example.com" autocomplete="off" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>"
                       style="padding: 0.75rem 1rem; background: #252525; border: 1px solid #3c3c3c; border-radius: 6px; color: #fff; font-size: 0.95rem; outline: none; width: 100%;">
            </div>

            <!-- Password field -->
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label for="password" style="font-size: 0.85rem; font-weight: 500;" class="muted">Password</label>
                <input type="password" id="password" name="password" placeholder="At least 6 characters" required
                       style="padding: 0.75rem 1rem; background: #252525; border: 1px solid #3c3c3c; border-radius: 6px; color: #fff; font-size: 0.95rem; outline: none; width: 100%;">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="font-weight: 600; padding: 0.75rem; margin-top: 0.5rem; justify-content: center;">
                Sign Up
            </button>
            
        </form>

        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid #333; padding-top: 1.5rem;">
            <p class="muted" style="font-size: 0.9rem; margin: 0;">
                Already have an account? <a href="login.php" style="color: var(--accent-color, #f5c542); text-decoration: none; font-weight: 500;">Log in instead</a>
            </p>
        </div>

    </div>
</div>

</body>
</html>