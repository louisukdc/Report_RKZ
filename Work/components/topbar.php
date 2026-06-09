<?php
$user_data = function_exists('getCurrentUser') ? getCurrentUser() : null;
$username_display = htmlspecialchars(isset($user_data['username']) ? $user_data['username'] : 'User');
$role_display = htmlspecialchars(isset($user_data['role']) ? $user_data['role'] : 'Umum');
?>
<header class="topbar">
    <div class="page-title">
        <?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?>
    </div>
    <div class="user-profile">
        <div class="avatar">
            <i class="fas fa-user"></i>
        </div>
        <div style="display: flex; flex-direction: column; margin-right: 1rem;">
            <span style="font-weight: 600; font-size: 0.875rem;"><?= $username_display ?></span>
            <span style="color: var(--text-muted); font-size: 0.75rem;"><?= $role_display ?></span>
        </div>
        <a href="logout.php" class="btn btn-danger btn-sm" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</header>
