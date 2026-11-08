<?php
session_start();
include("include/db_connect.php");

// Security check: Only participants
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    die("Access Denied.");
}

$user_id = $_SESSION['user_id'];

// Validate booking ID
if (!isset($_GET['booking_id'])) {
    die("Invalid request.");
}

$booking_id = intval($_GET['booking_id']);

// Update booking to cancelled if not already processed
$sql = "UPDATE bookings 
        SET booking_status = 'cancelled', 
            payment_status = 'cancelled', 
            cancelled_at = NOW()
        WHERE id = ? 
          AND user_id = ? 
          AND booking_status NOT IN ('cancelled','attended','confirmed')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Booking cancelled successfully.";
    } else {
        $_SESSION['error'] = "Unable to cancel booking. It may have already been processed or cancelled.";
    }
} else {
    $_SESSION['error'] = "Database error: " . $stmt->error;
}

// Redirect back
$redirect = $_SERVER['HTTP_REFERER'] ?? 'participant_dashboard.php';
header("Location: $redirect");
exit();
?>