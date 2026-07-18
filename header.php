<?php
// filename: header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

function sanitize($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
$current_page = basename($_SERVER['PHP_SELF']);


$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $notifStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $notifStmt->execute([$_SESSION['user_id']]);
        $unread_count = $notifStmt->fetchColumn();
    } catch (PDOException $e) {
        $unread_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShareNest</title>
    
    <link rel="stylesheet" href="/css/css/style.css">
    <link rel="stylesheet" href="/css/css/dashboard.css">
    <link rel="stylesheet" href="/css/css/responsive.css">
    
    <!-- Sticky Footer Core Layout Rules -->
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            flex-direction: column;
        }
        .main-content-wrapper {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
        }
    </style>
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="browse.php" class="brand">
      <span class="brand-mark">S</span>
      <span class="brand-name">Share<em>Nest</em></span>
    </a>
    
    <div class="nav-links">
      <a href="browse.php" class="<?= $current_page == 'browse.php' ? 'active' : '' ?>">Browse</a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="listings.php" class="<?= $current_page == 'listings.php' ? 'active' : '' ?>">My Listings</a>
        <a href="requests.php" class="<?= $current_page == 'requests.php' ? 'active' : '' ?>">Wanted Requests</a>
        
        <a href="notifications.php" class="<?= $current_page == 'notifications.php' ? 'active' : '' ?>" style="position: relative; display: inline-flex; align-items: center; gap: 6px;">
          Notifications
          <?php if ($unread_count > 0): ?>
            <span style="background: #ff7675; color: #fff; font-size: 0.75rem; padding: 2px 6px; border-radius: 50%; font-weight: bold; line-height: 1; display: inline-block;">
              <?= $unread_count ?>
            </span>
          <?php endif; ?>
        </a>
      <?php endif; ?>
    </div>

    <div class="nav-actions">
      <?php if(isset($_SESSION['user_id'])): ?>
        <span class="text-sm muted" style="margin-right: 10px;"><?= sanitize($_SESSION['user_name']) ?></span>
        <a href="logout.php" class="btn btn-outline">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Wrapper opens to capture page structure and push down footer -->
<div class="main-content-wrapper">
    <div class="container" style="margin-top: 2rem;">