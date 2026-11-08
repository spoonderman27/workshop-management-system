<?php
// notifications.php
session_start();
include 'include/db_connect.php';
include 'include/notifications_api.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark all as read when visiting this page
markAllAsRead($conn, $user_id);

// Handle Delete Notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM notifications WHERE id = $del_id AND user_id = $user_id");
    header("Location: notifications.php");
    exit();
}

// Fetch notifications
$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Notifications - SmartWorkshop</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notify-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
        }

        .notify-list {
            list-style: none;
            padding: 0;
        }

        .notify-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #ddd;
        }

        .notify-item.unread {
            border-left-color: #e74c3c;
            background: #fffcfc;
        }

        .notify-date {
            font-size: 0.85rem;
            color: #888;
            display: block;
            margin-top: 5px;
        }

        .empty-state {
            text-align: center;
            color: #888;
            padding: 40px;
        }
    </style>
</head>

<body>
    <?php include 'include/nav.php'; ?>

    <main>
        <div class="notify-container">
            <h1>🔔 Your Notifications</h1>

            <?php if ($result->num_rows > 0): ?>
                <ul class="notify-list">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li class="notify-item <?= $row['is_read'] ? '' : 'unread' ?>">
                            <div style="display:flex; justify-content:space-between; align-items:start;">
                                <div>
                                    <div class="notify-msg"><?= htmlspecialchars($row['message']) ?></div>
                                    <span class="notify-date"><?= date('M j, g:i A', strtotime($row['created_at'])) ?></span>
                                </div>
                                <form method="POST" onsubmit="return confirm('Delete this notification?');">
                                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                    <button type="submit"
                                        style="background:none; border:none; color:#e74c3c; cursor:pointer; font-size:1.2rem;"
                                        title="Delete">🗑️</button>
                                </form>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    You have no notifications yet.
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'include/footer.php'; ?>
</body>

</html>