<?php
session_start();
include("include/db_connect.php");

if (isset($_POST['reset_btn'])) {

    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    if ($password !== $password_confirmation) {
        die("Passwords do not match.");
    }

    $token_hash = hash("sha256", $token);
    $current_time = date("Y-m-d H:i:s");

    $sql = "SELECT * FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $token_hash, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $user['user_id']);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Password reset successful. Please login.";
            header("Location: login.php");
        } else {
            $_SESSION['error'] = "Failed to update password.";
            header("Location: reset_password.php?token=$token");
        }

    } else {
        $_SESSION['error'] = "Token invalid or expired.";
        header("Location: forgot_password.php");
    }
}
?>