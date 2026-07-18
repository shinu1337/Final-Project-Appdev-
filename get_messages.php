<?php
// filename: get_messages.php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_GET['request_id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = $_GET['request_id'];

try {
    // Verify access
    $stmt = $pdo->prepare("SELECT id FROM requests WHERE id = ? AND (renter_id = ? OR owner_id = ?)");
    $stmt->execute([$request_id, $user_id, $user_id]);
    if (!$stmt->fetch()) {
        exit;
    }

    // Get message logs
    $msgStmt = $pdo->prepare("SELECT * FROM messages WHERE request_id = ? ORDER BY id ASC");
    $msgStmt->execute([$request_id]);
    $messages = $msgStmt->fetchAll();

    foreach ($messages as $msg) {
        $isMe = ($msg['sender_id'] == $user_id);
        ?>
        <div style="display: flex; flex-direction: column; align-items: <?= $isMe ? 'flex-end' : 'flex-start' ?>;">
            <div style="max-width: 70%; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.95rem; line-height: 1.4; word-break: break-word;
                        background: <?= $isMe ? '#f5c542' : '#2b2b2b' ?>; 
                        color: <?= $isMe ? '#000' : '#fff' ?>;
                        border-bottom-<?= $isMe ? 'right' : 'left' ?>-radius: 2px;">
                <?= htmlspecialchars($msg['message_text'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <span class="muted" style="font-size: 0.7rem; margin-top: 4px; padding: 0 4px;">
                <?= date('h:i A', strtotime($msg['created_at'])) ?>
            </span>
        </div>
        <?php
    }
} catch (PDOException $e) {
    exit;
}