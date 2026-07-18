<?php
// filename: listings.php
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Track if we are editing an item
$edit_mode = false;
$edit_item = [
    'id' => '',
    'title' => '',
    'price' => '',
    'description' => '',
    'category_id' => '',
    'image' => ''
];

// 1. HANDLE EDIT BUTTON CLICK (Populate form fields)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $item_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
    $stmt->execute([$item_id, $user_id]);
    $found = $stmt->fetch();
    if ($found) {
        $edit_mode = true;
        $edit_item = $found;
    }
}

// 2. HANDLE DELETE SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_listing'])) {
    $item_id = $_POST['item_id'] ?? '';
    if (!empty($item_id)) {
        try {
            $stmt = $pdo->prepare("SELECT image FROM items WHERE id = ? AND user_id = ?");
            $stmt->execute([$item_id, $user_id]);
            $item = $stmt->fetch();

            if ($item) {
                if (!empty($item['image']) && $item['image'] !== 'placeholder.jpg') {
                    $file_path = 'images/' . $item['image'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                $delete_stmt = $pdo->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
                $delete_stmt->execute([$item_id, $user_id]);
                $success = 'Item deleted successfully!';
            } else {
                $error = 'Item not found or permission denied.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// 3. HANDLE ADD OR UPDATE SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_listing']) || isset($_POST['update_listing']))) {
    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $item_id = $_POST['item_id'] ?? ''; // Only set during updates

    if (empty($title) || empty($price) || empty($category_id)) {
        $error = 'Title, price, and category are required fields.';
    } else {
        try {
            if (isset($_POST['update_listing'])) {
                // --- UPDATE LOGIC ---
                $stmt = $pdo->prepare("SELECT image FROM items WHERE id = ? AND user_id = ?");
                $stmt->execute([$item_id, $user_id]);
                $current_item = $stmt->fetch();
                
                $image_filename = $current_item['image'] ?? 'placeholder.jpg';

                if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['item_image']['tmp_name'];
                    $file_name = $_FILES['item_image']['name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
                    
                    if (move_uploaded_file($file_tmp, 'images/' . $new_file_name)) {
                        if ($image_filename !== 'placeholder.jpg' && file_exists('images/' . $image_filename)) {
                            unlink('images/' . $image_filename);
                        }
                        $image_filename = $new_file_name;
                    }
                }

                $stmt = $pdo->prepare("UPDATE items SET title = ?, price = ?, description = ?, image = ?, category_id = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$title, $price, $description, $image_filename, $category_id, $item_id, $user_id]);
                $success = 'Item updated successfully!';
                
            } else {
                // --- ADD LOGIC ---
                $image_filename = 'placeholder.jpg'; 
                if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['item_image']['tmp_name'];
                    $file_name = $_FILES['item_image']['name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
                    
                    if (!is_dir('images/')) { mkdir('images/', 0777, true); }
                    if (move_uploaded_file($file_tmp, 'images/' . $new_file_name)) {
                        $image_filename = $new_file_name;
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO items (user_id, title, price, description, image, category_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $title, $price, $description, $image_filename, $category_id]);
                $success = 'Item listed successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch categories
try {
    $cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) { $categories = []; }

// Fetch user's listings
$stmt = $pdo->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$my_items = $stmt->fetchAll();

include 'header.php';
?>

<div class="page">
    <div class="page-head">
        <h1>My Listings</h1>
        <p class="muted">Manage things you are lending to the community</p>
    </div>

    <?php if($error): ?>
        <div class="badge badge-danger" style="display:block; margin-bottom:1.5rem; padding: 0.75rem 1rem;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="badge badge-success" style="display:block; margin-bottom:1.5rem; padding: 0.75rem 1rem;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="dash-grid">
        
        <!-- Contextual Form -->
        <div class="card card-pad">
            <h3><?= $edit_mode ? 'Update Item Details' : 'Post a New Item' ?></h3>
            <div class="divider"></div>
            
            <form method="POST" action="listings.php" enctype="multipart/form-data">
                <input type="hidden" name="item_id" value="<?= htmlspecialchars($edit_item['id']) ?>">

                <div class="form-group">
                    <label class="form-label" for="title">Item Title</label>
                    <input type="text" id="title" name="title" class="input" placeholder="e.g., Mountain Bike" value="<?= htmlspecialchars($edit_item['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="input" required style="width: 100%; height: 42px; background: var(--bg-surface, #1e1e1e); color: #fff; border: 1px solid var(--border-color, #333); border-radius: 6px; padding: 0 10px;">
                        <option value="" disabled <?= !$edit_mode ? 'selected' : '' ?>>Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $edit_item['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="price">Rental Price (₱ / day)</label>
                    <input type="number" step="0.01" id="price" name="price" class="input" placeholder="0.00" value="<?= htmlspecialchars($edit_item['price']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="textarea" placeholder="Describe condition, pickup rules..."><?= htmlspecialchars($edit_item['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="item_image">Item Image <?= $edit_mode ? '<small class="muted">(Leave empty to keep current)</small>' : '' ?></label>
                    <input type="file" id="item_image" name="item_image" class="input" accept="image/*" <?= !$edit_mode ? 'required' : '' ?>>
                </div>

                <?php if ($edit_mode): ?>
                    <button type="submit" name="update_listing" class="btn btn-primary btn-block">Save Changes</button>
                    <a href="listings.php" class="btn btn-outline btn-block" style="display:block; text-align:center; margin-top:0.5rem; text-decoration:none; line-height:38px; padding:0;">Cancel Edit</a>
                <?php else: ?>
                    <button type="submit" name="add_listing" class="btn btn-primary btn-block">Upload & Post Listing</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- FIXED TABLE GRID PANEL -->
        <div class="card listings-table" style="padding: 1.5rem; box-sizing: border-box;">
            <!-- Grid Header Setup -->
            <div class="listings-header" style="display: grid; grid-template-columns: 2fr 1.2fr 1fr 0.8fr; gap: 1rem; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid #2a2a35; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #aaa;">
                <div>Item Detail</div>
                <div>Price</div>
                <div>Status</div>
                <div style="text-align: right;">Actions</div>
            </div>

            <?php if (empty($my_items)): ?>
                <div class="empty" style="padding: 3rem 0; text-align: center; color: #aaa;"><p>You haven't listed any items yet.</p></div>
            <?php else: ?>
                <?php foreach ($my_items as $item): ?>
                    <!-- Grid Row Setup -->
                    <div class="listing-row" style="display: grid; grid-template-columns: 2fr 1.2fr 1fr 0.8fr; gap: 1rem; align-items: center; padding: 1.25rem 0; border-bottom: 1px solid #222;">
                        
                        <!-- Col 1: Detail with clean flex spacing -->
                        <div class="listing-item" style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            <?php 
                                $image_src = (!empty($item['image']) && file_exists('images/' . $item['image'])) ? 'images/' . $item['image'] : 'images/placeholder.jpg'; 
                            ?>
                            <img src="<?= htmlspecialchars($image_src) ?>" alt="Thumbnail" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; flex-shrink: 0; background: #222;">
                            <div style="min-width: 0; display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-size: 0.75rem; color: #aaa; text-transform: uppercase; letter-spacing: 0.5px;"></span>
                                <b style="color: #fff; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;" title="<?= htmlspecialchars($item['title']) ?>">
                                    <?= htmlspecialchars($item['title']) ?>
                                </b>
                            </div>
                        </div>
                        
                        <!-- Col 2: Price Field formatting -->
                        <div style="color: #f5c542; font-weight: 600; font-size: 0.95rem; white-space: nowrap;">
                            ₱<?= number_format($item['price'], 2) ?><span style="color: #aaa; font-size: 0.8rem; font-weight: 400;">/day</span>
                        </div>
                        
                        <!-- Col 3: Status Element -->
                        <div>
                            <span class="badge badge-success" style="background: rgba(46, 204, 113, 0.15); color: #2ecc71; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 500;">Available</span>
                        </div>
                        
                        <!-- Col 4: Action Layout aligned right -->
                        <div class="listing-actions" style="display: flex; gap: 16px; align-items: center; justify-content: flex-end;">
                            <!-- EDIT BUTTON -->
                            <a href="listings.php?edit=<?= $item['id'] ?>" class="icon-btn" title="Edit Listing" style="color: #aaa; background: none; border: none; display: flex; align-items: center; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#aaa'">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>

                            <!-- DELETE FORM -->
                            <form method="POST" action="listings.php" onsubmit="return confirm('Are you sure you want to delete this listing?');" style="display:inline; margin:0;">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="delete_listing" class="icon-btn danger" title="Delete Listing" style="background: none; border: none; cursor: pointer; padding:0; display: flex; align-items: center; color: #e74c3c; transition: color 0.2s;" onmouseover="this.style.color='#c0392b'" onmouseout="this.style.color='#e74c3c'">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>