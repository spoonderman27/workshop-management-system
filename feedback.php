<?php
session_start();
include 'include/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];

// Check if workshop_id is provided
$workshop_id = isset($_GET['workshop_id']) ? intval($_GET['workshop_id']) : 0;

// If no workshop_id, show list of all workshops
if ($workshop_id == 0) {
    // Fetch all workshops the participant is enrolled in
    $workshops_query = "SELECT w.*, b.created_at as enrolled_date,
        (SELECT COUNT(*) FROM feedback WHERE user_id = $user_id AND workshop_id = w.id) as has_feedback,
        (SELECT rating FROM feedback WHERE user_id = $user_id AND workshop_id = w.id) as my_rating,
        (SELECT comment FROM feedback WHERE user_id = $user_id AND workshop_id = w.id) as my_comment
        FROM bookings b
        JOIN workshops w ON b.workshop_id = w.id
        WHERE b.user_id = $user_id
        ORDER BY w.date DESC";
    $workshops_result = mysqli_query($conn, $workshops_query);
    
    // Show workshop list
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Feedback - Digital Skills Workshop</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <?php include 'include/nav.php'; ?>
        
        <div class="feedback-dashboard">
            <h2> Feedback</h2>
            
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
            
            <?php if (mysqli_num_rows($workshops_result) > 0): ?>
                <?php while ($workshop = mysqli_fetch_assoc($workshops_result)): ?>
                    <div class="workshop-feedback-card">
                        <div class="workshop-feedback-header">
                            <div class="workshop-info">
                                <h3><?php echo htmlspecialchars($workshop['title']); ?></h3>
                                <p>📅 <?php echo date('F j, Y', strtotime($workshop['date'])); ?> at <?php echo date('g:i A', strtotime($workshop['time'])); ?></p>
                                <p>📍 <?php echo htmlspecialchars($workshop['location']); ?></p>
                                <p>👨‍🏫 Trainer: <?php echo htmlspecialchars($workshop['trainer']); ?></p>
                            </div>
                            <div class="feedback-status">
                                <?php if ($workshop['has_feedback'] > 0): ?>
                                    <span class="status-badge status-completed">✓ Feedback Submitted</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">⏳ Feedback Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($workshop['description']); ?></p>
                        
                        <?php if ($workshop['has_feedback'] > 0): ?>
                            <!-- Show existing feedback -->
                            <div class="my-feedback-display">
                                <h4>Your Feedback:</h4>
                                <div class="my-rating">
                                    <?php 
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $workshop['my_rating'] ? '★' : '☆';
                                    }
                                    ?>
                                    (<?php echo $workshop['my_rating']; ?>/5)
                                </div>
                                <div class="my-comment">
                                    "<?php echo htmlspecialchars($workshop['my_comment']); ?>"
                                </div>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="feedback.php?workshop_id=<?php echo $workshop['id']; ?>&edit=1" class="btn-submit-feedback" style="width: 200px;">
                                     Edit Feedback
                                </a>
                                <a href="view_certificate.php?workshop_id=<?php echo $workshop['id']; ?>" class="btn-submit-feedback" style="background: linear-gradient(135deg, #00b894, #00cec9); width: 200px;">
                                     View My Certificate
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Show submit feedback button -->
                            <div class="action-buttons">
                                <a href="feedback.php?workshop_id=<?php echo $workshop['id']; ?>" class="btn-submit-feedback">
                                     Submit Feedback
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-workshops">
                    <h3>No workshops enrolled yet</h3>
                    <p>You haven't joined any workshops. Browse available workshops to get started!</p>
                    <a href="index.php" class="btn" style="width: auto; padding: 10px 30px; margin-top: 10px;">Browse Workshops</a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php include 'include/footer.php'; ?>
    </body>
    </html>
    <?php
    exit();
}

// If workshop_id is provided, continue with feedback form logic
// Verify user is enrolled in this workshop
$enrollment_check = "SELECT b.id FROM bookings b WHERE b.user_id = $user_id AND b.workshop_id = $workshop_id";
$enrollment_result = mysqli_query($conn, $enrollment_check);

if (mysqli_num_rows($enrollment_result) == 0) {
    $_SESSION['error'] = "You are not enrolled in this workshop.";
    header("Location: my_workshops.php");
    exit();
}

// Fetch workshop details
$workshop_query = "SELECT * FROM workshops WHERE id = $workshop_id";
$workshop_result = mysqli_query($conn, $workshop_query);
$workshop = mysqli_fetch_assoc($workshop_result);

if (!$workshop) {
    $_SESSION['error'] = "Workshop not found.";
    header("Location: my_workshops.php");
    exit();
}

// Check if user already submitted feedback and get existing feedback
$check_feedback = "SELECT * FROM feedback WHERE user_id = $user_id AND workshop_id = $workshop_id";
$feedback_result = mysqli_query($conn, $check_feedback);
$existing_feedback = mysqli_fetch_assoc($feedback_result);
$is_editing = isset($_GET['edit']) && $_GET['edit'] == 1 && $existing_feedback;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_editing ? 'Edit' : 'Submit'; ?> Feedback - Digital Skills Workshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'include/nav.php'; ?>

    <div class="container">
        <div class="feedback-form">
            <h2><?php echo $is_editing ? ' Edit Feedback' : ' Submit Feedback'; ?></h2>
            
            <?php if ($workshop): ?>
                <p><strong>Workshop:</strong> <?php echo htmlspecialchars($workshop['title']); ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($workshop['date'])); ?></p>
                
                <?php if ($existing_feedback && !$is_editing): ?>
                    <div class="success-message">
                        ✅ You have already submitted feedback for this workshop. Thank you!
                    </div>
                    <a href="feedback.php?workshop_id=<?php echo $workshop_id; ?>&edit=1" class="btn">Edit My Feedback</a>
                    <a href="view_certificate.php?workshop_id=<?php echo $workshop_id; ?>" class="btn">View My Certificate</a>
                    <a href="feedback.php" class="btn btn-secondary" style="margin-top: 10px;">Back to Feedback List</a>
                <?php else: ?>
                    <form action="feedback_action.php" method="POST">
                        <input type="hidden" name="workshop_id" value="<?php echo $workshop_id; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                        <?php if ($is_editing): ?>
                            <input type="hidden" name="edit_feedback" value="1">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label><strong>Rate this workshop:</strong></label>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" <?php echo ($is_editing && $existing_feedback['rating'] == 5) ? 'checked' : ''; ?> required>
                                <label for="star5">★</label>
                                <input type="radio" id="star4" name="rating" value="4" <?php echo ($is_editing && $existing_feedback['rating'] == 4) ? 'checked' : ''; ?>>
                                <label for="star4">★</label>
                                <input type="radio" id="star3" name="rating" value="3" <?php echo ($is_editing && $existing_feedback['rating'] == 3) ? 'checked' : ''; ?>>
                                <label for="star3">★</label>
                                <input type="radio" id="star2" name="rating" value="2" <?php echo ($is_editing && $existing_feedback['rating'] == 2) ? 'checked' : ''; ?>>
                                <label for="star2">★</label>
                                <input type="radio" id="star1" name="rating" value="1" <?php echo ($is_editing && $existing_feedback['rating'] == 1) ? 'checked' : ''; ?>>
                                <label for="star1">★</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><strong>Your Comments:</strong></label>
                            <textarea name="comment" placeholder="Share your experience with this workshop..." required><?php echo $is_editing ? htmlspecialchars($existing_feedback['comment']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" name="submit_feedback"><?php echo $is_editing ? 'Update Feedback' : 'Submit Feedback'; ?></button>
                        <a href="feedback.php" class="btn btn-secondary" style="margin-top: 10px;">Cancel</a>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
</body>
</html>