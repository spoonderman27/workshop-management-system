<?php
session_start();
include 'include/db_connect.php';

// Check if user is organizer, admin, OR trainer
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'organizer' && $_SESSION['role'] != 'admin' && $_SESSION['role'] != 'trainer')) {
    header("Location: login.php");
    exit();
}

// Get filter values
$workshop_filter = isset($_GET['workshop_id']) ? intval($_GET['workshop_id']) : 0;
$rating_filter = isset($_GET['rating']) ? intval($_GET['rating']) : 0;

// Fetch all workshops for filter dropdown - FIXED: Use correct column names
$all_workshops = mysqli_query($conn, "SELECT * FROM workshops ORDER BY date DESC");

// Build query based on filters - FIXED: Use correct column names
$feedback_query = "SELECT f.*, u.username as user_name, w.title as workshop_name, w.date as workshop_date
    FROM feedback f
    JOIN users u ON f.user_id = u.user_id
    JOIN workshops w ON f.workshop_id = w.id
    WHERE 1=1";

if ($workshop_filter > 0) {
    $feedback_query .= " AND f.workshop_id = $workshop_filter";
}

if ($rating_filter > 0) {
    $feedback_query .= " AND f.rating = $rating_filter";
}

$feedback_query .= " ORDER BY f.created_at DESC";

$feedback_result = mysqli_query($conn, $feedback_query);

// Get feedback statistics
$stats_where = "";
if ($workshop_filter > 0) {
    $stats_where = " WHERE workshop_id = $workshop_filter";
}

$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, AVG(rating) as avg FROM feedback" . $stats_where));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Report - Digital Skills Workshop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .report-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .report-header h1 {
            color: #6c5ce7;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-form select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-form button {
            background: #6c5ce7;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .stats-bar {
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            color: white;
            padding: 25px;
            border-radius: 10px;
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .feedback-grid {
            display: grid;
            gap: 20px;
        }

        .feedback-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .feedback-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .feedback-workshop {
            color: #6c5ce7;
            font-weight: bold;
            font-size: 18px;
        }

        .feedback-rating {
            color: #ffc107;
            font-size: 20px;
        }

        .feedback-meta {
            color: #888;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .feedback-comment {
            color: #555;
            line-height: 1.6;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #6c5ce7;
        }

        .btn-back {
            background: #6c5ce7;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 30px;
        }

        .no-data {
            text-align: center;
            padding: 50px;
            color: #888;
            background: white;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <?php include 'include/nav.php'; ?>

    <div class="report-container">
        <div class="report-header">
            <h1>⭐ Feedback Report</h1>
            <form class="filter-form" method="GET">
                <select name="workshop_id">
                    <option value="0">All Workshops</option>
                    <?php
                    mysqli_data_seek($all_workshops, 0);
                    while ($w = mysqli_fetch_assoc($all_workshops)):
                        ?>
                        <option value="<?php echo $w['id']; ?>" <?php echo $workshop_filter == $w['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($w['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <select name="rating">
                    <option value="0">All Ratings</option>
                    <option value="5" <?php echo $rating_filter == 5 ? 'selected' : ''; ?>>5 Stars</option>
                    <option value="4" <?php echo $rating_filter == 4 ? 'selected' : ''; ?>>4 Stars</option>
                    <option value="3" <?php echo $rating_filter == 3 ? 'selected' : ''; ?>>3 Stars</option>
                    <option value="2" <?php echo $rating_filter == 2 ? 'selected' : ''; ?>>2 Stars</option>
                    <option value="1" <?php echo $rating_filter == 1 ? 'selected' : ''; ?>>1 Star</option>
                </select>

                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-value"><?php echo $stats['total'] ?: 0; ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $stats['avg'] ? number_format($stats['avg'], 1) : 'N/A'; ?></div>
                <div class="stat-label">Average Rating</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    <?php
                    $stars = round($stats['avg'] ?: 0);
                    for ($i = 0; $i < $stars; $i++)
                        echo '⭐';
                    ?>
                </div>
                <div class="stat-label">Star Rating</div>
            </div>
        </div>

        <div class="feedback-grid">
            <?php if (mysqli_num_rows($feedback_result) > 0): ?>
                <?php while ($feedback = mysqli_fetch_assoc($feedback_result)): ?>
                    <div class="feedback-card">
                        <div class="feedback-card-header">
                            <div>
                                <div class="feedback-workshop"><?php echo htmlspecialchars($feedback['workshop_name']); ?></div>
                                <div class="feedback-meta">
                                    By: <?php echo htmlspecialchars($feedback['user_name']); ?> |
                                    <?php echo date('M j, Y g:i A', strtotime($feedback['created_at'])); ?>
                                </div>
                            </div>
                            <div class="feedback-rating">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $feedback['rating'] ? '★' : '☆';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="feedback-comment">
                            "<?php echo htmlspecialchars($feedback['comment']); ?>"
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <h3>No feedback found</h3>
                    <p>No feedback matches your current filters.</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center;">
            <a href="dashboard_analytics.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
</body>

</html>