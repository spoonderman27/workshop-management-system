<?php
// include/notifications_api.php

// Function to add a notification
function addNotification($conn, $user_id, $message)
{
    if (!$conn)
        return false;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    return $stmt->execute();
}

// Function to get unread notifications count
function getUnreadCount($conn, $user_id)
{
    if (!$conn)
        return 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc()['cnt'];
}

// Function to mark all as read
function markAllAsRead($conn, $user_id)
{
    if (!$conn)
        return false;
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}
?>