<?php
session_start();
include("include/db_connect.php");

//organizer access only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];

//create workshop
if (isset($_POST['create_btn'])) {

    $title = htmlspecialchars($_POST['title']);
    $desc = htmlspecialchars($_POST['description']);
    $trainer_id = htmlspecialchars($_POST['trainer']);

    //trainer name
    $t_res = $conn->query("SELECT username FROM users WHERE user_id = '$trainer_id'");
    $t_row = $t_res->fetch_assoc();
    $trainer_name = $t_row['username'];

    $date = $_POST['date'];
    $time = $_POST['time'];
    $loc = htmlspecialchars($_POST['location']);
    $fee = $_POST['fee'];

    //date validation
    if ($date < date('Y-m-d')) {
        $_SESSION['error'] = "(cannot select a past date";
        header("Location: create_workshop.php");
        exit();
    }

    //trainer
    $sql = "INSERT INTO workshops 
	(title, description, trainer, trainer_id, date, time, location, fee, organizer_id) VALUES 
	('$title', '$desc', '$trainer_name', '$trainer_id', '$date', '$time', '$loc', '$fee', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Workshop created successfully!";

        // Broadcast Notification: New Workshop
        include_once 'include/notifications_api.php';
        $participants = $conn->query("SELECT user_id FROM users WHERE role = 'participant'");
        while ($p = $participants->fetch_assoc()) {
            addNotification($conn, $p['user_id'], "📢 New Workshop Alert: $title is now open for registration!");
        }

    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }

    header("Location: dashboard_organizer.php");
    exit();
}


//update workshop
if (isset($_POST['update_btn'])) {

    $id = $_POST['id'];
    $title = htmlspecialchars($_POST['title']);
    $desc = htmlspecialchars($_POST['description']);
    $trainer_id = htmlspecialchars($_POST['trainer']); // ID

    //fetch trainer name
    $t_res = $conn->query("SELECT username FROM users WHERE user_id = '$trainer_id'");
    $t_row = $t_res->fetch_assoc();
    $trainer_name = $t_row['username'];

    $date = $_POST['date'];
    $time = $_POST['time'];
    $loc = htmlspecialchars($_POST['location']);
    $fee = $_POST['fee'];

    //date validation
    if ($date < date('Y-m-d')) {
        $_SESSION['error'] = "cannot select a past date";
        header("Location: edit_workshop.php?id=$id");
        exit();
    }

    //update trainer
    $sql = "UPDATE workshops SET title='$title', description='$desc', trainer='$trainer_name', trainer_id='$trainer_id', date='$date', time='$time', location='$loc', fee = '$fee' 
            WHERE id='$id' AND organizer_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Workshop updated successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }

    header("Location: dashboard_organizer.php");
    exit();
}

//delete workshop
if (isset($_GET['delete_id'])) {

    $id = $_GET['delete_id'];
    $sql = "DELETE FROM workshops WHERE id='$id' AND organizer_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Workshop deleted successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }

    header("Location: dashboard_organizer.php");
    exit();
}
?>