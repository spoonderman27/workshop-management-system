<?php
session_start();
include 'include/db_connect.php';

// Only Admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Prevent self-deletion
    if ($delete_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $success_msg = "User deleted successfully.";
        }
    } else {
        $error_msg = "You cannot delete your own account.";
    }
}

// Filter Logic
$filter_role = isset($_GET['role']) ? $_GET['role'] : 'all';
$valid_roles = ['admin', 'organizer', 'trainer', 'participant'];

$sql = "SELECT * FROM users";
if ($filter_role !== 'all' && in_array($filter_role, $valid_roles)) {
    $sql .= " WHERE role = '" . $conn->real_escape_string($filter_role) . "'";
}
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filter-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            color: #555;
            background: #eee;
            font-weight: 500;
            transition: all 0.3s;
        }
        .filter-btn:hover, .filter-btn.active {
            background: #3498db;
            color: white;
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            margin-left: 5px;
        }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-edit { background: #f1c40f; color: #333; }
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: bold;
        }
        .role-admin { background: #34495e; color: white; }
        .role-organizer { background: #9b59b6; color: white; }
        .role-trainer { background: #e67e22; color: white; }
        .role-participant { background: #2ecc71; color: white; }
    </style>
</head>
<body>
    <?php include 'include/nav.php'; ?>

    <main>
        <div class="card" style="max-width: 1200px; margin: 0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="color:#2c3e50; text-shadow:none; margin:0;">User Management</h2>
                <a href="register.php" class="btn" style="width:auto; background:#27ae60;">+ Add New User</a>
            </div>

            <?php if (isset($success_msg)): ?>
                <div class="success-message"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if (isset($error_msg)): ?>
                <div class="error-message"><?= $error_msg ?></div>
            <?php endif; ?>

            <div class="filter-bar">
                <a href="manage_users.php?role=all" class="filter-btn <?= $filter_role == 'all' ? 'active' : '' ?>">All Users</a>
                <a href="manage_users.php?role=admin" class="filter-btn <?= $filter_role == 'admin' ? 'active' : '' ?>">Admins</a>
                <a href="manage_users.php?role=participant" class="filter-btn <?= $filter_role == 'participant' ? 'active' : '' ?>">Participants</a>
                <a href="manage_users.php?role=trainer" class="filter-btn <?= $filter_role == 'trainer' ? 'active' : '' ?>">Trainers</a>
                <a href="manage_users.php?role=organizer" class="filter-btn <?= $filter_role == 'organizer' ? 'active' : '' ?>">Organizers</a>
            </div>

            <table class="profile-table">
                <thead>
                    <tr style="background:#f8f9fa;">
                        <th>User</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['username']) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['email']) ?><br>
                                    <small><?= htmlspecialchars($row['phone']) ?></small>
                                </td>
                                <td>
                                    <span class="role-badge role-<?= $row['role'] ?>">
                                        <?= ucfirst($row['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                <td style="text-align:right;">
                                    <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                                        <a href="edit_user.php?id=<?= $row['user_id'] ?>" class="action-btn btn-edit">Edit</a>
                                        <a href="manage_users.php?delete_id=<?= $row['user_id'] ?>" 
                                           class="action-btn btn-delete"
                                           onclick="return confirm('Are you sure you want to delete this user? This cannot be undone.');">Delete</a>
                                    <?php else: ?>
                                        <span style="color:#999; font-size:0.9rem;">(You)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; padding:20px;">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include 'include/footer.php'; ?>
</body>
</html>
