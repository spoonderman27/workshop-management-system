<?php
session_start();
include("include/db_connect.php");

if (isset($_POST['register_btn'])) {

    //get input
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    //simple validation
    if (empty($username) || empty($email) || empty($password) || empty($country) || empty($phone)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }

    //role check
    $allowed_roles = ['participant', 'organizer', 'trainer'];
    if (!in_array($role, $allowed_roles)) {
        $_SESSION['error'] = "Invalid role selected.";
        header("Location: register.php");
        exit();
    }

    //password length minimum
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        header("Location: register.php");
        exit();
    }

    //phone validation
    if (!preg_match('/^[0-9]+$/', $phone)) {
        $_SESSION['error'] = "Invalid Phone Number. Please use numbers only (no spaces or symbols).";
        header("Location: register.php");
        exit();
    }

    //check existing email
    //change "SELECT id" to "SELECT user_id"
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email is already registered!";
        $stmt->close();
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    //encrypt password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    //insert to database
    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, country, password, role) VALUES (?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("ssssss", $username, $email, $phone, $country, $hashed_password, $role);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please login.";

            // Notification: Notify ALL Admins
            include_once 'include/notifications_api.php';
            $admin_query = "SELECT user_id FROM users WHERE role = 'admin'";
            $admin_res = $conn->query($admin_query);
            if ($admin_res) {
                $notif_msg = "👤 New User Registration: $username has registered as " . ucfirst($role) . ".";
                while ($admin_row = $admin_res->fetch_assoc()) {
                    addNotification($conn, $admin_row['user_id'], $notif_msg);
                }
            }

            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error'] = "Database Error: " . $stmt->error;
            header("Location: register.php");
            exit();
        }

    } else {
        $_SESSION['error'] = "Failed to prepare statement: " . $conn->error;
        header("Location: register.php");
        exit();
    }

}

header("Location: register.php");
exit();
?>