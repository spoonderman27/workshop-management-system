<?php
session_start();
include("include/db_connect.php");

//kick if not organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Workshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<?php
//fetch trainers
$trainers = $conn->query("SELECT user_id, username FROM users WHERE role = 'trainer'");
?>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="card" style="max-width: 600px; margin: 0 auto;">

            <h2>Create New Workshop</h2>

            <form action="workshop_action.php" method="POST">

                <div>
                    <label>Workshop Topic / Title</label>
                    <input type="text" name="title" required placeholder="eg Python introduction">
                </div>

                <div>
                    <label>Description</label>
                    <textarea name="description" rows="5" required placeholder="What will be covered?"></textarea>
                </div>

                <div>
                    <label>Trainer Name</label>
                    <select name="trainer" required>
                        <option value="">Select a Trainer</option>
                        <?php while ($t = $trainers->fetch_assoc()): ?>
                            <option value="<?= $t['user_id'] ?>"><?= htmlspecialchars($t['username']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="display:flex; gap: 20px;">
                    <div style="flex:1;">
                        <label>Date</label>
                        <input type="date" name="date" required min="<?= date('Y-m-d'); ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Time</label>
                        <input type="time" name="time" required>
                    </div>
                </div>

                <div>
                    <label>Location</label>
                    <input type="text" name="location" required placeholder="eg Google meet">
                </div>

				<div>
					<label>Workshop Fee (RM)</label>
					<input type="number" name="fee" step="0.01" min="0" required placeholder="eg 50.00">
				</div>
                <br>
                <button type="submit" name="create_btn">Create Workshop</button>
                <a href="dashboard_organizer.php" class="btn btn-secondary">Cancel</a>

            </form>
        </div>
    </main>
	<?php include 'include/footer.php'; ?>

</body>

</html>