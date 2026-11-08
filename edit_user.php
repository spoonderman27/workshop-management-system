<?php
session_start();
include 'include/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id == 0)
    header("Location: manage_users.php");

// Fetch User
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user)
    die("User not found.");

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];

    // Optional Password Update
    $password_sql = "";
    if (!empty($_POST['password'])) {
        $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password = '$hashed'";
    }

    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, role=? $password_sql WHERE user_id=?");
    $stmt->bind_param("ssssi", $username, $email, $phone, $role, $id);

    if ($stmt->execute()) {
        header("Location: manage_users.php?success=updated");
        exit();
    } else {
        $error = "Error updating user.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'include/nav.php'; ?>
    <main>
        <div class="form-card">
            <h2>Edit User</h2>
            <?php if (isset($error))
                echo "<p class='error-message'>$error</p>"; ?>

            <form method="POST">
                <div>
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div>
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                <div>
                    <label>Role</label>
                    <select name="role">
                        <option value="participant" <?= $user['role'] == 'participant' ? 'selected' : '' ?>>Participant
                        </option>
                        <option value="trainer" <?= $user['role'] == 'trainer' ? 'selected' : '' ?>>Trainer</option>
                        <option value="organizer" <?= $user['role'] == 'organizer' ? 'selected' : '' ?>>Organizer</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div>
                    <label>Password (Leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="New Password">
                </div>
                <button type="submit">Update User</button>
                <a href="manage_users.php" class="btn" style="background:#7f8c8d; margin-top:10px;">Cancel</a>
            </form>
        </div>
    </main>
</body>

</html>