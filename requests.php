<?php
// filename: requests.php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = $_GET['msg'] ?? '';

// Handle Status Updates ONLY when the form button is intentionally clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action']; // Must be exactly 'accepted' or 'declined'

    if ($request_id > 0 && in_array($action, ['accepted', 'declined'])) {
        try {
            // Hardened query: Only allows updates if the logged-in user owns the item and the current status is strictly 'pending'
            $updateStmt = $pdo->prepare("
                UPDATE requests r
                JOIN items i ON r.item_id = i.id
                SET r.status = ? 
                WHERE r.id = ? AND i.user_id = ? AND r.status = 'pending'
            ");
            $updateStmt->execute([$action, $request_id, $user_id]);
            
            header("Location: requests.php?msg=status_updated");
            exit;
        } catch (PDOException $e) {
            die("Error updating request status: " . $e->getMessage());
        }
    }
}

try {
    // 1. Fetch Outgoing Requests (Items the user wants to BORROW from others)
    $borrowStmt = $pdo->prepare("
        SELECT r.*, i.title, i.price_per_day, CONCAT(u.first_name, ' ', u.last_name) as owner_name 
        FROM requests r
        JOIN items i ON r.item_id = i.id
        JOIN users u ON i.user_id = u.id
        WHERE r.renter_id = ?
        ORDER BY r.created_at DESC
    ");
    $borrowStmt->execute([$user_id]);
    $outgoing_requests = $borrowStmt->fetchAll();

    // 2. Fetch Incoming Requests (Items others want to BORROW from this user)
    $lendStmt = $pdo->prepare("
        SELECT r.*, i.title, i.price_per_day, CONCAT(u.first_name, ' ', u.last_name) as renter_name, i.user_id as item_owner_id
        FROM requests r
        JOIN items i ON r.item_id = i.id
        JOIN users u ON r.renter_id = u.id
        WHERE i.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $lendStmt->execute([$user_id]);
    $incoming_requests = $lendStmt->fetchAll();

} catch (PDOException $e) {
    die("Database access error: " . $e->getMessage());
}

include 'header.php';
?>

<div style="max-width: 1100px; margin: 2rem auto; padding: 0 1rem; min-height: 75vh;">
    
    <!-- HEADER SECTION -->
    <div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="color: #fff; font-size: 1.6rem; margin: 0;">Rental Inbox</h2>
            <p class="muted" style="margin: 4px 0 0 0; font-size: 0.9rem;">Manage incoming borrowing offers and proceed with secured payments</p>
        </div>
        <a href="transactions.php" class="btn btn-outline" style="padding: 0.6rem 1.2rem; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
            📜 View Transaction Receipts
        </a>
    </div>

    <?php if ($msg === 'status_updated'): ?>
        <div class="badge badge-success" style="display: block; margin-bottom: 1.5rem; padding: 0.75rem 1rem; font-weight: 500;">
            Request status updated successfully!
        </div>
    <?php endif; ?>

    <!-- SECTION 1: BORROWING REQUESTS (OUTGOING) -->
    <div style="margin-bottom: 3rem;">
        <h3 style="color: #fff; font-size: 1.25rem; margin-bottom: 1rem; border-bottom: 1px solid #333; padding-bottom: 0.5rem;">Items You Want to Borrow</h3>
        
        <?php if (empty($outgoing_requests)): ?>
            <div class="card" style="padding: 2rem; text-align: center;">
                <p class="muted" style="margin: 0;">You haven't submitted any rental requests yet.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($outgoing_requests as $row): ?>
                    <div class="card" style="padding: 1.25rem 1.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <h4 style="color: #fff; margin: 0 0 4px 0; font-size: 1.1rem;"><?= htmlspecialchars($row['title']) ?></h4>
                            <p class="muted" style="font-size: 0.85rem; margin: 0;">
                                Owner: <strong style="color: #fff;"><?= htmlspecialchars($row['owner_name']) ?></strong> | Rate: ₱<?= number_format($row['price_per_day'], 2) ?>/day
                            </p>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <?php if ($row['status'] === 'pending'): ?>
                                <span class="badge" style="background: rgba(245, 197, 66, 0.15); color: #f5c542;">Pending Owner Approval</span>
                            <?php elseif ($row['status'] === 'declined'): ?>
                                <span class="badge badge-danger">Declined</span>
                            <?php elseif ($row['status'] === 'accepted'): ?>
                                <span class="badge badge-success">Accepted</span>
                            <?php endif; ?>

                            <?php if ($row['status'] === 'accepted' && $row['payment_status'] === 'unpaid'): ?>
                                <a href="checkout.php?request_id=<?= $row['id'] ?>" class="btn btn-primary" style="font-weight: 600; padding: 0.5rem 1rem; text-decoration: none; font-size: 0.85rem;">
                                    💳 Proceed to Payment
                                </a>
                            <?php elseif ($row['payment_status'] === 'paid'): ?>
                                <span class="badge badge-success" style="background: rgba(46, 204, 113, 0.3); color: #2ecc71; border: 1px solid #2ecc71;">Paid & Verified</span>
                            <?php endif; ?>

                            <a href="chat.php?request_id=<?= $row['id'] ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; text-decoration: none; font-size: 0.85rem;">Open Chat Workspace</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- SECTION 2: LENDING REQUESTS (INCOMING) -->
    <div>
        <h3 style="color: #fff; font-size: 1.25rem; margin-bottom: 1rem; border-bottom: 1px solid #333; padding-bottom: 0.5rem;">Incoming Requests for Your Items</h3>
        
        <?php if (empty($incoming_requests)): ?>
            <div class="card" style="padding: 2rem; text-align: center;">
                <p class="muted" style="margin: 0;">No one has requested to borrow your items yet.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($incoming_requests as $row): ?>
                    <div class="card" style="padding: 1.25rem 1.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <h4 style="color: #fff; margin: 0 0 4px 0; font-size: 1.1rem;"><?= htmlspecialchars($row['title']) ?></h4>
                            <p class="muted" style="font-size: 0.85rem; margin: 0;">
                                Renter: <strong style="color: #fff;"><?= htmlspecialchars($row['renter_name']) ?></strong> | Rate: ₱<?= number_format($row['price_per_day'], 2) ?>/day
                            </p>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <!-- Strictly renders selection form only if row status string matches 'pending' in database -->
                            <?php if ($row['status'] === 'pending' && $row['item_owner_id'] == $user_id): ?>
                                <form method="POST" action="requests.php" style="display: flex; gap: 8px; margin: 0;">
                                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="action" value="accepted" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;">Accept</button>
                                    <button type="submit" name="action" value="declined" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem; color: #ff7675; border-color: rgba(231,76,60,0.4);">Decline</button>
                                </form>
                            <?php else: ?>
                                <?php if ($row['status'] === 'accepted'): ?>
                                    <span class="badge badge-success">Accepted</span>
                                    <?php if ($row['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success" style="background: rgba(46, 204, 113, 0.3); color: #2ecc71;">User Paid</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(255,255,255,0.05); color: var(--text-muted);">Awaiting Payment</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-danger">Declined</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <a href="chat.php?request_id=<?= $row['id'] ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; text-decoration: none; font-size: 0.85rem;">Open Chat Workspace</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>