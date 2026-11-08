<?php
session_start();
include 'include/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all certificates for this user
$certs_query = "SELECT c.*, w.title as workshop_name, w.date as workshop_date
    FROM certificates c
    JOIN workshops w ON c.workshop_id = w.id
    WHERE c.user_id = $user_id
    ORDER BY c.issue_date DESC";

$certs_result = mysqli_query($conn, $certs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates - Digital Skills Workshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'include/nav.php'; ?>

    <div class="certificates-container">
        <h2>🏆 My Certificates</h2>
        
        <?php if (mysqli_num_rows($certs_result) > 0): ?>
            <div class="cert-grid">
                <?php while ($cert = mysqli_fetch_assoc($certs_result)): ?>
                    <div class="cert-card">
                        <div class="cert-inner">
                            <div class="cert-icon">🏆</div>
                            <div class="cert-workshop-name"><?php echo htmlspecialchars($cert['workshop_name']); ?></div>
                            <div class="cert-date">
                                Completed: <?php echo date('F j, Y', strtotime($cert['workshop_date'])); ?>
                            </div>
                            <div class="cert-code">
                                <?php echo $cert['certificate_code']; ?>
                            </div>
                            <a href="view_certificate.php?workshop_id=<?php echo $cert['workshop_id']; ?>" 
                               class="btn-view-cert">
                                View Certificate
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: white; border-radius: 10px; margin-top: 30px;">
                <h3>No certificates yet</h3>
                <p>Complete workshops and submit feedback to earn certificates!</p>
                <a href="my_workshops.php" class="btn" style="width: auto; padding: 10px 30px; margin-top: 20px;">View My Workshops</a>
            </div>
        <?php endif; ?>
    </div>

    <?php 
    if (file_exists('include/footer.php')) {
        include 'include/footer.php';
    }
    ?>
</body>
</html>