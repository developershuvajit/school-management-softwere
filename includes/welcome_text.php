<div class="welcome-text">
    <h4>
        Hi, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>!
        Welcome back to your <strong>
            <?php
            // Convert role to readable format
            switch ($_SESSION['user_role'] ?? '') {
                case 'super_admin':
                    echo "Super Admin";
                    break;
                case 'teacher':
                    echo "Teacher";
                    break;
                case 'parent':
                    echo "Parent";
                    break;
                default:
                    echo "Dashboard";
                    break;
            }
            ?>
        </strong> dashboard!
    </h4>
</div>