<?php
session_start();
include("include/db_connect.php");

if (isset($_POST['forgot_btn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

        $sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $token_hash, $expiry, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $reset_link = "http://localhost/c8/reset_password.php?token=" . $token;
            $_SESSION['success'] = "Password reset link sent to email.<br><strong>Link: <a href='$reset_link'>Reset Password</a></strong>";
            header("Location: forgot_password.php");
        } else {
            $_SESSION['error'] = "Something went wrong. Please try again.";
            header("Location: forgot_password.php");
        }
    } else {
        $_SESSION['error'] = "Email address not found.";
        header("Location: forgot_password.php");
    }
}
?>