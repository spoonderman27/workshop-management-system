<?php
session_start();
include 'include/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$workshop_id = isset($_GET['workshop_id']) ? intval($_GET['workshop_id']) : 0;

// Fetch certificate details - FIXED: Column names
$cert_query = "SELECT c.*, u.username as user_name, w.title as workshop_name, w.date as workshop_date
               FROM certificates c
               JOIN users u ON c.user_id = u.user_id
               JOIN workshops w ON c.workshop_id = w.id
               WHERE c.user_id = $user_id AND c.workshop_id = $workshop_id";

$cert_result = mysqli_query($conn, $cert_query);
$certificate = mysqli_fetch_assoc($cert_result);

if (!$certificate) {
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo htmlspecialchars($certificate['user_name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
        }
        body {
            font-family: 'Georgia', serif;
            background: url('img/bg.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .page-content {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
        }
        .certificate {
            width: 1000px;
            height: 700px;
            margin: 20px auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            position: relative;
        }
        .certificate-inner {
            width: 100%;
            height: 100%;
            background: white;
            border: 8px solid #6c5ce7;
            padding: 40px;
            position: relative;
            text-align: center;
        }
        .certificate-inner::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 2px solid #6c5ce7;
            pointer-events: none;
        }
        .logo {
            font-size: 24px;
            color: #6c5ce7;
            margin-bottom: 20px;
        }
        .title {
            font-size: 42px;
            color: #6c5ce7;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 5px;
        }
        .subtitle {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        .recipient-label {
            font-size: 16px;
            color: #888;
        }
        .recipient-name {
            font-size: 38px;
            color: #333;
            font-weight: bold;
            margin: 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #6c5ce7;
            display: inline-block;
        }
        .workshop-label {
            font-size: 16px;
            color: #888;
            margin-top: 20px;
        }
        .workshop-name {
            font-size: 24px;
            color: #6c5ce7;
            margin: 10px 0;
            font-style: italic;
        }
        .details {
            margin-top: 40px;
            display: flex;
            justify-content: space-around;
        }
        .detail-item {
            text-align: center;
        }
        .detail-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
        }
        .detail-value {
            font-size: 14px;
            color: #333;
            margin-top: 5px;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            margin: 0 auto;
            padding-top: 5px;
        }
        .print-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 15px 30px;
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .print-btn:hover {
            opacity: 0.9;
        }
        @media print {
            body {
                background: white;
            }
            .print-btn {
                display: none;
            }
            .certificate {
                margin: 0;
                box-shadow: none;
            }
            footer {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="page-content">
        <div class="certificate">
            <div class="certificate-inner">
                <div class="logo">🎓 Digital Skills Workshop Platform</div>
                <h1 class="title">Certificate of Completion</h1>
                <p class="subtitle">This is to certify that</p>
                <p class="recipient-label">has been awarded to</p>
                <h2 class="recipient-name"><?php echo htmlspecialchars($certificate['user_name']); ?></h2>
                <p class="workshop-label">for successfully completing the workshop</p>
                <h3 class="workshop-name">"<?php echo htmlspecialchars($certificate['workshop_name']); ?>"</h3>
                
                <div class="details">
                    <div class="detail-item">
                        <div class="detail-label">Workshop Date</div>
                        <div class="detail-value"><?php echo date('F j, Y', strtotime($certificate['workshop_date'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Certificate Code</div>
                        <div class="detail-value"><?php echo $certificate['certificate_code']; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Issue Date</div>
                        <div class="detail-value"><?php echo date('F j, Y', strtotime($certificate['issue_date'])); ?></div>
                    </div>
                </div>
                
                <div style="margin-top: 40px;">
                    <div class="signature-line">Authorized Signature</div>
                </div>
            </div>
        </div>
        
        <button class="print-btn" onclick="window.print()">🖨️ Print Certificate</button>
    </div>
	<?php include 'include/footer.php'; ?>
</body>
</html>