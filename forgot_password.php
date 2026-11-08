<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="form-card">

            <h2>Forgot Password</h2>
            <p style="text-align:center; margin-bottom: 20px;">Enter your email to reset your password.</p>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-msg">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-msg">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="forgot_password_action.php" method="POST">

                <div>
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your registered email">
                </div>

                <button type="submit" name="forgot_btn">Send Reset Link</button>

                <p style="text-align:center; margin-top:15px; font-size:0.9rem;">
                    Remembered? <a href="login.php" style="color: #3498db; font-weight:600;">Login here</a>
                </p>

            </form>

        </div>
    </main>
    <?php include 'include/footer.php'; ?>

</body>

</html>