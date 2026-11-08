<?php
session_start();
include("include/db_connect.php");

//security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

//fetch existing data
$result = $conn->query("SELECT * FROM workshops WHERE id='$id' AND organizer_id='$user_id'");

if ($result->num_rows == 0) {
    die("Workshop not found or you don't have permission to edit it.");
}

$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Workshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="card" style="max-width: 600px; margin: 0 auto;">

            <h2>Edit Workshop</h2>

            <form action="workshop_action.php" method="POST">

                <input type="hidden" name="id" value="<?= $row['id']; ?>">

                <div>
                    <label>Workshop Topic / Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($row['title']); ?>" required>
                </div>

                <div>
                    <label>Description</label>
                    <textarea name="description" rows="5"
                        required><?= htmlspecialchars($row['description']); ?></textarea>
                </div>

                <?php
                //fetch trainers
                $trainers = $conn->query("SELECT user_id, username FROM users WHERE role = 'trainer'");
                ?>
                <div>
                    <label>Trainer Name</label>
                    <select name="trainer" required>
                        <option value="">Select a Trainer</option>
                        <?php while ($t = $trainers->fetch_assoc()): ?>
                            <?php $selected = ($t['user_id'] == $row['trainer_id']) ? 'selected' : ''; ?>
                            <option value="<?= $t['user_id'] ?>" <?= $selected ?>><?= htmlspecialchars($t['username']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="display:flex; gap: 20px;">
                    <div style="flex:1;">
                        <label>Date</label>
                        <input type="date" name="date" value="<?= $row['date']; ?>" required
                            min="<?= date('Y-m-d'); ?>">
                    </div>
                    <div style="flex:1;">
                        <label>Time</label>
                        <input type="time" name="time" value="<?= $row['time']; ?>" required>
                    </div>
                </div>

                <div>
                    <label>Location</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($row['location']); ?>" required>
                </div>

				<div>
					<label>Workshop Fee (RM)</label>
					<input type="number" name="fee" step="0.01" min="0"
						   value="<?= $row['fee']; ?>" required>
				</div>
                <br>
                <button type="submit" name="update_btn" style="background-color: #f59e0b;">Update Workshop</button>
                <a href="dashboard_organizer.php" class="btn btn-secondary">Cancel</a>

            </form>
        </div>
    </main>
	<?php include 'include/footer.php'; ?>

</body>

</html>