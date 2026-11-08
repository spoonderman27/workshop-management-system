<?php
session_start();
include("include/db_connect.php");

// only organizer allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    die("Access Denied. You are not an organizer.");
}

$user_id = $_SESSION['user_id'];


/* ============================
   EXISTING WORKSHOP LIST
   ============================ */

$result = $conn->query(
    "SELECT * FROM workshops 
     WHERE organizer_id = '$user_id' 
     ORDER BY date ASC"
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Organizer Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-msg">
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-msg">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>


        <!-- ============================
     EXISTING WORKSHOP MANAGEMENT
     ============================ -->
        <div class="card" style="max-width: 1000px; margin: 0 auto;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                <h2 style="margin:0; text-align:left; color:#2c3e50; text-shadow:none;">
                    My Workshops
                </h2>

                <a href="create_workshop.php" class="btn"
                    style="width:auto; padding:10px 20px; background-color:#10b981; margin-top:0;">
                    + Create New
                </a>
            </div>

            <table class="profile-table">
                <thead style="background-color: #f8fafc;">
                    <tr>
                        <th>Title</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight:600; color:#334155;">
                                    <?= htmlspecialchars($row['title']); ?>
                                </td>

                                <td style="font-size:0.9rem; color:#64748b;">
                                    <?= $row['date']; ?><br>
                                    <small><?= $row['time']; ?></small>
                                </td>

                                <td><?= htmlspecialchars($row['location']); ?></td>

                                <td style="text-align:right;">

                                    <a href="attendance_manage.php?id=<?= $row['id']; ?>"
                                        style="color:#10b981; font-weight:600; margin-right:15px; text-decoration:none;">
                                        📋 Attendance
                                    </a>

                                    <a href="view_participants.php?id=<?= $row['id']; ?>"
                                        style="color:#7c3aed; font-weight:600; margin-right:15px; text-decoration:none;">
                                        View Participants
                                    </a>

                                    <a href="edit_workshop.php?id=<?= $row['id']; ?>"
                                        style="color:#2563eb; font-weight:600; margin-right:15px; text-decoration:none;">
                                        Edit
                                    </a>

                                    <a href="workshop_action.php?delete_id=<?= $row['id']; ?>"
                                        style="color:#ef4444; font-weight:600; text-decoration:none;"
                                        onclick="return confirm('Are you sure you want to delete this event?');">
                                        Delete
                                    </a>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:40px; color:#94a3b8;">
                                You haven't created any workshops yet.
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