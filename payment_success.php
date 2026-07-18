<?php
// filename: payment_success.php
require_once 'db.php';
require_once 'stripe-php/init.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Replace with your real Stripe Test Secret Key from Stripe Dashboard
\Stripe\Stripe::setApiKey('sk_test_51TuXuT2IrLjxU2S1sZdXGcj5QkSUp1GCeF4g1FVn79ufaWr2pf5SrQyNEFkaJ5yl2tgWe2qtVGbfOG14XULJtf6d00QEuOUJKi');

$session_id = $_GET['session_id'] ?? '';
$request_id = $_GET['request_id'] ?? '';

if (!$session_id || !$request_id) {
    die("Invalid payment signature parameters.");
}

try {
    $stripeSession = \Stripe\Checkout\Session::retrieve($session_id);
    
    if ($stripeSession->payment_status === 'paid') {
        $amount_paid = $stripeSession->amount_total / 100;
        
        $stmt = $pdo->prepare("
            UPDATE requests 
            SET payment_status = 'paid', amount_paid = ?, paid_at = NOW() 
            WHERE id = ? AND stripe_session_id = ? AND payment_status != 'paid'
        ");
        $stmt->execute([$amount_paid, $request_id, $session_id]);
    }

    header("Location: transactions.php");
    exit;
} catch (Exception $e) {
    die("Verification sequence failed: " . $e->getMessage());
}