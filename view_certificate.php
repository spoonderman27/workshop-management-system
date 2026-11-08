<?php
session_start();
include 'include/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$workshop_id = isset($_GET['workshop_id']) ? intval($_GET['workshop_id']) : 0;

// Fetch certificate details
$cert_query = "SELECT c.*, u.username as user_name, w.title as workshop_name, w.date as workshop_date
               FROM certificates c
               JOIN users u ON c.user_id = u.user_id
               JOIN workshops w ON c.workshop_id = w.id
               WHERE c.user_id = $user_id AND c.workshop_id = $workshop_id";

$cert_result = mysqli_query($conn, $cert_query);
$certificate = mysqli_fetch_assoc($cert_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificate - Digital Skills Workshop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'include/nav.php'; ?>
    
    <div class="certificate-page">
        <?php if ($certificate): ?>
            <div class="certificate-preview">
                <div class="certificate-inner">
                    <h1>🏆 Certificate of Completion</h1>
                    <h2>This is to certify that</h2>
                    <div class="participant-name"><?php echo htmlspecialchars($certificate['user_name']); ?></div>
                    <p>has successfully completed the workshop</p>
                    <div class="workshop-name">"<?php echo htmlspecialchars($certificate['workshop_name']); ?>"</div>
                    
                    <div class="cert-details">
                        <p>Date: <?php echo date('F j, Y', strtotime($certificate['workshop_date'])); ?></p>
                        <p>Certificate Code: <span class="cert-code"><?php echo $certificate['certificate_code']; ?></span></p>
                        <p>Issued: <?php echo date('F j, Y', strtotime($certificate['issue_date'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="generate_certificate.php?workshop_id=<?php echo $workshop_id; ?>" 
                   class="btn-download" target="_blank">
                    📥 Download Certificate
                </a>
            </div>
        <?php else: ?>
            <div class="no-certificate">
                <h3>❌ No Certificate Found</h3>
                <p>Please complete the workshop and submit feedback to receive your certificate.</p>
                <a href="index.php" class="btn">Back to My Workshops</a>
            </div>
        <?php endif; ?>
    </div>
	<?php include 'include/footer.php'; ?>
</body>
</html>