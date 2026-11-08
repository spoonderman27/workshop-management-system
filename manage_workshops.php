<?php
session_start();
include 'include/db_connect.php';

// Only organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];

// Pending payments count 
$pending_payments_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings b
    JOIN workshops w ON b.workshop_id = w.id
    WHERE w.organizer_id = '$user_id'
    AND b.payment_status = 'pending'
    AND b.booking_status != 'cancelled'
");
$pending_payments = $pending_payments_result ? $pending_payments_result->fetch_assoc()['total'] : 0;

// Fetch workshops for this organizer
$workshops_result = $conn->query("
    SELECT * FROM workshops 
    WHERE organizer_id = '$user_id'
    ORDER BY date ASC
");

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM workshops WHERE id = $delete_id AND organizer_id = '$user_id'");
    $_SESSION['success'] = "Workshop deleted successfully.";
    header("Location: manage_workshops.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workshops - SmartWorkshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'include/nav.php'; ?>

    <main>
        <div class="card" style="max-width: 1000px; margin: 0 auto;">

            <h2 style="margin-top: 0; color: #2c3e50; text-shadow: none;">🚀 Quick Actions</h2>

            <div class="quick-actions">
                <a href="payment_verification.php" class="action-card payments">
                    <h3><?= $pending_payments ?></h3>
                    <p>Pending Payments</p>
                    <?php if ($pending_payments > 0): ?>
                        <span class="badge-notify">Action Needed</span>
                    <?php endif; ?>
                </a>

                <a href="create_workshop.php" class="action-card create">
                    <h3>+</h3>
                    <p>Create Workshop</p>
                </a>

                <a href="report_attendance.php" class="action-card report">
                    <h3>📋</h3>
                    <p>View Reports</p>
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-msg">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <h2 style="margin-top: 30px; color: #2c3e50; text-shadow: none;">My Workshops</h2>

            <table class="profile-table">
                <thead style="background-color: #f8fafc;">
                    <tr>
                        <th>Title</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($workshops_result->num_rows > 0): ?>
                        <?php while ($workshop = $workshops_result->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 600; color: #334155;">
                                    <?= htmlspecialchars($workshop['title']); ?>
                                </td>
                                <td style="font-size: 0.9rem; color: #64748b;">
                                    <?= $workshop['date']; ?><br>
                                    <small><?= $workshop['time']; ?></small>
                                </td>
                                <td><?= htmlspecialchars($workshop['location']); ?></td>
                                <td style="text-align: right;">
                                    <a href="attendance_manage.php?id=<?= $workshop['id']; ?>"
                                        style="color: #10b981; font-weight: 600; margin-right: 15px; text-decoration: none;">
                                        📋 Attendance
                                    </a>

                                    <a href="classroom_discussion.php?id=<?= $workshop['id']; ?>"
                                        style="color: #3498db; font-weight: 600; margin-right: 15px; text-decoration: none;">
                                        💬 Discussion
                                    </a>

                                    <a href="edit_workshop.php?id=<?= $workshop['id']; ?>"
                                        style="color: #2563eb; font-weight: 600; margin-right: 15px; text-decoration: none;">
                                        Edit
                                    </a>

                                    <a href="manage_workshops.php?delete_id=<?= $workshop['id']; ?>"
                                        style="color: #ef4444; font-weight: 600; text-decoration: none;"
                                        onclick="return confirm('Are you sure you want to delete this workshop?');">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                                No workshops created yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </main>

    <?php include 'include/footer.php'; ?>
</body>

</html>