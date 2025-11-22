<?php
// FILE: /app/views/layouts/guest.php
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
<body class="guest-layout">
    <div class="guest-container">
        <div class="guest-header">
            <h1>SplashMarketingAI</h1>
            <p>AI-Powered Multi-Channel Marketing Automation</p>
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
        <div class="guest-content">
            <?php echo $content; ?>
        </div>

        <div class="guest-footer">
            <p>&copy; <?php echo date('Y'); ?> SplashMarketingAI. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
