<?php
session_start();
include("include/db_connect.php");

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access Denied. You must log in.");
}

$user_id = $_SESSION['user_id'];
$role = strtolower(trim($_SESSION['role'])); // Add trim() to remove any spaces

// Only allow organizer, trainer, admin
if (!in_array($role, ['organizer', 'trainer', 'admin'])) {
    die("Access Denied. You do not have permission. Your role: " . htmlspecialchars($role));
}

// Set dashboard link based on role using switch for better clarity
switch($role) {
    case 'trainer':
        $dashboard_link = 'dashboard_trainer.php';
        break;
    case 'organizer':
        $dashboard_link = 'dashboard_organizer.php';
        break;
    case 'admin':
        $dashboard_link = 'dashboard_admin.php';
        break;
    default:
        $dashboard_link = 'index.php'; // Fallback
}

// Get workshop ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Workshop ID.");
}
$workshop_id = intval($_GET['id']);

// Verify workshop access
if ($role === 'organizer') {
    $stmt = $conn->prepare("SELECT title FROM workshops WHERE id = ? AND organizer_id = ?");
    $stmt->bind_param("ii", $workshop_id, $user_id);
} elseif ($role === 'trainer') {
    $stmt = $conn->prepare("SELECT title FROM workshops WHERE id = ? AND trainer_id = ?");
    $stmt->bind_param("ii", $workshop_id, $user_id);
} else { // admin
    $stmt = $conn->prepare("SELECT title FROM workshops WHERE id = ?");
    $stmt->bind_param("i", $workshop_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Access Denied: Workshop not found or you don't have access.");
}

$workshop = $result->fetch_assoc();

// Fetch participants (both registered and confirmed)
$sql = "SELECT 
            u.username, 
            u.email, 
            u.phone, 
            b.created_at,
            b.payment_status,
            b.booking_status
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id 
        WHERE b.workshop_id = ? 
        AND b.booking_status IN ('registered', 'confirmed')
        ORDER BY b.created_at DESC";

$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $workshop_id);
$stmt2->execute();
$participants = $stmt2->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Participants - SmartWorkshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include("include/nav.php"); ?>

<main>
    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h2 style="margin-bottom:5px; text-align:left;">Participant List</h2>
                <p style="color:#64748b;">For: <strong><?= htmlspecialchars($workshop['title']); ?></strong></p>
            </div>
            <a href="<?= $dashboard_link ?>" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <?php if ($participants->num_rows > 0): ?>
            <div style="background: #f8f9fa; padding: 10px 15px; border-radius: 6px; margin-bottom: 15px;">
                <span style="font-weight: 600; color: #2c3e50;">Total Participants: <?= $participants->num_rows; ?></span>
            </div>

            <table class="participant-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Date Joined</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $count = 1; while ($p = $participants->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:600;"><?= $count++; ?></td>
                        <td style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($p['username']); ?></td>
                        <td><?= htmlspecialchars($p['email']); ?></td>
                        <td><?= htmlspecialchars($p['phone']); ?></td>
                        <td style="color:#6c757d; font-size:0.9em;"><?= date('M j, Y g:i A', strtotime($p['created_at'])); ?></td>
                        <td>
                            <span class="status-badge status-<?= $p['payment_status']; ?>">
                                <?= ucfirst($p['payment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $p['booking_status']; ?>">
                                <?= ucfirst($p['booking_status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div style="font-size:48px; margin-bottom:15px;">📋</div>
                <h3>No Participants Yet</h3>
                <p>No one has registered for this workshop yet.</p>
                <a href="<?= $dashboard_link ?>" class="btn" style="background:#2c3e50; color:white;">Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'include/footer.php'; ?>
</body>
</html>