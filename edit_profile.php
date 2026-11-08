<?php
session_start();
include("include/db_connect.php");

//security check, make sure user login before accessing page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//fetch user data
$result = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'");
$user = $result->fetch_assoc();

//profile pic url
if (!empty($user['avatar'])) {
    $img_src = "uploads/" . $user['avatar'];
} else {
    $img_src = "https://ui-avatars.com/api/?name=" . $user['username'] . "&background=2563eb&color=fff";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php include("include/nav.php"); ?>
    
    <main>
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            
            <h2>Edit Profile</h2>

            <form action="profile_action.php" method="POST" enctype="multipart/form-data">
                
                <div style="text-align:center; margin-bottom:20px;">
                    <img src="<?= $img_src; ?>" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border: 3px solid #e2e8f0;">
                </div>

                <div>
                    <label>Change Profile Picture</label>
                    <input type="file" name="avatar" accept="image/*">
                </div>

                <div>
                    <label>Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                </div>

                <div>
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" required>
                </div>

                <div>
                    <label>Country</label>
                    <input type="text" name="country" value="<?= htmlspecialchars($user['country']); ?>" required>
                </div>
                
                <br>
                <button type="submit" name="update_profile" class="btn">Save Changes</button>
                <a href="profile.php" class="btn btn-secondary">Cancel</a>

            </form>
        </div>
    </main>
	<?php include 'include/footer.php'; ?>

</body>
</html>