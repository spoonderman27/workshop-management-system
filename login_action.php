<?php
session_start();
include("include/db_connect.php");

if (isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    //query database for user email
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        //password verify
        if (password_verify($password, $user['password'])) {
            //store user info
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            //redirect
            if ($user['role'] == 'organizer') {
                header("Location: dashboard_organizer.php");
            } else if ($user['role'] == 'trainer') {
                header("Location: dashboard_trainer.php");
            } else {
                header("Location: index.php");
            }
        } else {
            $_SESSION['error'] = "Invalid password.";
            header("Location: login.php");
        }
    } else {
        $_SESSION['error'] = "Email not found.";
        header("Location: login.php");
    }
}
?>