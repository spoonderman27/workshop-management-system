<?php
session_start();
include 'include/db_connect.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch bookings with workshop details and attendance info
$workshops_query = "
    SELECT b.*, w.title, w.date AS workshop_date, w.time, w.location, w.trainer,
           a.attendance_status, a.check_in_time, a.check_in_method,
           (SELECT COUNT(*) FROM feedback 
            WHERE user_id = $user_id AND workshop_id = w.id) AS has_feedback
    FROM bookings b
    JOIN workshops w ON b.workshop_id = w.id
    LEFT JOIN attendance a ON b.id = a.booking_id
    WHERE b.user_id = $user_id
    ORDER BY w.date DESC, b.created_at DESC
";
$workshops_result = mysqli_query($conn, $workshops_query);

// Handle cancellation
if(isset($_POST['cancel_booking'])){
    $booking_id = intval($_POST['booking_id']);
    $verify_stmt = $conn->prepare("SELECT * FROM bookings WHERE id=? AND user_id=?");
    $verify_stmt->bind_param("ii", $booking_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if($verify_result->num_rows > 0){
        $cancel_stmt = $conn->prepare("UPDATE bookings SET booking_status='cancelled' WHERE id=?");
        $cancel_stmt->bind_param("i", $booking_id);

        if($cancel_stmt->execute()){
            $_SESSION['success'] = "Booking cancelled successfully.";
        } else {
            $_SESSION['error'] = "Failed to cancel booking.";
        }
    }
    header("Location: my_workshops.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Workshops</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>
<?php include 'include/nav.php'; ?>

<main>
<div style="max-width:1000px;margin:0 auto;">
<h2 class="page-title">📝 My Workshops</h2>

<?php if(isset($_SESSION['success'])): ?>
<div class="success-message"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="error-message"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if(mysqli_num_rows($workshops_result) > 0): ?>
<?php while($workshop = mysqli_fetch_assoc($workshops_result)): ?>

<div class="registration-card">
    <div class="card-header">
        <div>
            <h3><?= htmlspecialchars($workshop['title']); ?></h3>
            <p>📅 <?= date('F j, Y', strtotime($workshop['workshop_date'])); ?>
               at <?= date('g:i A', strtotime($workshop['time'])); ?></p>
        </div>
        <span class="status-badge status-<?= $workshop['booking_status']; ?>">
            <?= ucfirst($workshop['booking_status']); ?>
        </span>
    </div>

    <div class="detail-grid">
        <div class="detail-item"><strong>Location</strong><?= htmlspecialchars($workshop['location']); ?></div>
        <div class="detail-item"><strong>Trainer</strong><?= htmlspecialchars($workshop['trainer']); ?></div>
        <div class="detail-item"><strong>Payment Method</strong><?= htmlspecialchars($workshop['payment_method']); ?></div>
        <div class="detail-item">
            <strong>Payment Status</strong>
            <span class="status-badge status-<?= $workshop['payment_status']; ?>"><?= ucfirst($workshop['payment_status']); ?></span>
        </div>

        <?php if($workshop['attendance_status']): ?>
        <div class="detail-item">
            <strong>Attendance</strong>
            <span class="status-badge status-<?= $workshop['attendance_status']; ?>">
                <?= ucfirst($workshop['attendance_status']); ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if($workshop['check_in_time']): ?>
        <div class="detail-item">
            <strong>Check-in Time</strong>
            <?= date('g:i A', strtotime($workshop['check_in_time'])); ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="card-footer">

        <!-- Resubmit Payment Button (for rejected payments) --!>

        <?php if($workshop['payment_status'] == 'rejected' && strtotime($workshop['workshop_date']) > time()): ?>
        <a href="resubmit_payment.php?booking_id=<?= $workshop['id']; ?>" class="btn" style="background:#f39c12;">
            🔄 Resubmit Payment
        </a>
        
        <form method="POST" onsubmit="return confirm('Cancel this booking?');">
            <input type="hidden" name="booking_id" value="<?= $workshop['id']; ?>">
            <button type="submit" name="cancel_booking" class="btn" style="background:#e74c3c;">Cancel Booking</button>
        </form>

        <!-- Cancel Booking (for pending/other statuses) -->
        <?php elseif($workshop['booking_status'] == 'registered' && $workshop['payment_status'] != 'rejected' && strtotime($workshop['workshop_date']) > time()): ?>
        <form method="POST" onsubmit="return confirm('Cancel this booking?');">
            <input type="hidden" name="booking_id" value="<?= $workshop['id']; ?>">
            <button type="submit" name="cancel_booking" class="btn" style="background:#e74c3c;">Cancel Booking</button>
        </form>
        <?php endif; ?>

        <!-- Certificate -->
        <?php if($workshop['has_feedback'] > 0): ?>
        <a href="view_certificate.php?workshop_id=<?= $workshop['workshop_id']; ?>" class="btn" style="background:#00b894;">
            🏆 View Certificate
        </a>

        <!-- Feedback (only for verified/confirmed payments after workshop date) -->
        <?php elseif(strtotime($workshop['workshop_date']) < time() && in_array(strtolower($workshop['payment_status']), ['verified', 'confirmed'])): ?>
        <a href="feedback.php?workshop_id=<?= $workshop['workshop_id']; ?>" class="btn" style="background:#6c5ce7;">
            📝 Submit Feedback
        </a>
        <?php endif; ?>

    </div>
</div>

<?php endwhile; ?>
<?php else: ?>
<div class="card" style="text-align:center;padding:50px;">
    <h3>No workshops booked yet</h3>
    <p>Browse available workshops to get started.</p>
    <a href="index.php" class="btn">Browse Workshops</a>
</div>
<?php endif; ?>

</div>
</main>

<?php include 'include/footer.php'; ?>
</body>
</html>