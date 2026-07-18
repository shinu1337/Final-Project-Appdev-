<?php
// filename: item_detail.php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$item_id = $_GET['id'] ?? 0;
$error = '';
$item = null;

if ($item_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            $error = 'The requested listing could not be found.';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
} else {
    $error = 'No valid item selected.';
}

// HANDLE REAL CHECKOUT ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
    $renter_id = $_SESSION['user_id'];
    $owner_id = $item['user_id']; // The user who listed the item

    // Prevent users from renting their own items
    if ($renter_id == $owner_id) {
        $error = "You cannot request to rent your own item.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO requests (item_id, renter_id, owner_id) VALUES (?, ?, ?)");
            $stmt->execute([$item_id, $renter_id, $owner_id]);
            
            header("Location: browse.php?checkout_success=1");
            exit;
        } catch (PDOException $e) {
            $error = 'Failed to submit request: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="container" style="margin-top: 3rem; max-width: 1100px; margin-left: auto; margin-right: auto; padding: 0 1rem; min-height: 75vh;">
    
    <a href="browse.php" class="muted" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 1.5rem; font-size: 0.9rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Back to Marketplace
    </a>

    <?php if ($error || !$item): ?>
        <div class="badge badge-danger" style="display:block; margin-bottom:1.5rem; padding: 0.75rem 1rem;">
            <?= htmlspecialchars($error ?: 'Listing unavailable.') ?>
        </div>
    <?php endif; ?>

    <?php if ($item): ?>
        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 2rem; align-items: start;">
            
            <!-- Left Side Column -->
            <div>
                <div class="card" style="padding: 0; overflow: hidden; border-radius: 8px; background: var(--bg-surface, #1e1e1e); line-height: 0;">
                    <?php 
                        $image_src = (!empty($item['image']) && file_exists('images/' . $item['image'])) 
                            ? 'images/' . $item['image'] 
                            : 'images/placeholder.jpg'; 
                    ?>
                    <img src="<?= htmlspecialchars($image_src) ?>" alt="Item Preview" style="width: 100%; max-height: 500px; object-fit: cover; display: block;">
                </div>
                
                <div style="margin-top: 2rem;">
                    <h2 style="font-size: 1.75rem; margin-bottom: 0.5rem; color: #fff; line-height: 1.3;">
                        <?= htmlspecialchars($item['title']) ?>
                    </h2>
                    
                    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;" class="muted">
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Owner: Community Member
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            Metro Manila, PH
                        </span>
                    </div>
                    
                    <div class="divider" style="margin: 1.5rem 0; height: 1px; background: var(--border-color, #333);"></div>
                    
                    <h4 style="margin-bottom: 0.75rem; color: #fff;">Description</h4>
                    <p class="muted" style="line-height: 1.6; font-size: 0.95rem; white-space: pre-line;">
                        <?= !empty($item['description']) ? htmlspecialchars($item['description']) : 'No description provided for this neighborhood asset.' ?>
                    </p>
                </div>
            </div>

            <!-- Right Side Column -->
            <div class="card card-pad" style="position: sticky; top: 2rem; background: var(--bg-surface, #1e1e1e); border: 1px solid var(--border-color, #333);">
                <span class="badge badge-success" style="margin-bottom: 1rem; display: inline-block;">Available</span>
                
                <div style="margin-bottom: 1.5rem;">
                    <span class="muted" style="font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">Rental Rate</span>
                    <span style="font-size: 2rem; font-weight: 700; color: #f5c542;">₱<?= number_format($item['price'], 2) ?></span>
                    <span class="muted" style="font-size: 0.9rem;">/ day</span>
                </div>

                <div class="divider" style="margin: 1.5rem 0; height: 1px; background: var(--border-color, #333);"></div>
                
                <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem 0; font-size: 0.85rem; display: grid; gap: 0.5rem;" class="muted">
                    <li style="display: flex; justify-content: space-between;">
                        <span>Security Deposit</span>
                        <span style="color: #fff;">₱0.00</span>
                    </li>
                    <li style="display: flex; justify-content: space-between;">
                        <span>Pickup Option</span>
                        <span style="color: #fff;">Meetup / Self-Collect</span>
                    </li>
                </ul>

                <form method="POST" action="">
                    <button type="submit" name="checkout" class="btn btn-primary btn-block" style="text-align: center; justify-content: center; font-weight: 600;">
                        Proceed to Checkout
                    </button>
                </form>
                
                <p class="muted" style="font-size: 0.75rem; text-align: center; margin-top: 0.75rem; line-height: 1.4;">
                    By clicking checkout, you request an agreement matching neighborhood terms.
                </p>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>