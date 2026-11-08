<?php
session_start();
include("include/db_connect.php");

//only trainer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
    die("Access Denied. You are not a trainer.");
}

$user_id = $_SESSION['user_id'];

//trainer workshop by date
$stmt = $conn->prepare("SELECT * FROM workshops WHERE trainer_id = ? ORDER BY date ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>

        <div class="card" style="max-width: 1000px; margin: 0 auto;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                <h2 style="margin:0; text-align:left; color:#2c3e50; text-shadow:none;">My Scheduled Workshops</h2>
            </div>

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
                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 600; color: #334155;">
                                    <?= htmlspecialchars($row['title']); ?>
                                </td>

                                <td style="font-size: 0.9rem; color: #64748b;">
                                    <?= $row['date']; ?> <br>
                                    <small><?= $row['time']; ?></small>
                                </td>

                                <td><?= htmlspecialchars($row['location']); ?></td>

                                <td style="text-align: right;">

                                    <a href="view_participants.php?id=<?= $row['id']; ?>"
                                        style="color: #7c3aed; font-weight:600; text-decoration:none; display:block; margin-bottom:5px;">
                                        View Participants
                                    </a>
                                    <a href="classroom_teams.php?id=<?= $row['id']; ?>"
                                        style="color: #16a085; font-weight:600; text-decoration:none; display:block; margin-bottom:5px;">
                                        👥 View Teams
                                    </a>
                                    <a href="classroom_discussion.php?id=<?= $row['id']; ?>"
                                        style="color: #3498db; font-weight:600; text-decoration:none;">
                                        💬 Join Discussion
                                    </a>

                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 40px; color: #94a3b8;">
                                You have not been assigned to any workshops yet.
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