<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="brand">
            <img src="img/logo.jpg" alt="Logo" class="logo-img">
            SmartWorkshop
        </a>

        <div class="hamburger">
            <div class="line"></div>
            <div class="line"></div>
            <div class="line"></div>
        </div>

        <ul class="nav-links">
            <li><a href="index.php"> Home</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>


                <?php if ($_SESSION['role'] == 'organizer'): ?>
                    <li><a href="dashboard_organizer.php">Dashboard</a></li>
                    <li><a href="report_attendance.php">Attendance</a></li>
                    <li><a href="manage_workshops.php">Workshops</a></li>
                    <li><a href="report_feedback.php">Feedback</a></li>

                <?php elseif ($_SESSION['role'] == 'trainer'): ?>
                    <li><a href="dashboard_trainer.php">Dashboard</a></li>
                    <li><a href="report_feedback.php">Feedback</a></li>
                    <li><a href="upload_material.php">Materials</a></li>
                    <li><a href="trainer_view.php">Submissions</a></li>

                <?php elseif ($_SESSION['role'] == 'participant'): ?>
                    <!-- Participant links -->
                    <li><a href="my_workshops.php"> Workshops</a></li>
                    <li><a href="my_certificates.php"> Certificates</a></li>
                    <li><a href="feedback.php"> Feedback</a></li>

                <?php elseif ($_SESSION['role'] == 'admin'): ?>
                    <li><a href="dashboard_analytics.php">Analytics</a></li>
                    <li><a href="report_attendance.php">Attendance</a></li>
                    <li><a href="report_feedback.php">Feedback</a></li>
                    <li><a href="manage_users.php">Users</a></li>
                <?php endif; ?>

                <li><a href="profile.php"> Profile</a></li>

                <?php
                // Get unread notifications
                include_once 'include/notifications_api.php';
                $unread_count = getUnreadCount($conn, $_SESSION['user_id']);
                ?>
                <li>
                    <a href="notifications.php" style="position: relative;">
                        🔔
                        <?php if ($unread_count > 0): ?>
                            <span
                                style="background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.7rem; position: absolute; top: -5px; right: -5px;">
                                <?= $unread_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="logout.php" style="color: #e74c3c;"> Logout</a></li>
            <?php else: ?>
                <li><a href="register.php">📝 Register</a></li>
                <li><a href="login.php">🔐 Login</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <script>
        const burger = document.querySelector('.hamburger');
        const nav = document.querySelector('.nav-links');
        const navLinks = document.querySelectorAll('.nav-links li');

        burger.addEventListener('click', () => {
            // Toggle Nav
            nav.classList.toggle('nav-active');

            // Burger Animation
            burger.classList.toggle('toggle');
        });

        // Prevent transition flash on resize
        let resizeTimer;
        window.addEventListener("resize", () => {
            document.body.classList.add("resize-animation-stopper");
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                document.body.classList.remove("resize-animation-stopper");
            }, 400);
        });
    </script>
</nav>