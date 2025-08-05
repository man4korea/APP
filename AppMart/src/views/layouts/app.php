<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title><?php echo htmlspecialchars($title ?? 'AppMart - AI-Powered Web App Marketplace'); ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/app.css'); ?>">
    
    <!-- Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($description ?? 'Discover and download premium web applications from talented developers worldwide.'); ?>">
    <meta name="keywords" content="web apps, marketplace, developers, php applications, download apps">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($title ?? 'AppMart'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description ?? 'AI-Powered Web App Marketplace'); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo url($_SERVER['REQUEST_URI']); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo asset('images/favicon.ico'); ?>">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo url('/'); ?>" class="logo">AppMart</a>
                
                <nav class="nav">
                    <a href="<?php echo url('/'); ?>" <?php echo ($_GET['route'] ?? '') === '' ? 'class="active"' : ''; ?>>Home</a>
                    <a href="<?php echo url('/apps'); ?>" <?php echo ($_GET['route'] ?? '') === 'apps' ? 'class="active"' : ''; ?>>Browse Apps</a>
                    
                    <?php if (controllers\AuthController::isAuthenticated()): ?>
                        <?php $user = controllers\AuthController::getUser(); ?>
                        
                        <a href="<?php echo url('/dashboard'); ?>" <?php echo ($_GET['route'] ?? '') === 'dashboard' ? 'class="active"' : ''; ?>>Dashboard</a>
                        
                        <?php if ($user['role'] === 'developer'): ?>
                            <a href="<?php echo url('/apps/create'); ?>">Upload App</a>
                        <?php endif; ?>
                        
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?php echo url('/admin'); ?>">Admin</a>
                        <?php endif; ?>
                        
                        <div style="display: inline-flex; align-items: center; gap: 1rem; margin-left: 1rem;">
                            <span style="color: #64748b;">Hello, <?php echo htmlspecialchars($user['username']); ?></span>
                            <a href="<?php echo url('/auth/logout'); ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Logout</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo url('/auth/login'); ?>" class="btn btn-outline">Login</a>
                        <a href="<?php echo url('/auth/register'); ?>" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['auth_success'])): ?>
        <div class="alert alert-success" style="margin: 1rem auto; max-width: 1200px;">
            <?php echo htmlspecialchars($_SESSION['auth_success']); ?>
            <?php unset($_SESSION['auth_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['auth_errors'])): ?>
        <div class="alert alert-error" style="margin: 1rem auto; max-width: 1200px;">
            <?php foreach ($_SESSION['auth_errors'] as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
            <?php unset($_SESSION['auth_errors']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['app_success'])): ?>
        <div class="alert alert-success" style="margin: 1rem auto; max-width: 1200px;">
            <?php echo htmlspecialchars($_SESSION['app_success']); ?>
            <?php unset($_SESSION['app_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['app_errors'])): ?>
        <div class="alert alert-error" style="margin: 1rem auto; max-width: 1200px;">
            <?php foreach ($_SESSION['app_errors'] as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
            <?php unset($_SESSION['app_errors']); ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer style="background: #1f2937; color: #d1d5db; padding: 3rem 0 2rem; margin-top: 4rem;">
        <div class="container">
            <div class="grid grid-cols-4" style="gap: 2rem;">
                <div>
                    <h3 style="color: #3b82f6; margin-bottom: 1rem; font-size: 1.2rem;">AppMart</h3>
                    <p style="font-size: 0.9rem; line-height: 1.6;">
                        AI-powered marketplace for discovering and downloading premium web applications from talented developers worldwide.
                    </p>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1rem; color: #f9fafb;">For Developers</h4>
                    <ul style="list-style: none; font-size: 0.9rem; line-height: 1.8;">
                        <li><a href="<?php echo url('/auth/register'); ?>" style="color: #d1d5db; text-decoration: none;">Join as Developer</a></li>
                        <li><a href="<?php echo url('/apps/create'); ?>" style="color: #d1d5db; text-decoration: none;">Upload Your App</a></li>
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">Developer Guidelines</a></li>
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">API Documentation</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1rem; color: #f9fafb;">Support</h4>
                    <ul style="list-style: none; font-size: 0.9rem; line-height: 1.8;">
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">Help Center</a></li>
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">Contact Us</a></li>
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">Terms of Service</a></li>
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1rem; color: #f9fafb;">Connect</h4>
                    <ul style="list-style: none; font-size: 0.9rem; line-height: 1.8;">
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">Blog</a></li>
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">Newsletter</a></li>
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">Twitter</a></li>
                        <li><a href="#" style="color: #d1d5db; text-decoration: none;">GitHub</a></li>
                    </ul>
                </div>
            </div>
            
            <hr style="border: none; height: 1px; background: #374151; margin: 2rem 0 1rem;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                <p>&copy; <?php echo date('Y'); ?> AppMart. All rights reserved.</p>
                <p>Built with ❤️ for developers</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo asset('js/app.js'); ?>"></script>
    
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?php echo asset($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>