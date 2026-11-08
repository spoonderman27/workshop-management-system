<?php
include 'include/db_connect.php';
session_start();

// 1. SECURITY: Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. DATA: Get the workshop ID and User ID
$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$participant_id = $_SESSION['user_id'];

// Check enrollment
if (isset($_SESSION['role']) && $_SESSION['role'] == 'participant') {
    $check_booking = "SELECT id FROM bookings WHERE user_id = '$participant_id' AND workshop_id = '$workshop_id' AND booking_status != 'cancelled'";
    $booking_result = mysqli_query($conn, $check_booking);
    if (mysqli_num_rows($booking_result) == 0) {
        $_SESSION['error'] = "You must be enrolled to access this page.";
        header("Location: index.php");
        exit();
    }
}

// 3. LOGIC: Handle File Upload (New Submission or Edit)
if (isset($_POST['submit_assignment'])) {
    $w_id = $_POST['workshop_id'];
    $is_edit = isset($_POST['edit_submission_id']) ? $_POST['edit_submission_id'] : null;

    $file_name = $_FILES['assignment_file']['name'];
    $file_tmp = $_FILES['assignment_file']['tmp_name'];
    $file_error = $_FILES['assignment_file']['error'];

    $target_directory = "UPLOAD/submissions/" . time() . "_" . $file_name;

    if ($file_error !== UPLOAD_ERR_OK) {
        echo "<script>alert('Error: File upload failed.');</script>";
    } else {
        if (move_uploaded_file($file_tmp, $target_directory)) {
            // Check team
            $team_check = $conn->query("SELECT team_id FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE t.workshop_id = '$w_id' AND tm.user_id = '$participant_id'");
            $team_row = $team_check->fetch_assoc();
            $team_id = $team_row ? $team_row['team_id'] : 'NULL';

            if ($is_edit) {
                // UPDATE EXISTING (Only if Pending)
                $sql = "UPDATE submissions SET submission_content = '$target_directory', submitted_at = NOW() 
                        WHERE submission_id = '$is_edit' AND status = 'Pending' AND participant_id = '$participant_id'";
                $msg = "Assignment updated successfully!";
            } else {
                // INSERT NEW
                $sql = "INSERT INTO submissions (workshop_id, participant_id, submission_content, status, team_id) 
                        VALUES ('$w_id', '$participant_id', '$target_directory', 'Pending', $team_id)";
                $msg = "Project submitted successfully!";
            }

            if (mysqli_query($conn, $sql)) {
                // Notification: Notify Trainer
                include_once 'include/notifications_api.php';

                // Get Trainer ID and Workshop Title
                $ws_query = "SELECT trainer_id, title FROM workshops WHERE id = '$w_id'";
                $ws_res = mysqli_query($conn, $ws_query);
                if ($ws_row = mysqli_fetch_assoc($ws_res)) {
                    $trainer_id = $ws_row['trainer_id'];
                    $ws_title = $ws_row['title'];

                    // Get Participant Name
                    $u_res = mysqli_query($conn, "SELECT username FROM users WHERE user_id = '$participant_id'");
                    $u_row = mysqli_fetch_assoc($u_res);
                    $u_name = $u_row['username'];

                    // Check for Team Name if team_id matches
                    $team_info = "";
                    if ($team_id && $team_id !== 'NULL') {
                        $t_qry = mysqli_query($conn, "SELECT name FROM teams WHERE id = '$team_id'");
                        if ($t_r = mysqli_fetch_assoc($t_qry)) {
                            $team_info = " (Team: " . $t_r['name'] . ")";
                        }
                    }

                    // Send Notification
                    $notif_msg = "📝 New Submission: $u_name{$team_info} submitted a project for $ws_title.";
                    addNotification($conn, $trainer_id, $notif_msg);
                }

                echo "<script>alert('$msg'); window.location.href='submit_assignment.php?id=$w_id';</script>";
            }
        }
    }
}

// 4. DISPLAY: Fetch workshop title
$w_info = mysqli_query($conn, "SELECT title FROM workshops WHERE id = '$workshop_id'");
$w_row = mysqli_fetch_assoc($w_info);

// 5. HISTORY: Fetch ALL submissions for this user & workshop
$all_subs_query = mysqli_query($conn, "SELECT * FROM submissions WHERE workshop_id = '$workshop_id' AND participant_id = '$participant_id' ORDER BY submitted_at DESC");

// 6. EDIT MODE: Check if user clicked "Edit"
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_res = mysqli_query($conn, "SELECT * FROM submissions WHERE submission_id = '$edit_id' AND status = 'Pending' AND participant_id = '$participant_id'");
    $edit_data = mysqli_fetch_assoc($edit_res);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Submit Project - SmartWorkshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("include/nav.php"); ?>

    <main>
        <div class="form-card"
            style="max-width: 800px; margin: 40px auto; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); background: #fff;">

            <h2 style="color: #2c3e50; text-align: center;">
                <?= $edit_data ? "Edit Submission" : "Submit Your Project" ?>
            </h2>
            <p style="text-align: center; color: #666; margin-bottom: 25px;">
                Workshop: <strong
                    style="color: #8e44ad;"><?= htmlspecialchars($w_row['title'] ?? 'Selected Workshop'); ?></strong>
            </p>

            <form action="submit_assignment.php?id=<?= $workshop_id ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="workshop_id" value="<?= $workshop_id ?>">

                <?php if ($edit_data): ?>
                    <input type="hidden" name="edit_submission_id" value="<?= $edit_data['submission_id'] ?>">
                    <p style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; font-size: 0.9rem;">
                        <strong>Editing:</strong> Replacing previous file submitted on <?= $edit_data['submitted_at'] ?>
                    </p>
                <?php endif; ?>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Upload File (PDF, ZIP, or
                        Image)</label>
                    <input type="file" name="assignment_file" required
                        style="display: block; width: 100%; padding: 12px; border: 2px dashed #ddd; border-radius: 8px; background: #fdfdfd;">
                </div>

                <button type="submit" name="submit_assignment" class="btn"
                    style="width: 100%; background-color: <?= $edit_data ? '#f39c12' : '#8e44ad' ?>; color: white; padding: 12px; border: none; border-radius: 6px; font-size: 1.1rem; cursor: pointer;">
                    <?= $edit_data ? "Update Submission" : "Upload & Submit Work" ?>
                </button>

                <?php if ($edit_data): ?>
                    <div style="text-align: center; margin-top: 10px;">
                        <a href="submit_assignment.php?id=<?= $workshop_id ?>"
                            style="color: #666; text-decoration: none;">Cancel Edit</a>
                    </div>
                <?php endif; ?>
            </form>

            <hr style="margin: 40px 0; border: 0; border-top: 1px solid #eee;">

            <h3 style="color: #2c3e50; margin-bottom: 20px;">Your Submission History</h3>

            <?php if (mysqli_num_rows($all_subs_query) > 0): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; text-align: left;">
                            <th style="padding: 12px; border-bottom: 2px solid #eee;">Date</th>
                            <th style="padding: 12px; border-bottom: 2px solid #eee;">Status</th>
                            <th style="padding: 12px; border-bottom: 2px solid #eee;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sub = mysqli_fetch_assoc($all_subs_query)): ?>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                    <small><?= date("d M Y, h:i A", strtotime($sub['submitted_at'])) ?></small>
                                </td>
                                <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                    <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; 
                                        background: <?= ($sub['status'] == 'Reviewed') ? '#d4edda' : '#fff3cd'; ?>; 
                                        color: <?= ($sub['status'] == 'Reviewed') ? '#155724' : '#856404'; ?>;">
                                        <?= $sub['status'] ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; border-bottom: 1px solid #eee;">
                                    <a href="<?= $sub['submission_content'] ?>" target="_blank"
                                        style="color: #3498db; text-decoration: none; font-size: 0.85rem; margin-right: 10px;">View</a>

                                    <?php if ($sub['status'] == 'Pending'): ?>
                                        <a href="submit_assignment.php?id=<?= $workshop_id ?>&edit_id=<?= $sub['submission_id'] ?>"
                                            style="color: #f39c12; text-decoration: none; font-size: 0.85rem;">Edit</a>
                                    <?php else: ?>
                                        <span style="color: #ccc; font-size: 0.85rem; cursor: not-allowed;"
                                            title="Cannot edit reviewed work">Locked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($sub['feedback_text'])): ?>
                                <tr style="background: #fdfdfd;">
                                    <td colspan="3" style="padding: 10px 12px; border-bottom: 1px solid #eee; font-size: 0.85rem;">
                                        <strong style="color: #666;">Feedback:</strong>
                                        <i><?= htmlspecialchars($sub['feedback_text']) ?></i>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #999;">No submissions found yet.</p>
            <?php endif; ?>

            <div style="margin-top: 25px; text-align: center;">
                <a href="index.php" style="color: #3498db; text-decoration: none; font-size: 0.9rem;">← Back to Home</a>
            </div>
        </div>
    </main>
</body>

</html>