<?php
// filename: notifications.php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// If clicking an individual notification, mark it as read and go straight to chat
if (isset($_GET['read_and_go'])) {
    $notif_id = $_GET['read_and_go'];
    $req_id = $_GET['req_id'];
    
    try {
        $updateStmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $updateStmt->execute([$notif_id, $user_id]);
        header("Location: chat.php?request_id=" . $req_id);
        exit;
    } catch (PDOException $e) {
        header("Location: chat.php?request_id=" . $req_id);
        exit;
    }
}

// Fetch all notifications for this logged-in user
try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    $notifications = [];
}

include 'header.php';
?>

<div class="container" style="margin-top: 3rem; max-width: 800px; margin-left: auto; margin-right: auto; padding: 0 1rem; min-height: 75vh;">
    <div class="page-head" style="margin-bottom: 2rem;">
        <h1>Your Notifications</h1>
        <p class="muted">Track updates for items you have requested to borrow</p>
    </div>

    <div style="display: grid; gap: 1rem;">
        <?php if (empty($notifications)): ?>
            <div class="card" style="padding: 3rem 1rem; text-align: center; background: #1e1e1e; border: 1px solid #333;">
                <p class="muted" style="margin: 0;">No new transaction alerts or rental responses recorded.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <a href="notifications.php?read_and_go=<?= $notif['id'] ?>&req_id=<?= $notif['request_id'] ?>" 
                   style="text-decoration: none; color: inherit; display: block;">
                    <div class="card" style="background: <?= $notif['is_read'] ? '#1e1e1e' : '#252525' ?>; 
                                            border: 1px solid <?= $notif['is_read'] ? '#333' : '#f5c542' ?>; 
                                            padding: 1.25rem; border-radius: 8px; transition: transform 0.2s; cursor: pointer;"
                         onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                            <div>
                                <p style="margin: 0 0 0.5rem 0; color: #fff; line-height: 1.4; font-size: 0.95rem;">
                                    <?= htmlspecialchars($notif['message']) ?>
                                </p>
                                <span class="muted" style="font-size: 0.8rem;">
                                    <?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
                                </span>
                            </div>
                            
                            <?php if (!$notif['is_read']): ?>
                                <span class="badge badge-success" style="font-size: 0.75rem;">New</span>
                            <?php endif; ?>
                        </div>

                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>