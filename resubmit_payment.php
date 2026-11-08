<?php
session_start();
include("include/db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get booking_id from URL
if (!isset($_GET['booking_id'])) {
    $_SESSION['error'] = "Invalid booking ID.";
    header("Location: my_workshops.php");
    exit();
}

$booking_id = intval($_GET['booking_id']);

// Verify this booking belongs to user and payment was rejected
$verify_query = "SELECT b.*, w.title, w.date AS workshop_date, w.fee
                 FROM bookings b
                 JOIN workshops w ON b.workshop_id = w.id
                 WHERE b.id = ? AND b.user_id = ? AND b.payment_status = 'rejected'";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Booking not found or payment was not rejected.";
    header("Location: my_workshops.php");
    exit();
}

$booking = $result->fetch_assoc();

// Handle resubmission
if (isset($_POST['resubmit_payment'])) {
    $payment_method = $_POST['payment_method'];
    $payment_reference = trim($_POST['payment_reference']);
    $payment_amount = floatval($_POST['payment_amount']);

    // Validate inputs
    if (empty($payment_method) || empty($payment_reference) || $payment_amount <= 0) {
        $_SESSION['error'] = "Please fill in all payment details.";
    } else {
        // Update booking with new payment details
        $update_query = "UPDATE bookings 
                         SET payment_method = ?, 
                             payment_reference = ?, 
                             payment_amount = ?,
                             payment_status = 'pending',
                             booking_status = 'registered',
                             payment_verified_by = NULL,
                             payment_verified_at = NULL
                         WHERE id = ?";
        
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssdi", $payment_method, $payment_reference, $payment_amount, $booking_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Payment resubmitted successfully! Waiting for verification.";
            
            // Optional: Send notification to organizer
            if (file_exists('include/notifications_api.php')) {
                include_once 'include/notifications_api.php';
                $organizer_query = "SELECT organizer_id FROM workshops WHERE id = " . $booking['workshop_id'];
                $org_result = $conn->query($organizer_query);
                if ($org_result && $org_row = $org_result->fetch_assoc()) {
                    addNotification($conn, $org_row['organizer_id'], 
                        "💰 Payment Resubmitted: A participant has resubmitted payment for " . $booking['title']);
                }
            }
            
            header("Location: my_workshops.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to resubmit payment. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resubmit Payment</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include("include/nav.php"); ?>

<main>
    <div class="card" style="max-width: 600px; margin: 40px auto;">
        <h2>💳 Resubmit Payment</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="registration-card" style="background: #fff3cd; border-left: 4px solid #ffc107; margin: 20px 0;">
            <strong style="display: block; margin-bottom: 10px;">⚠️ Previous Payment Rejected</strong>
            <p style="margin: 0; color: #856404;">Your previous payment was not verified. Please resubmit with correct payment details.</p>
        </div>

        <div class="registration-card" style="border-left: 4px solid #3498db;">
            <h3 style="margin-top: 0;"><?= htmlspecialchars($booking['title']); ?></h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <strong>Date</strong>
                    <?= date('F j, Y', strtotime($booking['workshop_date'])); ?>
                </div>
                <div class="detail-item">
                    <strong>Fee</strong>
                    RM <?= number_format($booking['fee'], 2); ?>
                </div>
            </div>
        </div>

        <form method="POST" style="margin-top: 20px;">
            <div class="form-group">
                <label for="payment_method">Payment Method *</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="">-- Select Payment Method --</option>
                    <option value="bank_transfer" <?= $booking['payment_method'] == 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    <option value="qr_code" <?= $booking['payment_method'] == 'online_banking' ? 'selected' : ''; ?>>QR Code</option>
                 </select>
            </div>

            <div class="form-group">
                <label for="payment_reference">Payment Reference / Transaction ID *</label>
                <input type="text" 
                       name="payment_reference" 
                       id="payment_reference" 
                       placeholder="e.g., TXN123456789" 
                       required>
                <small style="display: block; margin-top: 5px; color: #666;">Enter your transaction ID or reference number</small>
            </div>

            <div class="form-group">
                <label for="payment_amount">Payment Amount (RM) *</label>
                <input type="number" 
                       name="payment_amount" 
                       id="payment_amount" 
                       step="0.01" 
                       value="<?= $booking['fee']; ?>" 
                       required>
            </div>

			<div style="display: flex; gap: 10px; margin-top: 25px;">
				<a href="my_workshops.php" class="btn" style="background: #95a5a6; flex: 1; text-align: center;">← Back</a>
				<button type="submit" name="resubmit_payment" class="btn" style="background: #27ae60; flex: 1;">
					✓ Resubmit Payment
				</button>
			</div>
        </form>
    </div>
</main>

<?php include("include/footer.php"); ?>
</body>
</html>