<?php
include 'include/db_connect.php';
session_start();

// Security: Only Trainers/Admins can access
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'trainer' && $_SESSION['role'] != 'admin')) {
    header("Location: index.php");
    exit();
}

// Handle Feedback Update
if (isset($_POST['update_feedback'])) {
    $sub_id = $_POST['submission_id'];
    $fb = mysqli_real_escape_string($conn, $_POST['feedback']);

    // Update feedback and change status to Reviewed
    mysqli_query($conn, "UPDATE submissions SET feedback_text = '$fb', status = 'Reviewed' WHERE submission_id = '$sub_id'");

    // Notification: Feedback received
    // Get participant ID and Workshop Title from submission
    include_once 'include/notifications_api.php';
    $sub_info = mysqli_query($conn, "SELECT s.participant_id, w.title FROM submissions s JOIN workshops w ON s.workshop_id = w.id WHERE s.submission_id = '$sub_id'")->fetch_assoc();
    if ($sub_info) {
        addNotification($conn, $sub_info['participant_id'], "You received feedback for your project in " . $sub_info['title']);
    }

    header("Location: trainer_view.php?success=1");
    exit();
}

// Fetch submissions with Participant, Workshop, and Team details
// Filter by trainer if logged in as trainer
$where_clause = "";
if ($_SESSION['role'] == 'trainer') {
    $trainer_id = $_SESSION['user_id'];
    $where_clause = "WHERE w.trainer_id = '$trainer_id'";
}

$query = "SELECT s.*, u.username, w.title, t.name as team_name 
          FROM submissions s 
          JOIN users u ON s.participant_id = u.user_id 
          JOIN workshops w ON s.workshop_id = w.id
          LEFT JOIN teams t ON s.team_id = t.id
          $where_clause
          ORDER BY s.submitted_at DESC";
$results = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Trainer Review - SmartWorkshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="card" style="max-width: 100%;">
            <h2>Review Participant Projects</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">Download participant files and provide
                feedback.</p>

            <?php if (isset($_GET['success'])): ?>
                <div
                    style="background:#dcfce7; color:#166534; padding:10px; text-align:center; border-radius:4px; margin-bottom:20px;">
                    Feedback saved and participant notified!
                </div>
            <?php endif; ?>

            <table class="profile-table">
                <thead>
                    <tr>
                        <th>Submission Status</th>
                        <th>Workshop</th>
                        <th>Submitted File</th>
                        <th style="width: 350px;">Feedback</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($results)): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($row['username']); ?></strong>
                                <?php if (!empty($row['team_name'])): ?>
                                    <br><span style="color: #8e44ad; font-size: 0.85rem;">👥 Team:
                                        <?= htmlspecialchars($row['team_name']) ?></span>
                                <?php endif; ?>
                                <br>
                                <span
                                    style="font-size: 0.8rem; padding: 2px 8px; border-radius: 12px; background: <?= ($row['status'] == 'Reviewed') ? '#dcfce7' : '#fef3c7'; ?>; color: <?= ($row['status'] == 'Reviewed') ? '#166534' : '#92400e'; ?>;">
                                    <?= $row['status']; ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['title']); ?></td>
                            <td>
                                <a href="<?= $row['submission_content']; ?>" download class="btn"
                                    style="padding: 5px 10px; width: auto; font-size: 0.8rem; background-color: #3498db;">
                                    Download File
                                </a>
                            </td>
                            <form action="trainer_view.php" method="POST">
                                <td>
                                    <input type="hidden" name="submission_id" value="<?= $row['submission_id']; ?>">
                                    <textarea name="feedback" rows="3"
                                        style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;"
                                        placeholder="Enter comments here..."><?= htmlspecialchars($row['feedback_text']); ?></textarea>
                                </td>
                                <td>
                                    <button type="submit" name="update_feedback" class="btn"
                                        style="padding: 10px; background-color: #2c3e50;">Save</button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($results) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #999;">No project submissions
                                found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>