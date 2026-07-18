<?php
// filename: chat.php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = $_GET['request_id'] ?? 0;
$error = '';

// Verify that the logged-in user belongs to this specific transaction
try {
    $stmt = $pdo->prepare("
        SELECT r.*, i.title 
        FROM requests r 
        JOIN items i ON r.item_id = i.id 
        WHERE r.id = ? AND (r.renter_id = ? OR r.owner_id = ?)
    ");
    $stmt->execute([$request_id, $user_id, $user_id]);
    $request = $stmt->fetch();

    if (!$request) {
        die("Conversation not found or access denied.");
    }
} catch (PDOException $e) {
    die("Database access error: " . $e->getMessage());
}

// Handle sending a new message via standard POST fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message_text = trim($_POST['message_text'] ?? '');
    if (!empty($message_text)) {
        $receiver_id = ($user_id == $request['owner_id']) ? $request['renter_id'] : $request['owner_id'];
        
        try {
            $sendStmt = $pdo->prepare("INSERT INTO messages (request_id, sender_id, receiver_id, message_text) VALUES (?, ?, ?, ?)");
            $sendStmt->execute([$request_id, $user_id, $receiver_id, $message_text]);
            header("Location: chat.php?request_id=" . $request_id);
            exit;
        } catch (PDOException $e) {
            $error = "Failed to send message.";
        }
    }
}

// Get initial message logs for page render
$msgStmt = $pdo->prepare("SELECT * FROM messages WHERE request_id = ? ORDER BY id ASC");
$msgStmt->execute([$request_id]);
$messages = $msgStmt->fetchAll();

include 'header.php';
?>

<div class="container" style="margin-top: 2rem; max-width: 800px; margin-left: auto; margin-right: auto; padding: 0 1rem;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid #333; padding-bottom: 1rem;">
        <div>
            <h2 style="margin:0; color:#fff; font-size:1.4rem;"><?= htmlspecialchars($request['title']) ?> Chat Workspace</h2>
            <p class="muted" style="margin: 4px 0 0 0; font-size:0.85rem;">Coordinate your pickup timelines and location setup instructions</p>
        </div>
        <a href="requests.php" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; text-decoration: none;">Back</a>
    </div>

    <!-- Chat Message Container Window -->
    <div class="card" style="background:#1e1e1e; border:1px solid #333; height: 450px; display: flex; flex-direction: column; justify-content: space-between; padding: 0; overflow:hidden;">
        
        <!-- Messages Display Panel Area -->
        <div style="flex: 1; padding: 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1rem;" id="chat-box">
            <?php foreach ($messages as $msg): 
                $isMe = ($msg['sender_id'] == $user_id);
            ?>
                <div style="display: flex; flex-direction: column; align-items: <?= $isMe ? 'flex-end' : 'flex-start' ?>;">
                    <div style="max-width: 70%; padding: 0.75rem 1rem; border-radius: 12px; font-size: 0.95rem; line-height:1.4; word-break: break-word;
                                background: <?= $isMe ? '#f5c542' : '#2b2b2b' ?>; 
                                color: <?= $isMe ? '#000' : '#fff' ?>;
                                border-bottom-<?= $isMe ? 'right' : 'left' ?>-radius: 2px;">
                        <?= htmlspecialchars($msg['message_text']) ?>
                    </div>
                    <span class="muted" style="font-size: 0.7rem; margin-top: 4px; padding: 0 4px;">
                        <?= date('h:i A', strtotime($msg['created_at'])) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Input Form Bar -->
        <form id="chat-form" method="POST" action="" style="display: flex; padding: 1rem; background: rgba(0,0,0,0.2); border-top: 1px solid #333; gap: 10px; margin: 0;">
            <input type="text" id="message_text" name="message_text" placeholder="Type your arrangement messages here..." autocomplete="off" required
                   style="flex: 1; padding: 0.75rem 1rem; background: #252525; border: 1px solid #3c3c3c; border-radius: 6px; color: #fff; font-size: 0.95rem; outline: none;">
            <button type="submit" name="send_message" class="btn btn-primary" style="font-weight: 600; padding: 0 1.5rem;">Send</button>
        </form>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    const requestId = <?= json_encode($request_id) ?>;

    // Helper to scroll to bottom
    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Scroll to bottom on initial load
    scrollToBottom();

    // Fetch new messages asynchronously without full reload
    function fetchMessages() {
        // Track if user is scrolled to the bottom before update
        const isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 50;

        fetch(`get_messages.php?request_id=${requestId}`)
            .then(response => response.text())
            .then(html => {
                chatBox.innerHTML = html;
                // Only snap window scroll down if they were already watching the bottom
                if (isScrolledToBottom) {
                    scrollToBottom();
                }
            })
            .catch(err => console.error('Error fetching chat messages:', err));
    }

    // Poll the backend endpoint every 2000ms (2 seconds)
    setInterval(fetchMessages, 2000);
</script>

<?php include 'footer.php'; ?>