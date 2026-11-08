<?php
session_start();
include 'include/db_connect.php';

// Check if user is organizer or trainer
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'organizer' && $_SESSION['role'] != 'trainer' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

$workshop_id = isset($_GET['workshop_id']) ? intval($_GET['workshop_id']) : 0;

// Fetch workshop details
$workshop_query = "SELECT * FROM workshops WHERE id = $workshop_id";
$workshop_result = mysqli_query($conn, $workshop_query);
$workshop = mysqli_fetch_assoc($workshop_result);

// Fetch all feedback for this workshop
$feedback_query = "SELECT f.*, u.username as user_name
                   FROM feedback f
                   JOIN users u ON f.user_id = u.user_id
                   WHERE f.workshop_id = $workshop_id
                   ORDER BY f.created_at DESC";
$feedback_result = mysqli_query($conn, $feedback_query);

// Calculate average rating
$avg_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_feedback
              FROM feedback WHERE workshop_id = $workshop_id";
$avg_result = mysqli_query($conn, $avg_query);
$avg_data = mysqli_fetch_assoc($avg_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback - Digital Skills Workshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'include/nav.php'; ?>
  

    <div class="feedback-container">
        <?php if ($workshop): ?>
            <div style="background: linear-gradient(135deg, #6c5ce7, #a855f7); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 30px;">
                <h2 style="color: white; margin: 0 0 10px 0;"><?php echo htmlspecialchars($workshop['title']); ?></h2>
                <p>Feedback Summary</p>
                <div style="font-size: 48px; font-weight: bold;">
                    <?php echo $avg_data['avg_rating'] ? number_format($avg_data['avg_rating'], 1) : 'N/A'; ?>
                </div>
                <div style="color: #ffc107; font-size: 24px;">
                    <?php
                    $avg = round($avg_data['avg_rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $avg ? '★' : '☆';
                    }
                    ?>
                </div>
                <p>Total Feedback: <?php echo $avg_data['total_feedback']; ?></p>
            </div>

            <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                    <div style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: bold; color: #333;">👤 <?php echo htmlspecialchars($feedback['user_name']); ?></span>
                            <span style="color: #888; font-size: 14px;"><?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?></span>
                        </div>
                        <div style="color: #ffc107;">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $feedback['rating'] ? '★' : '☆';
                            }
                            ?>
                            (<?php echo $feedback['rating']; ?>/5)
                        </div>
                        <p style="color: #555; line-height: 1.6;"><?php echo htmlspecialchars($feedback['comment']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 50px; color: #888;">
                    <h3>No feedback yet</h3>
                    <p>Participants haven't submitted feedback for this workshop.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; color: #888;">
                <h3>Workshop not found</h3>
                <p>Please select a valid workshop to view feedback.</p>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard_analytics.php" class="btn">Back to Dashboard</a>
        </div>
    </div>
	<?php include 'include/footer.php'; ?>
</body>
</html>
