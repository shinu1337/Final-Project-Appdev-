<?php
// filename: checkout.php
require_once 'db.php';
require_once 'stripe-php/init.php'; // Uses your local directory path placement

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_GET['request_id'])) {
    header("Location: login.php");
    exit;
}

// Replace with your real Stripe Test Secret Key from Stripe Dashboard (sk_test_...)
\Stripe\Stripe::setApiKey('sk_test_51TuXuT2IrLjxU2S1sZdXGcj5QkSUp1GCeF4g1FVn79ufaWr2pf5SrQyNEFkaJ5yl2tgWe2qtVGbfOG14XULJtf6d00QEuOUJKi');

$request_id = $_GET['request_id'];
$user_id = $_SESSION['user_id'];

try {
    // Selects the active 'price' column from the items table to avoid 0.00 errors
    $stmt = $pdo->prepare("
        SELECT r.*, i.title, i.price 
        FROM requests r 
        JOIN items i ON r.item_id = i.id 
        WHERE r.id = ? AND r.renter_id = ? AND r.status = 'accepted'
    ");
    $stmt->execute([$request_id, $user_id]);
    $rental = $stmt->fetch();

    if (!$rental) {
        die("Invalid transaction or request has not been accepted yet.");
    }

    // Assigns the real marketplace item price
    $total_amount = $rental['price']; 
    $amount_in_cents = $total_amount * 100; // Stripe expects amounts in cents (e.g., 1000 PHP = 100000 cents)

    // Configures the checkout session with standard card elements activated
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'php',
                'product_data' => [
                    'name' => "Rental: " . $rental['title'],
                ],
                'unit_amount' => $amount_in_cents,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/dashboard/sharenest/payment_success.php?session_id={CHECKOUT_SESSION_ID}&request_id=' . $request_id,
        'cancel_url' => 'http://localhost/dashboard/sharenest/requests.php',
    ]);

    // Saves the session identifier to map the callback verification cleanly
    $updateStmt = $pdo->prepare("UPDATE requests SET stripe_session_id = ? WHERE id = ?");
    $updateStmt->execute([$session->id, $request_id]);

    // Redirects the user directly to the live Stripe billing portal
    header("Location: " . $session->url);
    exit;

} catch (Exception $e) {
    die("Stripe initialization error: " . $e->getMessage());
}