<?php
session_start();
include("include/db_connect.php");

//check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//join workshop
if (isset($_GET['join_id'])) {

    $workshop_id = $_GET['join_id'];

    //check user joined or not
    $check = $conn->query("SELECT id FROM bookings WHERE user_id='$user_id' AND workshop_id='$workshop_id'");

    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, workshop_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $workshop_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "You successfully joined the workshop!";

            // Notification: User joined
            // Notification: User joined
            // Notification Logic
            include_once 'include/notifications_api.php';

            // 1. Get Workshop Details (Title & Organizer ID)
            $ws_query = "SELECT title, organizer_id FROM workshops WHERE id = '$workshop_id'";
            $ws_res = $conn->query($ws_query);

            if ($ws_res && $ws_row = $ws_res->fetch_assoc()) {
                $ws_title = $ws_row['title'];
                $organizer_id = $ws_row['organizer_id'];

                // 2. Notify Participant
                addNotification($conn, $user_id, "You successfully registered for $ws_title.");

                // 3. Notify Organizer
                if (!empty($organizer_id)) {
                    // Get Participant Name
                    $u_res = $conn->query("SELECT username FROM users WHERE user_id = '$user_id'");
                    if ($u_res && $u_row = $u_res->fetch_assoc()) {
                        $u_name = $u_row['username'];
                        $msg_org = "💰 Payment Pending: $u_name has registered for $ws_title. Please verify payment.";
                        addNotification($conn, $organizer_id, $msg_org);
                    }
                }
            }

        } else {
            $_SESSION['error'] = "Something went wrong.";
        }
    } else {
        $_SESSION['error'] = "You have already joined this workshop.";
    }

    //back to homepage
    header("Location: index.php");
    exit();
}

//unenroll workshop
if (isset($_GET['leave_id'])) {

    $workshop_id = $_GET['leave_id'];

    $stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ? AND workshop_id = ?");
    $stmt->bind_param("ii", $user_id, $workshop_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "You have unenrolled from the workshop.";
    } else {
        $_SESSION['error'] = "Error removing booking.";
    }

    //back to profile
    header("Location: profile.php");
    exit();
}
?>