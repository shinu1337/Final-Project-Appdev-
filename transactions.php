<?php
// filename: transactions.php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT r.*, i.title, CONCAT(u.first_name, ' ', u.last_name) as owner_name 
        FROM requests r
        JOIN items i ON r.item_id = i.id
        JOIN users u ON r.owner_id = u.id
        WHERE r.renter_id = ? AND r.payment_status = 'paid'
        ORDER BY r.paid_at DESC
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error gathering invoices: " . $e->getMessage());
}

include 'header.php';
?>

<div style="max-width: 900px; margin: 2rem auto; padding: 0 1rem; min-height: 75vh;">
    <div style="margin-bottom: 2rem;">
        <h2 style="color: #fff; font-size: 1.6rem; margin: 0;">Transaction Receipts</h2>
        <p class="muted" style="margin: 4px 0 0 0; font-size: 0.9rem;">Review verified proof-of-payment receipts for your borrowed items</p>
    </div>

    <?php if (empty($transactions)): ?>
        <div class="card" style="text-align: center; padding: 3rem 1rem;">
            <p class="muted" style="margin: 0; font-size: 1rem;">No digital transaction receipts found.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($transactions as $tx): ?>
                <div class="card" style="padding: 1.5rem 2rem; border-left: 4px solid var(--accent-color, #f5c542);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <span class="muted" style="font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Receipt Ref: #<?= htmlspecialchars($tx['id']) ?></span>
                            <h3 style="color: #fff; margin: 4px 0 8px 0; font-size: 1.2rem;"><?= htmlspecialchars($tx['title']) ?></h3>
                            <p class="muted" style="font-size: 0.85rem; margin: 0;">
                                Provider / Owner: <strong style="color: #fff;"><?= htmlspecialchars($tx['owner_name']) ?></strong>
                            </p>
                            <p class="muted" style="font-size: 0.85rem; margin: 4px 0 0 0;">
                                Settled Date: <?= date('M d, Y h:i A', strtotime($tx['paid_at'])) ?>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge badge-success" style="margin-bottom: 8px;">Completed</span>
                            <div style="font-size: 1.4rem; font-weight: 700; color: var(--accent-color, #f5c542);">
                                ₱<?= number_format($tx['amount_paid'], 2) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>