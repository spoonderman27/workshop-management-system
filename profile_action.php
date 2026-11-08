<?php
session_start();
include("include/db_connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['update_profile'])) {
    
    $user_id  = $_SESSION['user_id'];
    $username = htmlspecialchars($_POST['username']);
    $phone    = htmlspecialchars($_POST['phone']);
    $country  = htmlspecialchars($_POST['country']);

    //image upload
    $avatar_sql_part = ""; 
    
    //check if file uploaded
    if (!empty($_FILES['avatar']['name'])) {
        $file_name = $_FILES['avatar']['name'];
        $file_tmp  = $_FILES['avatar']['tmp_name'];
        $file_type = $_FILES['avatar']['type'];
        
        //ensure it is an image
        if ($file_type == "image/jpeg" || $file_type == "image/png" || $file_type == "image/jpg") {
            //unique name to prevent overwrite
            $new_filename = "user_" . $user_id . "_" . time() . ".jpg";
            $destination  = "uploads/" . $new_filename;

            if (move_uploaded_file($file_tmp, $destination)) {
                //sql to update avatar
                $avatar_sql_part = ", avatar='$new_filename'";
            } else {
                $_SESSION['error'] = "Failed to upload image.";
                header("Location: edit_profile.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Only JPG and PNG files are allowed.";
            header("Location: edit_profile.php");
            exit();
        }
    }

    //update Database
    //append $avatar_sql_part, no changes if no image uploaded
    $sql = "UPDATE users SET username='$username', phone='$phone', country='$country' $avatar_sql_part WHERE user_id='$user_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['username'] = $username; //update session name 
        $_SESSION['success']  = "Profile updated successfully!";
        header("Location: profile.php");
    } else {
        $_SESSION['error'] = "Error updating profile: " . $conn->error;
        header("Location: edit_profile.php");
    }

} else {
    header("Location: profile.php");
}
?>