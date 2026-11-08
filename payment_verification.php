<?php
session_start();
include("include/db_connect.php");

// Only organizer allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    die("Access Denied");
}

$organizer_id = $_SESSION['user_id'];

// Handle payment verification
if (isset($_POST['verify_payment'])) {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $update_query = "UPDATE bookings 
                         SET payment_status = 'verified', 
                             payment_verified_at = NOW(), 
                             booking_status = 'confirmed',
                             payment_verified_by = ?
                         WHERE id = ?";
        $message = "Payment verified and booking confirmed.";
    } else {
        $update_query = "UPDATE bookings 
                         SET payment_status = 'rejected', 
                             booking_status = 'cancelled',
                             payment_verified_by = ?
                         WHERE id = ?";
        $message = "Payment rejected.";
    }

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $organizer_id, $booking_id);
    $stmt->execute();

    $_SESSION['success'] = $message;
    header("Location: payment_verification.php");
    exit();
}

// Fetch pending payments (exclude cancelled bookings)
$pending_query = "SELECT b.*, w.title, w.date AS workshop_date, u.username, u.email, u.phone
                  FROM bookings b
                  JOIN workshops w ON b.workshop_id = w.id
                  JOIN users u ON b.user_id = u.user_id
                  WHERE b.payment_status = 'pending'
                    AND b.booking_status != 'cancelled'
                    AND w.organizer_id = '$organizer_id'
                  ORDER BY b.created_at DESC";
$pending_result = $conn->query($pending_query);

// Fetch verified/rejected payments (exclude cancelled)
$verified_query = "SELECT b.*, w.title, w.date AS workshop_date, u.username
                   FROM bookings b
                   JOIN workshops w ON b.workshop_id = w.id
                   JOIN users u ON b.user_id = u.user_id
                   WHERE b.payment_status IN ('verified','rejected')
                     AND b.booking_status != 'cancelled'
                     AND w.organizer_id = '$organizer_id'
                   ORDER BY b.payment_verified_at DESC
                   LIMIT 20";
$verified_result = $conn->query($verified_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Verification</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include("include/nav.php"); ?>

<main>
    <div class="card" style="max-width: 1200px; margin: 0 auto;">
        <h2>Payment Verification Dashboard</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-msg"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-msg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab(event,'pending')">
                Pending Verification (<?= $pending_result->num_rows; ?>)
            </button>
            <button class="tab-btn" onclick="showTab(event,'verified')">
                Recent Verifications
            </button>
        </div>

        <!-- Pending Payments Tab -->
        <div id="pending-tab" class="tab-content active">
            <?php if ($pending_result->num_rows > 0): ?>
                <?php while ($reg = $pending_result->fetch_assoc()): ?>
                    <div class="verification-card">
                        <div class="card-header">
                            <div>
                                <h3><?= htmlspecialchars($reg['username']); ?></h3>
                                <p style="color: #666; font-size: 14px;"><?= htmlspecialchars($reg['title']); ?></p>
                            </div>
                            <span class="status-badge status-pending">Pending</span>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-item"><strong>Email</strong><?= htmlspecialchars($reg['email']); ?></div>
                            <div class="detail-item"><strong>Phone</strong><?= htmlspecialchars($reg['phone']); ?></div>
                            <div class="detail-item"><strong>Workshop Date</strong><?= date('F j, Y', strtotime($reg['workshop_date'])); ?></div>
                            <div class="detail-item"><strong>Registration Date</strong><?= date('M j, Y g:i A', strtotime($reg['created_at'])); ?></div>
                            <div class="detail-item"><strong>Payment Method</strong><?= strtoupper(htmlspecialchars($reg['payment_method'])); ?></div>
                            <div class="detail-item"><strong>Payment Reference</strong><span class="reference-code"><?= htmlspecialchars($reg['payment_reference']); ?></span></div>
                            <div class="detail-item"><strong>Amount</strong>RM <?= number_format($reg['payment_amount'], 2); ?></div>
                        </div>

                        <form method="POST" class="verification-actions">
                            <input type="hidden" name="booking_id" value="<?= $reg['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" name="verify_payment" class="btn" style="background-color: #27ae60;">
                                ✓ Approve Payment
                            </button>
                            <button type="submit" name="verify_payment" class="btn" style="background-color: #e74c3c;"
                                    onclick="this.form.action.value='reject'; return confirm('Are you sure you want to reject this payment?');">
                                ✗ Reject Payment
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px; color: #666;">
                    <p>No pending payments to verify.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Verified Payments Tab -->
        <div id="verified-tab" class="tab-content">
            <?php if ($verified_result->num_rows > 0): ?>
                <?php while ($reg = $verified_result->fetch_assoc()): ?>
                    <div class="verification-card verified">
                        <div class="card-header">
                            <div>
                                <h3><?= htmlspecialchars($reg['username']); ?></h3>
                                <p style="color: #666; font-size: 14px;"><?= htmlspecialchars($reg['title']); ?></p>
                            </div>
                            <span class="status-badge status-<?= $reg['payment_status']; ?>">
                                <?= ucfirst($reg['payment_status']); ?>
                            </span>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-item"><strong>Workshop Date</strong><?= date('F j, Y', strtotime($reg['workshop_date'])); ?></div>
                            <div class="detail-item"><strong>Payment Reference</strong><?= htmlspecialchars($reg['payment_reference']); ?></div>
                            <div class="detail-item"><strong>Amount</strong>RM <?= number_format($reg['payment_amount'], 2); ?></div>
                            <div class="detail-item"><strong>Verified Date</strong><?= $reg['payment_verified_at'] ? date('M j, Y g:i A', strtotime($reg['payment_verified_at'])) : '-'; ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px; color: #666;">
                    <p>No verified payments yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include 'include/footer.php'; ?>

<script>
function showTab(e, tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabName + '-tab').classList.add('active');
    e.currentTarget.classList.add('active');
}
</script>
</body>
</html>