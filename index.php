<?php
session_start();
include("include/db_connect.php");

// Fetch workshops with current booking count
$sql = "SELECT workshops.*, users.username, 
        (SELECT COUNT(*) 
         FROM bookings 
         WHERE workshop_id = workshops.id 
           AND LOWER(payment_status) IN ('verified','approved','confirmed') 
           AND booking_status != 'cancelled'
        ) as registered_count
        FROM workshops 
        JOIN users ON workshops.organizer_id = users.user_id 
        ORDER BY date ASC";
$result = $conn->query($sql);

//fetch user enrolled workshops
$enrolled_ids = [];
if (isset($_SESSION['user_id'])) {
    $user_id = (int) $_SESSION['user_id']; // force integer
    $booking_query = $conn->prepare("SELECT workshop_id FROM bookings WHERE user_id=?");
    $booking_query->bind_param("i", $user_id);
    $booking_query->execute();
    $res = $booking_query->get_result();
    while ($b = $res->fetch_assoc()) {
        $enrolled_ids[] = $b['workshop_id'];
    }
}

//layout logic (count)
$workshop_count = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - SmartWorkshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <header
        style="width: 100%; background: url('img/banner1.jpg') no-repeat center center/cover; color: white; padding: 30px 0; text-align: center; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h1 style="font-size: 2.5rem; margin-bottom: 5px; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
            Welcome to Smart Workshops</h1>
        <p style="font-size: 1.2rem; font-weight: bold; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Master
            digital skills like Canva, ChatGPT, Capcut and OTHERS.</p>
    </header>

    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?= $_SESSION['success'];
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <h2 style="color: white; text-align: center;">Upcoming Workshops</h2>

        <div style="display: grid; gap: 20px; 
            <?php
            if ($workshop_count == 1) {
                echo 'grid-template-columns: 1fr; max-width: 500px; margin: 0 auto;';
            } else {
                echo 'grid-template-columns: 1fr 1fr;';
            }
            ?>">

            <?php if ($workshop_count > 0): ?>

                <?php while ($row = $result->fetch_assoc()):
                    $available_slots = 50 - $row['registered_count']; // Default max 50 if not set
                    $is_full = $available_slots <= 0;
                    ?>
                    <div class="card" style="display: flex; flex-direction: column; height: 100%;">

                        <h3><?= htmlspecialchars($row['title']); ?></h3>

                        <div style="margin-bottom: 10px; color: #555;">
                            <strong>Date:</strong> <?= $row['date']; ?> <br>
                            <strong>Time:</strong> <?= $row['time']; ?>
                        </div>

                        <p style="margin-bottom: 15px; flex-grow: 1;">
                            <?= htmlspecialchars($row['description']); ?>
                        </p>

                        <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">

                        <p style="margin-bottom: 15px;">
                            <strong>Location:</strong> <?= htmlspecialchars($row['location']); ?> <br>
                            <strong>Trainer:</strong> <?= htmlspecialchars($row['trainer']); ?> <br>
                            <strong>Fee:</strong> RM <?= number_format($row['fee'], 2); ?> <br>
                            <strong>Available Slots:</strong>
                            <span style="color: <?= $available_slots < 10 ? '#f39c12' : '#27ae60' ?>; font-weight: bold;">
                                <?= max(0, $available_slots); ?>
                            </span>
                        </p>

                        <?php if (isset($_SESSION['user_id'])): ?>

                            <?php if ($_SESSION['role'] == 'participant'): ?>
                                <?php if (in_array($row['id'], $enrolled_ids)): ?>
                                    <button class="btn" style="background-color: #27ae60; cursor: default;" disabled>✓ Enrolled</button>
                                <?php elseif ($is_full): ?>
                                    <button class="btn" style="background-color: #e74c3c; cursor: not-allowed;" disabled>Workshop
                                        Full</button>
                                <?php else: ?>
                                    <a href="register_workshop.php?id=<?= $row['id']; ?>" class="btn">Register Now</a>
                                <?php endif; ?>

                                <!-- Added from Module C -->
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                                    <a href="view_materials.php?id=<?= $row['id']; ?>" class="btn"
                                        style="background-color: #f39c12; font-size: 0.9rem;">View Materials</a>
                                    <a href="submit_assignment.php?id=<?= $row['id']; ?>" class="btn"
                                        style="background-color: #8e44ad; font-size: 0.9rem;">Submit Project</a>
                                </div>

                                <a href="classroom_discussion.php?id=<?= $row['id']; ?>" class="btn"
                                    style="background-color: #3498db; margin-top: 10px; width: 100%;">💬 Join Discussion</a>
                                <a href="classroom_teams.php?id=<?= $row['id']; ?>" class="btn"
                                    style="background-color: #16a085; margin-top: 5px; width: 100%;">👥 Join Team</a>

                            <?php elseif ($_SESSION['role'] == 'organizer'): ?>
                                <?php if ($row['organizer_id'] == $_SESSION['user_id']): ?>
                                    <a href="edit_workshop.php?id=<?= $row['id']; ?>" class="btn" style="background-color: #2c3e50;">Manage
                                        My Event</a>
                                <?php else: ?>
                                    <a href="#" class="btn"
                                        style="background-color: #95a5a6; cursor: not-allowed; pointer-events: none;">Organizer View</a>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php else: ?>
                            <a href="register.php" class="btn">Register to Join</a>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <p style="text-align:center; color: white;">There are currently no workshops scheduled.</p>
            <?php endif; ?>

        </div>
    </main>
    <?php include 'include/footer.php'; ?>
</body>

</html>