<?php
session_start();
include("include/db_connect.php");

// Only organizer allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];
$workshop_id = intval($_GET['id']);

// Verify workshop belongs to this organizer
$workshop = $conn->query("
    SELECT * FROM workshops 
    WHERE id = $workshop_id AND organizer_id = '$user_id'
")->fetch_assoc();

if (!$workshop) {
    die("Workshop not found or access denied.");
}

// Handle attendance submission
if (isset($_POST['mark_attendance'])) {
    // Process each participant
    $submitted_attendance = isset($_POST['attendance']) ? $_POST['attendance'] : [];

    // Fetch all verified participants for this workshop
    $participants = $conn->query("
        SELECT id AS booking_id, user_id 
        FROM bookings 
        WHERE workshop_id = $workshop_id 
        AND payment_status = 'verified'
    ");

    if ($participants && $participants->num_rows > 0) {
        while ($row = $participants->fetch_assoc()) {
            $booking_id = intval($row['booking_id']);
            $user_id_part = intval($row['user_id']);

            if (isset($submitted_attendance[$booking_id])) {
                // Mark as attended
                $conn->query("
                    INSERT INTO attendance (booking_id, workshop_id, user_id, attendance_status, check_in_time, check_in_method)
                    VALUES ($booking_id, $workshop_id, $user_id_part, 'present', CURRENT_TIMESTAMP, 'manual')
                    ON DUPLICATE KEY UPDATE 
                        attendance_status = 'present',
                        check_in_time = CURRENT_TIMESTAMP,
                        check_in_method = 'manual'
                ");
            } else {
                // Remove attendance if unchecked
                $conn->query("
                    DELETE FROM attendance 
                    WHERE booking_id = $booking_id
                ");
            }
        }
    }

    $_SESSION['success'] = "Attendance updated successfully.";
    header("Location: attendance_manage.php?id=$workshop_id");
    exit();
}

// Fetch participants with attendance data (exclude cancelled/rejected)
$participants = $conn->query("
    SELECT b.id AS booking_id, 
           u.username, 
           u.email, 
           b.booking_status,
           a.attendance_status,
           a.check_in_time
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    LEFT JOIN attendance a ON b.id = a.booking_id
    WHERE b.workshop_id = $workshop_id
    AND b.payment_status = 'verified'
    ORDER BY u.username
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Attendance</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include("include/nav.php"); ?>

<main>
<div class="card" style="max-width:900px; margin:0 auto;">
    <h2>✍ Manual Attendance</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <p><strong>Workshop:</strong> <?= htmlspecialchars($workshop['title']); ?></p>
    <p><strong>Date:</strong> <?= $workshop['date']; ?> | <strong>Time:</strong> <?= $workshop['time']; ?></p>

    <form method="POST">
        <table class="profile-table">
            <thead>
                <tr>
                    <th>Participant</th>
                    <th style="text-align:center;">Attendance</th>
                    <th style="text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($participants && $participants->num_rows > 0): ?>
                    <?php while ($row = $participants->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']); ?></td>
                            <td style="text-align:center; vertical-align:middle;">
                                <label style="display:flex; align-items:center; justify-content:center; gap:5px; margin:0; cursor:pointer;">
                                    <input type="checkbox" 
                                           name="attendance[<?= $row['booking_id']; ?>]" 
                                           value="attended" 
                                           <?= ($row['attendance_status'] === 'present') ? 'checked' : ''; ?>
                                           style="cursor:pointer;">
                                    Attended
                                </label>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($row['attendance_status'] === 'present'): ?>
                                    <span class="status-badge status-present">Checked In</span>
                                    <?php if ($row['check_in_time']): ?>
                                        <br><small style="color:#666;"><?= date('g:i A', strtotime($row['check_in_time'])); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="status-badge status-pending">Not Checked In</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding:40px; color:#94a3b8;">
                            No participants registered for this workshop yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($participants && $participants->num_rows > 0): ?>
            <br>
            <button type="submit" name="mark_attendance" class="btn">
                Save Attendance
            </button>
        <?php endif; ?>
    </form>
</div>
</main>

<?php include("include/footer.php"); ?>

</body>
</html>