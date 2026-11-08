<?php
include 'include/db_connect.php';
session_start();

// Get the specific ID from the URL link
$workshop_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Check if user is enrolled
if (isset($_SESSION['role']) && $_SESSION['role'] == 'participant') {
    $uid = $_SESSION['user_id'];
    $check_booking = "SELECT id FROM bookings WHERE user_id = '$uid' AND workshop_id = '$workshop_id' AND booking_status != 'cancelled'";
    $booking_result = mysqli_query($conn, $check_booking);
    if (mysqli_num_rows($booking_result) == 0) {
        $_SESSION['error'] = "You must be enrolled to access this page.";
        header("Location: index.php");
        exit();
    }
}

// Fetch materials only for this specific workshop
$query = "SELECT * FROM learning_materials WHERE workshop_id = '$workshop_id'";
$result = mysqli_query($conn, $query);

// Fetch workshop title for the heading
$workshop_info = mysqli_query($conn, "SELECT title FROM workshops WHERE id = '$workshop_id'");
$w_row = mysqli_fetch_assoc($workshop_info);
$title = $w_row ? $w_row['title'] : "Workshop Materials";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Materials - <?= htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="card">
            <h2>Materials for: <?= htmlspecialchars($title); ?></h2>
            <hr style="margin-bottom: 20px; border: 0; border-top: 1px solid #eee;">

            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="profile-table">
                    <thead>
                        <tr>
                            <th>File Title</th>
                            <th>Upload Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']); ?></td>
                                <td><?= date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="<?= $row['file_path']; ?>" class="btn"
                                        style="padding: 5px 15px; width: auto; background-color: #27ae60;" download>Download</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 20px;">No materials have been uploaded for this workshop yet.</p>
            <?php endif; ?>

            <div style="margin-top: 20px; text-align: center;">
                <a href="index.php" style="color: #3498db; text-decoration: none; font-weight: bold;">← Back to Home</a>
            </div>
        </div>
    </main>

</body>

</html>