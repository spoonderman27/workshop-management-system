<?php
// classroom_discussion.php
session_start();
include 'include/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$is_moderator = ($role == 'organizer' || $role == 'trainer'); // Admin excluded as requested

// Fetch workshop info
$ws = $conn->query("SELECT title FROM workshops WHERE id = $workshop_id")->fetch_assoc();
if (!$ws)
    die("Workshop not found.");

// Ensure discussion_mutes table exists
$conn->query("CREATE TABLE IF NOT EXISTS discussion_mutes (
    id int(11) NOT NULL AUTO_INCREMENT,
    workshop_id int(11) NOT NULL,
    user_id int(11) NOT NULL,
    muted_by int(11) NOT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    UNIQUE KEY unique_mute (workshop_id, user_id)
)");

// 1. MUTE CHECK: Check if current user is muted
$is_muted = false;
$muted_list = [];

// Fetch ALL muted users for this workshop
$mute_res = $conn->query("SELECT user_id FROM discussion_mutes WHERE workshop_id = '$workshop_id'");
while ($m = $mute_res->fetch_assoc()) {
    $muted_list[] = $m['user_id'];
}

// Check if current user is in the list
if (in_array($user_id, $muted_list)) {
    $is_muted = true;
}

// 2. HANDLE ACTIONS (Mute User / Delete Message / Post Message)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. MUTE USER (Trainers/Organizers Only)
    if (isset($_POST['mute_user_id']) && $is_moderator) {
        $target_id = intval($_POST['mute_user_id']);
        // Check if already muted
        $check = $conn->query("SELECT id FROM discussion_mutes WHERE workshop_id = '$workshop_id' AND user_id = '$target_id'");
        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO discussion_mutes (workshop_id, user_id, muted_by) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $workshop_id, $target_id, $user_id);
            $stmt->execute();
        } else {
            // Unmute if already muted (Toggle)
            $conn->query("DELETE FROM discussion_mutes WHERE workshop_id = '$workshop_id' AND user_id = '$target_id'");
        }
        header("Location: classroom_discussion.php?id=$workshop_id");
        exit();
    }

    // B. DELETE MESSAGE
    if (isset($_POST['delete_msg_id'])) {
        $msg_id = intval($_POST['delete_msg_id']);

        // Fetch message details to check permissions
        $check_sql = "SELECT d.user_id, u.role FROM discussions d JOIN users u ON d.user_id = u.user_id WHERE d.id = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("i", $msg_id);
        $stmt_check->execute();
        $msg_data = $stmt_check->get_result()->fetch_assoc();

        if ($msg_data) {
            $msg_owner_id = $msg_data['user_id'];
            $msg_sender_role = $msg_data['role'];

            $can_delete = false;
            if ($msg_owner_id == $user_id) {
                $can_delete = true; // Delete own message
            } elseif ($is_moderator && $msg_sender_role == 'participant') {
                $can_delete = true; // Moderator deleting participant message
            }

            if ($can_delete) {
                $stmt = $conn->prepare("DELETE FROM discussions WHERE id = ?");
                $stmt->bind_param("i", $msg_id);
                $stmt->execute();
            }
        }

        header("Location: classroom_discussion.php?id=$workshop_id");
        exit();
    }

    // C. POST MESSAGE (If not muted)
    if (isset($_POST['message']) && !$is_muted) {
        $msg = trim($_POST['message']);
        if (!empty($msg)) {
            $stmt = $conn->prepare("INSERT INTO discussions (workshop_id, user_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $workshop_id, $user_id, $msg);
            $stmt->execute();
            header("Location: classroom_discussion.php?id=$workshop_id");
            exit();
        }
    }
}

// Fetch messages
$sql = "SELECT d.*, u.username, u.role, u.user_id as sender_id FROM discussions d 
        JOIN users u ON d.user_id = u.user_id 
        WHERE workshop_id = ? 
        ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $workshop_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Discussion - <?= htmlspecialchars($ws['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-container {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 80vh;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .chat-header {
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
            gap: 20px;
        }

        .chat-header h2 {
            margin: 0;
            font-size: 1.2rem;
            color: white;
            text-shadow: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        .btn-exit {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }

        .btn-exit:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-messages {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            background: #f4f7f6;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message-bubble {
            max-width: 75%;
            padding: 12px 18px;
            border-radius: 18px;
            position: relative;
            font-size: 1rem;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            /* Ensure children (buttons) are positioned relative to this */
            display: flex;
            flex-direction: column;
        }

        .my-message {
            align-self: flex-end;
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .other-message {
            align-self: flex-start;
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            border: 1px solid #e0e0e0;
        }

        .sender-name {
            font-size: 0.8rem;
            margin-bottom: 4px;
            font-weight: 600;
            display: block;
            opacity: 0.8;
        }

        .my-message .sender-name {
            color: rgba(255, 255, 255, 0.9);
            text-align: right;
        }

        .other-message .sender-name {
            color: #6c5ce7;
        }

        .chat-input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .chat-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            outline: none;
            font-size: 1rem;
            transition: border-color 0.3s;
            min-width: 0;
        }

        .chat-input:focus {
            border-color: #6c5ce7;
        }

        .send-btn {
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 10px rgba(108, 92, 231, 0.3);
            width: auto;
            flex-shrink: 0;
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(108, 92, 231, 0.4);
            background: linear-gradient(135deg, #a855f7, #6c5ce7);
        }

        /* Action Buttons (Mute/Delete) */
        .msg-actions {
            margin-top: 5px;
            font-size: 0.8rem;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            opacity: 0.7;
        }

        .msg-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: inherit;
            font-weight: bold;
            text-decoration: underline;
        }

        .msg-btn:hover {
            opacity: 1;
        }

        .btn-mute {
            color: #e74c3c;
        }

        /* Red for mute */

        .muted-banner {
            background: #fab1a0;
            color: #d63031;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            border-top: 1px solid #e17055;
        }
    </style>
</head>

<body>
    <?php include 'include/nav.php'; ?>

    <div class="chat-container">
        <div class="chat-header">
            <h2>💬 Discussion: <?= htmlspecialchars($ws['title']) ?></h2>
            <a href="index.php" class="btn-exit">⬅ Return</a>
        </div>

        <div class="chat-messages" id="chatBox">
            <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $is_me = ($row['user_id'] == $user_id);
                        $msg_sender_role = $row['role'];

                        // Determine delete permission
                        $can_delete = false;
                        if ($is_me) {
                            $can_delete = true;
                        } elseif ($is_moderator && $msg_sender_role == 'participant') {
                            $can_delete = true;
                        }
                        ?>
                            <div class="message-bubble <?= $is_me ? 'my-message' : 'other-message' ?>">
                                <span class="sender-name">
                                    <?= htmlspecialchars($row['username']) ?>
                                    <?php
                                    if ($row['role'] == 'trainer')
                                        echo ' (Trainer)';
                                    elseif ($row['role'] == 'organizer')
                                        echo ' (Organizer)';
                                    ?>
                                </span>

                                <?= nl2br(htmlspecialchars($row['message'])) ?>

                                <!-- Actions: Delete / Mute -->
                                <div class="msg-actions">
                                    <!-- Delete Button -->
                                    <?php if ($can_delete): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?');">
                                                <input type="hidden" name="delete_msg_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="msg-btn">🗑️ Delete</button>
                                            </form>
                                    <?php endif; ?>

                                    <!-- Mute Button: Moderators can mute participants -->
                                    <?php if ($is_moderator && !$is_me && $msg_sender_role == 'participant'):
                                        $is_user_muted = in_array($row['sender_id'], $muted_list);
                                        $confirm_msg = $is_user_muted ? "Unmute this user?" : "Mute this user?";
                                        ?>
                                            <form method="POST" style="display:inline;"
                                                onsubmit="return confirm('<?= $confirm_msg ?>');">
                                                <input type="hidden" name="mute_user_id" value="<?= $row['sender_id'] ?>">
                                                <?php if ($is_user_muted): ?>
                                                        <button type="submit" class="msg-btn btn-mute">🔊 Unmute</button>
                                                <?php else: ?>
                                                        <button type="submit" class="msg-btn btn-mute">🔇 Mute</button>
                                                <?php endif; ?>
                                            </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php endwhile; ?>
            <?php else: ?>
                    <p style="text-align: center; color: #aaa;">No messages yet. Start the conversation!</p>
            <?php endif; ?>
        </div>

        <?php if ($is_muted): ?>
                <div class="muted-banner">
                    🚫 You have been temporarily muted by a moderator.
                </div>
        <?php else: ?>
                <form class="chat-input-area" method="POST">
                    <input type="text" name="message" class="chat-input" placeholder="Type a message..." required
                        autocomplete="off">
                    <button type="submit" class="send-btn">Send ➤</button>
                </form>
        <?php endif; ?>
    </div>

    <script>
        // Scroll to bottom
        var chatBox = document.getElementById("chatBox");
        chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>

</html>