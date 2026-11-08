<?php
session_start();
include("include/db_connect.php");

//only authenticated user can access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//fetch user
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

//profile pic
if (!empty($user['avatar'])) {
    $profile_pic = "uploads/" . $user['avatar'];
} else {
    $profile_pic = "https://ui-avatars.com/api/?name=" . $user['username'] . "&background=2563eb&color=fff&size=128";
}

//fetch enrolled workshop, for participants
$my_workshops = null;
if ($user['role'] == 'participant') {
    $booking_sql = "SELECT bookings.*, workshops.title, workshops.date, workshops.time 
                    FROM bookings 
                    JOIN workshops ON bookings.workshop_id = workshops.id 
                    WHERE bookings.user_id = '$user_id' 
                    ORDER BY workshops.date ASC";
    $my_workshops = $conn->query($booking_sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include("include/nav.php"); ?>

<main>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success-msg">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 800px; margin: 0 auto;">

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>My Profile</h2>
        <a href="edit_profile.php" class="btn" style="width:auto; padding:8px 15px; font-size:0.9rem;">
            Edit Profile
        </a>
    </div>

    <div style="text-align:center; margin-bottom:30px;">
        <img src="<?= $profile_pic; ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius:50%; border: 4px solid #e2e8f0;">
        <h3 style="margin-top:10px; color:#333;"><?= htmlspecialchars($user['username']); ?></h3>
        <p style="color:#777;"><?= htmlspecialchars($user['email']); ?></p>
    </div>

    <table class="profile-table">
        <tr><th>Username</th><td><?= htmlspecialchars($user['username']); ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($user['email']); ?></td></tr>
        <tr><th>Phone</th><td><?= htmlspecialchars($user['phone']); ?></td></tr>
        <tr><th>Country</th><td><?= htmlspecialchars($user['country']); ?></td></tr>
        <tr>
            <th>Role</th>
            <td>
                <span style="background:#e0f2fe; color:#0284c7; padding:5px 10px; border-radius:15px; text-transform: capitalize;">
                    <?= $user['role']; ?>
                </span>
            </td>
        </tr>
    </table>

    <?php if ($user['role'] == 'participant'): ?>

        <hr style="border:0; border-top:1px solid #eee; margin: 30px 0;">
        <h3 style="text-align:center; color: #2c3e50;">My Registered Workshops</h3>

        <table class="profile-table">
            <thead>
                <tr style="background:#f8fafc;">
                    <th>Workshop</th>
                    <th>Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($my_workshops && $my_workshops->num_rows > 0): ?>
                <?php while ($bk = $my_workshops->fetch_assoc()): ?>

                <tr>
                    <td><strong><?= htmlspecialchars($bk['title']); ?></strong></td>
                    <td><?= $bk['date']; ?> <small>(<?= $bk['time']; ?>)</small></td>

                    <td style="text-align:right;">

                        <!-- FEEDBACK BUTTON -->
                        <a href="feedback.php?workshop_id=<?= $bk['workshop_id']; ?>" 
                           class="btn" 
                           style="background:#10b981; font-size:0.8rem; padding:6px 10px; margin-bottom:5px;">
                            Give Feedback
                        </a>

                        <!-- CERTIFICATE BUTTON -->
                        <a href="certificate.php?workshop_id=<?= $bk['workshop_id']; ?>" 
                           class="btn" 
                           style="background:#6366f1; font-size:0.8rem; padding:6px 10px; margin-bottom:5px;">
                            Certificate
                        </a>

                        <!-- UNENROLL BUTTON (ORIGINAL) -->
                        <a href="booking_action.php?leave_id=<?= $bk['workshop_id']; ?>" 
                           style="color:white; background:#ef4444; padding:6px 10px; border-radius:4px; text-decoration:none; font-size:0.8rem;"
                           onclick="return confirm('Are you sure you want to unenroll?');">
                            Unenroll
                        </a>

                    </td>
                </tr>

                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align:center; color:#999; padding:20px;">
                        You haven't joined any workshops yet.
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>

    <?php endif; ?>

    <br>
    <a href="logout.php" class="btn" style="background-color: #ef4444; display: block; text-align: center; max-width: 200px; margin: 20px auto 0;">
        Logout
    </a>

</div>
</main>
<?php include 'include/footer.php'; ?>

</body>
</html>
