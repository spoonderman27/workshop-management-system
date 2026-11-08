<?php
session_start();
include 'include/db_connect.php';

// Check if user is organizer or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'organizer' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

// Get statistics
$total_workshops = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM workshops"))['count'];
$total_participants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'participant'"))['count'];
$total_registrations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
$avg_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg FROM feedback"))['avg'];
$total_feedback = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM feedback"))['count'];
$total_certificates = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM certificates"))['count'];

// Get recent workshops with stats
$workshops_query = "SELECT w.*,
    (SELECT COUNT(*) FROM bookings b WHERE b.workshop_id = w.id) as registered,
    (SELECT AVG(f.rating) FROM feedback f WHERE f.workshop_id = w.id) as avg_rating
    FROM workshops w
    ORDER BY w.date DESC
    LIMIT 10";
$workshops_result = mysqli_query($conn, $workshops_query);

// Get rating distribution
$rating_dist = [];
for ($i = 1; $i <= 5; $i++) {
    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM feedback WHERE rating = $i"))['count'];
    $rating_dist[$i] = $count;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Digital Skills Workshop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .dashboard h1 {
            color: #6c5ce7;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }
        .stat-card.secondary {
            background: linear-gradient(135deg, #00b894, #00cec9);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #fdcb6e, #f39c12);
        }
        .stat-card.info {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
        }
        .stat-number {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .section-title {
            color: #333;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #6c5ce7;
        }
        .workshop-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .workshop-table th {
            background: #6c5ce7;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .workshop-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .workshop-table tr:hover {
            background: #f8f9fa;
        }
        .rating-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 0;
        }
        .rating-label {
            width: 60px;
        }
        .rating-fill {
            height: 20px;
            background: linear-gradient(90deg, #ffc107, #ff9800);
            border-radius: 10px;
        }
        .rating-count {
            color: #666;
            font-size: 14px;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .btn-view {
            background: #6c5ce7;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-view:hover {
            background: #5b4cdb;
        }
        .quick-links {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .quick-link {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            text-decoration: none;
            color: #6c5ce7;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .quick-link:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <?php include 'include/nav.php'; ?>
    
   

    <div class="dashboard">
        <h1>📊 Analytics Dashboard</h1>

        <div class="quick-links">
            <a href="report_attendance.php" class="quick-link">📋 Attendance Report</a>
            <a href="report_feedback.php" class="quick-link">⭐ Feedback Report</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_workshops; ?></div>
                <div class="stat-label">Total Workshops</div>
            </div>
            <div class="stat-card secondary">
                <div class="stat-number"><?php echo $total_participants; ?></div>
                <div class="stat-label">Total Participants</div>
            </div>
            <div class="stat-card info">
                <div class="stat-number"><?php echo $total_registrations; ?></div>
                <div class="stat-label">Total Registrations</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-number"><?php echo $avg_rating ? number_format($avg_rating, 1) : 'N/A'; ?></div>
                <div class="stat-label">Avg. Rating</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_feedback; ?></div>
                <div class="stat-label">Total Feedback</div>
            </div>
            <div class="stat-card secondary">
                <div class="stat-number"><?php echo $total_certificates; ?></div>
                <div class="stat-label">Certificates Issued</div>
            </div>
        </div>

        <div class="chart-container">
            <h3 class="section-title">⭐ Rating Distribution</h3>
            <?php
            $max_rating_count = max($rating_dist);
            foreach ($rating_dist as $star => $count):
                $percentage = $max_rating_count > 0 ? ($count / $max_rating_count) * 100 : 0;
            ?>
                <div class="rating-bar">
                    <span class="rating-label"><?php echo $star; ?> Star</span>
                    <div style="flex: 1; background: #eee; border-radius: 10px; height: 20px;">
                        <div class="rating-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                    <span class="rating-count"><?php echo $count; ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 class="section-title">📅 Recent Workshops</h3>
        <table class="workshop-table">
            <thead>
                <tr>
                    <th>Workshop Name</th>
                    <th>Date</th>
                    <th>Registered</th>
                    <th>Avg Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($workshop = mysqli_fetch_assoc($workshops_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($workshop['title']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($workshop['date'])); ?></td>
                    <td><?php echo $workshop['registered']; ?></td>
                    <td>
                        <?php if ($workshop['avg_rating']): ?>
                            ⭐ <?php echo number_format($workshop['avg_rating'], 1); ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="view_feedback.php?workshop_id=<?php echo $workshop['id']; ?>" class="btn-view">View Feedback</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include 'include/footer.php'; ?>
</body>
</html>