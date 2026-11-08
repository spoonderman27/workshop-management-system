<?php
include 'include/db_connect.php';
session_start();

// Security: Only Trainers can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'trainer') {
    header("Location: index.php");
    exit();
}

$trainer_id = $_SESSION['user_id'];

// --- START: ORIGINAL UPLOAD LOGIC ---
if (isset($_POST['submit'])) {
    $workshop_id = $_POST['workshop_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $trainer_id = $_SESSION['user_id'];

    // Handle File Upload logic
    $file_name = $_FILES['material_file']['name'];
    $file_tmp = $_FILES['material_file']['tmp_name'];
    $target_directory = "UPLOAD/materials/" . time() . "_" . $file_name;

    if (move_uploaded_file($file_tmp, $target_directory)) {
        $sql = "INSERT INTO learning_materials (workshop_id, title, file_path, uploaded_by) 
                VALUES ('$workshop_id', '$title', '$target_directory', '$trainer_id')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Material uploaded successfully!'); window.location.href='upload_material.php';</script>";
        } else {
            echo "Database Error: " . mysqli_error($conn);
        }
    } else {
        echo "<script>alert('Failed to upload file to the folder.');</script>";
    }
}
// --- END: ORIGINAL UPLOAD LOGIC ---

// --- NEW FEATURE: DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // First, get the file path to remove it from the folder
    $file_query = mysqli_query($conn, "SELECT file_path FROM learning_materials WHERE material_id = '$delete_id' AND uploaded_by = '$trainer_id'");
    $file_data = mysqli_fetch_assoc($file_query);
    
    if ($file_data) {
        // Delete physical file
        if (file_exists($file_data['file_path'])) {
            unlink($file_data['file_path']);
        }
        // Delete from database
        mysqli_query($conn, "DELETE FROM learning_materials WHERE material_id = '$delete_id'");
        echo "<script>alert('Material deleted successfully!'); window.location.href='upload_material.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Upload Materials - SmartWorkshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="form-card">
            <h2>Upload Learning Material</h2>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">Share slides or notes with your participants</p>

            <form action="upload_material.php" method="POST" enctype="multipart/form-data">
                <div>
                    <label>Select Workshop</label>
                    <select name="workshop_id" required>
                        <option value="" disabled selected>Choose a workshop...</option>
                        <?php
                        $workshops = mysqli_query($conn, "SELECT id, title FROM workshops WHERE trainer_id = '$trainer_id'");
                        while ($row = mysqli_fetch_assoc($workshops)) {
                            echo "<option value='" . $row['id'] . "'>" . $row['title'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label>Material Title</label>
                    <input type="text" name="title" required placeholder="e.g. Canva Beginner Slides">
                </div>

                <div>
                    <label>File (PDF, PPTX, or Image)</label>
                    <input type="file" name="material_file" required>
                </div>

                <button type="submit" name="submit" class="btn"> Upload Now</button>
            </form>
        </div>

        <div class="card" style="margin-top: 30px; max-width: 900px; margin-left: auto; margin-right: auto;">
            <h2 style="text-align: center;">Your Uploaded Materials</h2>
            <table class="profile-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background-color: #f4f4f4;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Workshop</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Title</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">File</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $materials_query = "SELECT m.*, w.title as workshop_title 
                                        FROM learning_materials m 
                                        JOIN workshops w ON m.workshop_id = w.id 
                                        WHERE m.uploaded_by = '$trainer_id'
                                        ORDER BY m.material_id DESC";
                    $materials_result = mysqli_query($conn, $materials_query);

                    if (mysqli_num_rows($materials_result) > 0) {
                        while ($m_row = mysqli_fetch_assoc($materials_result)) {
                            ?>
                            <tr>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($m_row['workshop_title']); ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($m_row['title']); ?></td>
                                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                    <a href="<?= $m_row['file_path']; ?>" target="_blank" style="color: #3498db;">View File</a>
                                </td>
                                <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                    <a href="upload_material.php?delete_id=<?= $m_row['material_id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this material?');" 
                                       class="btn" style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.8rem;">Delete</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding: 20px;'>No materials uploaded yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <div style="margin-top: 20px; text-align: center;">
                <a href="index.php" style="color: #3498db; text-decoration: none;">← Back to Home</a>
            </div>
        </div>
    </main>

</body>
</html>