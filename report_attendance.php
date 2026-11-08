<?php
session_start();
include 'include/db_connect.php';

// Check if user is organizer or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'organizer' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

// Get filter values
$workshop_filter = isset($_GET['workshop_id']) ? intval($_GET['workshop_id']) : 0;

// Fetch all workshops for filter dropdown
$all_workshops = mysqli_query($conn, "SELECT * FROM workshops ORDER BY date DESC");

// Build query based on filter
$attendance_query = "SELECT b.*, u.username, u.email, w.title as workshop_name, w.date as workshop_date
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN workshops w ON b.workshop_id = w.id";

if ($workshop_filter > 0) {
    $attendance_query .= " WHERE b.workshop_id = $workshop_filter";
}

$attendance_query .= " ORDER BY w.date DESC, u.username ASC";
$attendance_result = mysqli_query($conn, $attendance_query);

// Get attendance summary
if ($workshop_filter > 0) {
    $summary_query = "SELECT COUNT(*) as total FROM bookings WHERE workshop_id = $workshop_filter";
} else {
    $summary_query = "SELECT COUNT(*) as total FROM bookings";
}

$summary = mysqli_fetch_assoc(mysqli_query($conn, $summary_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - Digital Skills Workshop</title>
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
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .report-header h1 {
            color: #6c5ce7;
        }
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
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
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .summary-card.total {
            border-left: 5px solid #6c5ce7;
        }
        .summary-number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        .summary-label {
            color: #666;
            margin-top: 5px;
        }
        .attendance-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .attendance-table th {
            background: #6c5ce7;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .attendance-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .btn-back {
            background: #6c5ce7;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include 'include/nav.php'; ?>
    
    

    <div class="report-container">
        <div class="report-header">
            <h1>📋 Attendance Report</h1>
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
                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="summary-cards">
            <div class="summary-card total">
                <div class="summary-number"><?php echo $summary['total'] ?: 0; ?></div>
                <div class="summary-label">Total Registered</div>
            </div>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Participant Name</th>
                    <th>Email</th>
                    <th>Workshop</th>
                    <th>Date</th>
                    <th>Registered On</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = 1;
                while ($row = mysqli_fetch_assoc($attendance_result)):
                ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['workshop_name']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($row['workshop_date'])); ?></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="margin-top: 30px; text-align: center;">
            <a href="dashboard_analytics.php" class="btn-back">← Back to Dashboard</a>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
</body>
</html>