<?php
session_start();
include("include/db_connect.php");

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if (isset($_POST['submit_feedback'])) {
    $user_id = $_SESSION['user_id'];
    $workshop_id = $_POST['workshop_id'];
    $rating = $_POST['rating'];
    $comment = htmlspecialchars($_POST['comment']);
    $is_editing = isset($_POST['edit_feedback']) && $_POST['edit_feedback'] == 1;
    
    if ($is_editing) {
        // UPDATE existing feedback
        $stmt = $conn->prepare(
            "UPDATE feedback 
             SET rating = ?, comment = ?, created_at = NOW()
             WHERE user_id = ? AND workshop_id = ?"
        );
        $stmt->bind_param("isii", $rating, $comment, $user_id, $workshop_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Feedback updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update feedback.";
        }
    } else {
        // INSERT new feedback
        $stmt = $conn->prepare(
            "INSERT INTO feedback (user_id, workshop_id, rating, comment)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("iiis", $user_id, $workshop_id, $rating, $comment);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Feedback submitted successfully!";
            
            // Auto-generate certificate after feedback submission
            $cert_code = 'CERT-' . strtoupper(substr(md5($user_id . $workshop_id . time()), 0, 10));
            
            // Check if certificate already exists
            $check_cert = $conn->prepare("SELECT id FROM certificates WHERE user_id = ? AND workshop_id = ?");
            $check_cert->bind_param("ii", $user_id, $workshop_id);
            $check_cert->execute();
            $cert_exists = $check_cert->get_result();
            
            if ($cert_exists->num_rows == 0) {
                // Insert certificate with certificate_code and issue_date
                $cert_stmt = $conn->prepare(
                    "INSERT INTO certificates (user_id, workshop_id, certificate_code, issue_date) 
                     VALUES (?, ?, ?, NOW())"
                );
                $cert_stmt->bind_param("iis", $user_id, $workshop_id, $cert_code);
                $cert_stmt->execute();
            }
        } else {
            $_SESSION['error'] = "You have already submitted feedback.";
        }
    }
    
    header("Location: feedback.php");
    exit();
}
?>