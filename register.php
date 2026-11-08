<?php
session_start();
include("include/db_connect.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include("include/nav.php"); ?>

    <main>
        <div class="form-card">

            <h2>Create an Account</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-msg">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="register_action.php" method="POST">

                <div>
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="eg pineapple">
                </div>

                <div>
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="will@example.com">
                </div>

                <div>
                    <label>Phone</label>
                    <input type="tel" name="phone" required placeholder="eg 601234567, no spaces or symbol">
                </div>

                <div>
                    <label>Country</label>
                    <input type="text" name="country" required placeholder="eg Malaysia">
                </div>

                <div>
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6" required
                        placeholder="6 letter and above">
                </div>

                <div>
                    <label>I am a:</label>
                    <select name="role">
                        <option value="participant">Participant (I want to learn)</option>
                        <option value="organizer">Organizer (I manage events)</option>
                        <option value="trainer">Trainer (I teach)</option>
                    </select>
                </div>

                <br>
                <button type="submit" name="register_btn">Register</button>

            </form>

            <p style="text-align:center; margin-top:15px; font-size:0.9rem;">
                Already have an account? <a href="login.php" style="color: #3498db;">Login</a>
            </p>

        </div>
    </main>
    <?php include 'include/footer.php'; ?>

</body>

</html>