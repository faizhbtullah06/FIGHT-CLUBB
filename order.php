<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requirePenonton();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: jadwal.php');
    exit;
}

$eventId = (int)($_POST['event_id'] ?? 0);
$categoryId = (int)($_POST['category_id'] ?? 0);
$qty = (int)($_POST['qty'][$categoryId] ?? 0);

$errors = [];

if ($eventId <= 0 || $categoryId <= 0 || $qty <= 0) {
    $errors[] = 'Silakan pilih kategori tiket dan jumlah yang valid.';
}

$cat = null;
if (empty($errors)) {
    $stmt = mysqli_prepare($conn, "SELECT tc.*, e.status AS event_status
                                    FROM ticket_categories tc
                                    JOIN events e ON e.id = tc.event_id
                                    WHERE tc.id = ? AND tc.event_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $categoryId, $eventId);
    mysqli_stmt_execute($stmt);
    $cat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$cat || $cat['event_status'] !== 'aktif') {
        $errors[] = 'Event tidak tersedia.';
    } else {
        $sisa = $cat['quota'] - $cat['sold'];
        if ($qty > $sisa) {
            $errors[] = 'Jumlah tiket melebihi sisa kuota yang tersedia (sisa: ' . $sisa . ').';
        }
    }
}

if (!empty($errors)) {
    $_SESSION['order_errors'] = $errors;
    header('Location: event_detail.php?id=' . $eventId);
    exit;
}

$totalPrice = $cat['price'] * $qty;
$paymentCode = generateUniqueCode('PAY');
$userId = currentUserId();

$insert = mysqli_prepare($conn, "INSERT INTO orders (user_id, event_id, category_id, qty, total_price, payment_code, status)
                                  VALUES (?, ?, ?, ?, ?, ?, 'pending')");
mysqli_stmt_bind_param($insert, "iiiids", $userId, $eventId, $categoryId, $qty, $totalPrice, $paymentCode);
mysqli_stmt_execute($insert);
$orderId = mysqli_insert_id($conn);

header('Location: payment.php?order_id=' . $orderId);
exit;
