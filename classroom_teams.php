<?php
// classroom_teams.php
session_start();
include 'include/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Check if user is enrolled
if (isset($_SESSION['role']) && $_SESSION['role'] == 'participant') {
    $check_booking = "SELECT id FROM bookings WHERE user_id = '$user_id' AND workshop_id = '$workshop_id' AND booking_status != 'cancelled'";
    $booking_result = mysqli_query($conn, $check_booking);
    if (mysqli_num_rows($booking_result) == 0) {
        $_SESSION['error'] = "You must be enrolled to access this page.";
        header("Location: index.php");
        exit();
    }
}

// Get workshop info
$ws = $conn->query("SELECT title FROM workshops WHERE id = $workshop_id")->fetch_assoc();
if (!$ws)
    die("Workshop not found");

// Check if user is already in a team for this workshop
// note: using subquery for simpler checking
$my_team_sql = "
    SELECT t.*, tm.joined_at 
    FROM teams t 
    JOIN team_members tm ON t.id = tm.team_id 
    WHERE t.workshop_id = $workshop_id AND tm.user_id = $user_id
";
$my_team = $conn->query($my_team_sql)->fetch_assoc();

// Handle Create Team
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_team'])) {
    $team_name = trim($_POST['team_name']);

    // Allow if participant not in team OR if trainer (can create multiple)
    if (!empty($team_name) && (!$my_team || $_SESSION['role'] == 'trainer')) {
        // 1. Create Team
        $stmt = $conn->prepare("INSERT INTO teams (workshop_id, name, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $workshop_id, $team_name, $user_id);
        if ($stmt->execute()) {
            $new_team_id = $stmt->insert_id;

            // 2. Add creator as member ONLY IF PARTICIPANT
            if ($_SESSION['role'] == 'participant') {
                $stmt2 = $conn->prepare("INSERT INTO team_members (team_id, user_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $new_team_id, $user_id);
                $stmt2->execute();
            }

            header("Location: classroom_teams.php?id=$workshop_id");
            exit();
        }
    }
}

// Handle Join Team
if (isset($_GET['join_team']) && !$my_team && $_SESSION['role'] == 'participant') {
    $team_to_join = intval($_GET['join_team']);
    $stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $team_to_join, $user_id);
    if ($stmt->execute()) {
        header("Location: classroom_teams.php?id=$workshop_id");
        exit();
    }
}

// Handle Delete Team (Trainer Only)
if (isset($_GET['delete_team']) && ($_SESSION['role'] == 'trainer' || $_SESSION['role'] == 'admin')) {
    $tid = intval($_GET['delete_team']);
    $conn->query("DELETE FROM teams WHERE id = $tid"); // Cascade deletes members
    header("Location: classroom_teams.php?id=$workshop_id");
    exit();
}

// Handle Leave Team
if (isset($_GET['leave_team']) && $my_team) {
    $tid = $my_team['id'];
    $conn->query("DELETE FROM team_members WHERE team_id = $tid AND user_id = $user_id");
    header("Location: classroom_teams.php?id=$workshop_id");
    exit();
}

// Get all teams in this workshop
$all_teams_sql = "
    SELECT t.*, u.username as creator_name, 
    (SELECT COUNT(*) FROM team_members WHERE team_id = t.id) as member_count,
    (SELECT GROUP_CONCAT(users.username SEPARATOR ', ') 
     FROM team_members 
     JOIN users ON team_members.user_id = users.user_id 
     WHERE team_members.team_id = t.id) as member_names
    FROM teams t
    JOIN users u ON t.created_by = u.user_id
    WHERE t.workshop_id = $workshop_id
    ORDER BY t.created_at DESC
";
$all_teams = $conn->query($all_teams_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Teams - <?= htmlspecialchars($ws['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        /* Page Title */
        h1 {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
            margin: 0;
            font-size: 2.5rem;
        }

        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .team-card {
            background: #ffffff !important;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border-top: 5px solid #3498db;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            z-index: 10;
        }

        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .my-team-card {
            border-top-color: #2ecc71;
            background: #f0fdf4 !important;
        }

        .team-name {
            font-size: 1.4rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .team-meta {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* Buttons */
        .btn-join {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            font-weight: 600;
            width: 100%;
            text-align: center;
            transition: opacity 0.2s;
        }

        .btn-join:hover {
            opacity: 0.9;
        }

        .btn-leave {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
        }

        /* Create Team Section */
        .create-box {
            background: #ffffff !important;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 10;
        }

        .create-box h3 {
            margin-top: 0;
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .create-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .input-group {
            flex: 1;
            min-width: 250px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1.1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .input-group input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .btn-create {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
            white-space: nowrap;
            box-shadow: 0 4px 6px rgba(46, 204, 113, 0.3);
            flex-shrink: 0;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(46, 204, 113, 0.4);
        }
    </style>
</head>

<body>
    <?php include 'include/nav.php'; ?>

    <div class="container">
        <div class="header-box">
            <h1>👥 Teams: <?= htmlspecialchars($ws['title']) ?></h1>
            <a href="index.php" class="btn" style="background:#555; width: auto; padding: 10px 20px;">&larr; Back to
                Home</a>
        </div>

        <!-- My Team Status -->
        <?php if ($my_team): ?>
            <div class="team-card my-team-card" style="margin-bottom: 30px;">
                <h2 style="margin-top:0;">✅ You are in team: <?= htmlspecialchars($my_team['name']) ?></h2>
                <p>Joined on: <?= date('M j, Y', strtotime($my_team['joined_at'])) ?></p>
                <a href="?id=<?= $workshop_id ?>&leave_team=1" class="btn-leave"
                    onclick="return confirm('Leave this team?');">Leave Team</a>
            </div>
        <?php elseif ($_SESSION['role'] == 'trainer' || $_SESSION['role'] == 'admin'): ?>
            <div class="create-box">
                <h3>✨ Create a New Team</h3>
                <form method="POST" class="create-form">
                    <div class="input-group">
                        <input type="text" name="team_name" placeholder="Enter a cool team name... (e.g. The Avengers)"
                            required>
                    </div>
                    <button type="submit" name="create_team" class="btn-create">Create Team</button>
                </form>
            </div>
        <?php endif; ?>

        <hr>

        <!-- All Teams List -->
        <h3 style="color: white; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">All Teams (<?= $all_teams->num_rows ?>)</h3>
        <div class="team-grid">
            <?php while ($t = $all_teams->fetch_assoc()): ?>
                <div class="team-card">
                    <div class="team-name"><?= htmlspecialchars($t['name']) ?></div>
                    <div class="team-meta">
                        Members: <strong><?= $t['member_count'] ?></strong><br>
                        <small style="color:#666; display:block; margin-top:5px; margin-bottom:10px;">
                            (<?= htmlspecialchars($t['member_names'] ?? 'No members') ?>)
                        </small>
                        Created by: <?= htmlspecialchars($t['creator_name']) ?>

                        <?php if ($_SESSION['role'] == 'trainer' || $_SESSION['role'] == 'admin'): ?>
                            | <a href="?id=<?= $workshop_id ?>&delete_team=<?= $t['id'] ?>" style="color:red;"
                                onclick="return confirm('Delete this team?');">Delete Team</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($_SESSION['role'] == 'participant'): ?>
                        <?php if (!$my_team): ?>
                            <a href="?id=<?= $workshop_id ?>&join_team=<?= $t['id'] ?>" class="btn-join">Join Team</a>
                        <?php elseif ($my_team['id'] == $t['id']): ?>
                            <span style="color:#2ecc71; font-weight:bold; display:block; margin-bottom:10px;">✅ Your Team</span>
                            <a href="submit_assignment.php?id=<?= $workshop_id ?>" class="btn"
                                style="background: #8e44ad; width:100%; box-sizing:border-box;">📤 Submit Project</a>
                        <?php else: ?>
                            <span style="color:#bdc3c7;">(Already in a team)</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

    </div>

    <?php include 'include/footer.php'; ?>
</body>

</html>