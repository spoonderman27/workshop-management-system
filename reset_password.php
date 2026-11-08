<?php
session_start();
include("include/db_connect.php");

$token = $_GET['token'] ?? null;
$user = null;

if ($token) {
    $token_hash = hash("sha256", $token);

    // We compare against PHP time to avoid DB timezone mismatches
    // Since we set expiry using PHP time in forgot_password_action.php
    $current_time = date("Y-m-d H:i:s");
    $sql = "SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $token_hash, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="form-card">

            <h2>Reset Password</h2>

            <?php if (!$user): ?>
                <div class="error-msg">
                    Invalid or expired token. <a href="forgot_password.php">Request a new one</a>.
                </div>
            <?php else: ?>

                <form action="reset_password_action.php" method="POST">

                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div>
                        <label>New Password</label>
                        <input type="password" name="password" required placeholder="Enter new password" minlength="6">
                    </div>

                    <div>
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" required placeholder="Confirm new password"
                            minlength="6">
                    </div>

                    <button type="submit" name="reset_btn">Reset Password</button>

                </form>

            <?php endif; ?>

        </div>
    </main>
    <?php include 'include/footer.php'; ?>

</body>

</html>