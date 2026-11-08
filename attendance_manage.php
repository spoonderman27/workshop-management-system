<?php
session_start();
include("include/db_connect.php");

// Only organizer allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    die("Access Denied");
}

$workshop_id = intval($_GET['id']);

// Get workshop info
$workshop = $conn->query("
    SELECT * FROM workshops WHERE id = $workshop_id
")->fetch_assoc();

// Get participants (exclude cancelled/rejected)
$participants = $conn->query("
    SELECT b.id AS booking_id, u.username, u.email, b.booking_status
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    WHERE b.workshop_id = $workshop_id
      AND b.booking_status IN ('registered','confirmed','attended')
      AND b.booking_status != 'cancelled'
      AND b.payment_status != 'rejected'
    ORDER BY u.username
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include("include/nav.php"); ?>

<main>
<div class="card" style="max-width:1000px;margin:0 auto;">

    <h2>📋 Attendance Management</h2>

    <p><strong>Workshop:</strong> <?= htmlspecialchars($workshop['title']) ?></p>
    <p><strong>Date:</strong> <?= $workshop['date'] ?> | <strong>Time:</strong> <?= $workshop['time'] ?></p>

    <hr>

    <!-- QR SECTION -->
    <h3>QR Code Check-in</h3>
    <p>Participants scan this QR during the event.</p>
    <img src="img/attendance_qr.jpg" alt="QR Code"
         style="width:200px;border:1px solid #ccc;padding:10px;">

    <hr>

    <!-- MANUAL ATTENDANCE -->
    <h3>Manual Attendance</h3>
    <a href="attendance_manual.php?id=<?= $workshop_id ?>" class="btn">
        Open Manual Attendance
    </a>

    <hr>

    <!-- PARTICIPANT LIST -->
    <h3>Participant List</h3>
    <table class="profile-table">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
        </tr>

        <?php if ($participants && $participants->num_rows > 0): ?>
            <?php while ($row = $participants->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <?php
                        if ($row['booking_status'] === 'attended') {
                            echo "<span style='color:green;font-weight:bold;'>Attended</span>";
                        } elseif ($row['booking_status'] === 'confirmed') {
                            echo "<span style='color:green;font-weight:bold;'>Confirmed</span>";
                        } else {
                            echo "<span style='color:#999;'>Registered</span>";
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="text-align:center;">No participants</td>
            </tr>
        <?php endif; ?>
    </table>

</div>
</main>

<?php include("include/footer.php"); ?>
</body>
</html>