<?php

require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


try {
    $stmt = $pdo->query("SELECT * FROM items ORDER BY id DESC");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
    $error = 'Error loading marketplace catalog: ' . $e->getMessage();
}

include 'header.php';
?>

<div class="container" style="margin-top: 2rem; max-width: 1200px; margin-left: auto; margin-right: auto; padding: 0 1rem;">
    
    
    <?php if (isset($_GET['checkout_success'])): ?>
        <div class="badge badge-success" style="display:block; margin-bottom:1.5rem; padding: 0.75rem 1rem;">
            Rental request submitted successfully! The owner will contact you shortly.
        </div>
    <?php endif; ?>

    <div class="section-head" style="margin-bottom: 2rem;">
        <div>
            <h2>Explore Neighborhood Items</h2>
            <p class="muted">Find active tools, equipment, and goods available near you</p>
        </div>
    </div>

    
    <div class="item-grid">
        <?php if (empty($items)): ?>
            <div class="card card-pad empty" style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem;">
                <p>No items found inside the marketplace catalog right now.</p>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="item-card" style="cursor: pointer; position: relative;" onclick="window.location.href='item_detail.php?id=<?= $item['id'] ?>';">
                    
                    <div class="item-media">
                        <?php 
                            $image_src = (!empty($item['image']) && file_exists('images/' . $item['image'])) 
                                ? 'images/' . $item['image'] 
                                : 'images/placeholder.jpg'; 
                        ?>
                        <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        <span class="tag">Community Rental</span>
                    </div>
                    
                    <div class="item-body">
                        <div class="item-title-row">
                            <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                        </div>
                        
                        <div class="item-loc">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>Metro Manila, PH</span>
                        </div>

                        <div class="item-price-row" onclick="event.stopPropagation();">
                            <span class="item-price">
                                ₱<?= number_format($item['price'], 2) ?> <small>/ day</small>
                            </span>
                            <a href="item_detail.php?id=<?= $item['id'] ?>" class="btn btn-outline" style="padding: 0.35rem 0.8rem; font-size: 0.75rem; text-decoration: none;">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>