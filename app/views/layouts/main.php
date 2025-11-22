<?php
// FILE: /app/views/layouts/main.php
$auth = Auth::getInstance();
$session = Session::getInstance();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? View::e($title) . ' - ' : ''; ?>SplashMarketingAI</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>SplashMarketingAI</h1>
                <p class="tenant-name"><?php echo View::e($auth->user()['tenant_name'] ?? 'Platform'); ?></p>
            </div>

            <nav class="sidebar-nav">
                <a href="/dashboard" class="nav-link">
                    <span>📊</span> Dashboard
                </a>
                <a href="/contacts" class="nav-link">
                    <span>👥</span> Contacts
                </a>
                <a href="/campaigns" class="nav-link">
                    <span>📧</span> Campaigns
                </a>
                <a href="/automation" class="nav-link">
                    <span>⚡</span> Automation
                </a>
                <a href="/templates" class="nav-link">
                    <span>📝</span> Templates
                </a>
                <a href="/analytics" class="nav-link">
                    <span>📈</span> Analytics
                </a>
                <a href="/settings" class="nav-link">
                    <span>⚙️</span> Settings
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <strong><?php echo View::e($auth->user()['first_name'] . ' ' . $auth->user()['last_name']); ?></strong>
                    <small><?php echo View::e($auth->user()['role']); ?></small>
                </div>
                <a href="/logout" class="btn-logout">Logout</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h2><?php echo isset($pageTitle) ? View::e($pageTitle) : 'Dashboard'; ?></h2>
            </div>

            <!-- Flash Messages -->
            <?php if ($session->hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?php echo View::e($session->getFlash('success')); ?>
                </div>
            <?php endif; ?>

            <?php if ($session->hasFlash('error')): ?>
                <div class="alert alert-error">
                    <?php echo View::e($session->getFlash('error')); ?>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="content-body">
                <?php echo $content; ?>
            </div>
        </main>
    </div>
</body>
</html>
