<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="form-card">

            <h2>Welcome Back</h2>

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

            <form action="login_action.php" method="POST">

                <div>
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>

                <div>
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                    <div style="text-align:right; margin-top:5px;">
                        <a href="forgot_password.php" style="font-size:0.85rem; color:#666;">Forgot Password?</a>
                    </div>
                </div>

                <button type="submit" name="login_btn">Login</button>

                <p style="text-align:center; margin-top:15px; font-size:0.9rem;">
                    New user? <a href="register.php" style="color: #3498db; font-weight:600;">Create an account</a>
                </p>

            </form>

        </div>
    </main>
    <?php include 'include/footer.php'; ?>

</body>

</html>