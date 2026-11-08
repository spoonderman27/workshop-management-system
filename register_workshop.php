<?php
session_start();
include 'include/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to book a workshop.";
    header("Location: login.php");
    exit();
}

// Check if participant
if ($_SESSION['role'] !== 'participant') {
    $_SESSION['error'] = "Only participants can book workshops.";
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch workshop details
$stmt = $conn->prepare("SELECT * FROM workshops WHERE id=?");
$stmt->bind_param("i", $workshop_id);
$stmt->execute();
$result = $stmt->get_result();
$workshop = $result->fetch_assoc();

if (!$workshop) {
    $_SESSION['error'] = "Workshop not found.";
    header("Location: index.php");
    exit();
}

// Check if already booked
$check_stmt = $conn->prepare("SELECT * FROM bookings WHERE workshop_id=? AND user_id=? AND booking_status != 'cancelled'");
$check_stmt->bind_param("ii", $workshop_id, $user_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = "You have already booked this workshop.";
    header("Location: my_workshops.php");
    exit();
}

// Count existing bookings to calculate availability
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM bookings WHERE workshop_id=? AND booking_status != 'cancelled'");
$count_stmt->bind_param("i", $workshop_id);
$count_stmt->execute();
$available_slots = 50 - $count_stmt->get_result()->fetch_assoc()['total'];
if ($available_slots <= 0) {
    $_SESSION['error'] = "This workshop is fully booked.";
    header("Location: index.php");
    exit();
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $payment_reference = mysqli_real_escape_string($conn, $_POST['payment_reference']);
    $payment_amount = $workshop['fee'] > 0 ? $workshop['fee'] : 0;

    $insert_stmt = $conn->prepare("
        INSERT INTO bookings (workshop_id, user_id, payment_method, payment_reference, payment_amount, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $insert_stmt->bind_param("iissd", $workshop_id, $user_id, $payment_method, $payment_reference, $payment_amount);

    if ($insert_stmt->execute()) {
        $_SESSION['success'] = "Booking successful! Please wait for payment verification.";

        // Notification: Notify Organizer (Payment Pending)
        include_once 'include/notifications_api.php';

        $organizer_id = $workshop['organizer_id'];
        $ws_title = $workshop['title'];

        // Get Participant Name
        $u_res = $conn->query("SELECT username FROM users WHERE user_id = '$user_id'");
        if ($u_res && $u_row = $u_res->fetch_assoc()) {
            $u_name = $u_row['username'];
            // Send Notification
            if (!empty($organizer_id)) {
                $msg = "💰 Payment Pending: $u_name has registered for $ws_title. Please verify payment.";
                addNotification($conn, $organizer_id, $msg); // Emoji works now due to db_connect.php fix
            }
        }

        header("Location: my_workshops.php");
        exit();
    } else {
        $_SESSION['error'] = "Booking failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Workshop - <?= htmlspecialchars($workshop['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'include/nav.php'; ?>

    <main>
        <div class="card" style="max-width:700px; margin:0 auto;">
            <h2>Book Workshop</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message"><?= $_SESSION['error'];
                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div
                style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:25px; border-left:4px solid #3498db;">
                <h3><?= htmlspecialchars($workshop['title']); ?></h3>
                <p><strong>Date:</strong> <?= date('F j, Y', strtotime($workshop['date'])); ?></p>
                <p><strong>Time:</strong> <?= date('g:i A', strtotime($workshop['time'])); ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($workshop['location']); ?></p>
                <p><strong>Trainer:</strong> <?= htmlspecialchars($workshop['trainer']); ?></p>
                <p style="color:#27ae60;"><strong>Fee:</strong> RM <?= number_format($workshop['fee'], 2); ?></p>
                <p><strong>Available Slots:</strong> <?= $available_slots; ?></p>
            </div>

            <form method="POST">
                <h3>Payment Information</h3>
                <div style="margin-bottom:20px;">
                    <label>Payment Method *</label>
                    <select name="payment_method" id="payment_method" required onchange="togglePaymentDetails()">
                        <option value="">-- Select Payment Method --</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="qr_code">QR Code Payment (DuitNow)</option>
                    </select>
                </div>

                <div id="bank_details" style="display:none; margin-bottom:20px; border:1px solid #ccc; padding:15px;">
                    <h4>Bank Transfer Details</h4>
                    <p><strong>Account Name:</strong> DSBMS Workshop Organizer</p>
                    <p><strong>Bank Name:</strong> Maybank</p>
                    <p><strong>Account Number:</strong> 16567849400</p>
                </div>

                <div id="qr_details" style="display:none; margin-bottom:20px; border:1px solid #ccc; padding:15px;">
                    <h4>QR Code Payment (DuitNow)</h4>
                    <img src="img/duitnow_qr.jpg" alt="QR Code" style="max-width:200px;">
                </div>

                <div style="margin-bottom:20px;">
                    <label>Payment Reference / Transaction ID *</label>
                    <input type="text" name="payment_reference" required
                        placeholder="Enter transaction ID or reference number">
                </div>

                <div style="display:flex; gap:15px;">
                    <button type="submit" class="btn" style="flex:1;">Complete Booking</button>
                    <a href="index.php" class="btn btn-secondary" style="flex:1; text-align:center;">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'include/footer.php'; ?>

    <script>
        function togglePaymentDetails() {
            const method = document.getElementById("payment_method").value;
            document.getElementById("bank_details").style.display = "none";
            document.getElementById("qr_details").style.display = "none";
            if (method === "bank_transfer") document.getElementById("bank_details").style.display = "block";
            if (method === "qr_code") document.getElementById("qr_details").style.display = "block";
        }
    </script>
</body>

</html>