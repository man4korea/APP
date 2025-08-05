<?php
$content = ob_start();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Discover Amazing Web Applications</h1>
        <p class="hero-subtitle">AI-powered marketplace for premium web apps built by talented developers worldwide</p>
        
        <div style="margin-top: 2rem;">
            <a href="<?php echo url('/apps'); ?>" class="btn btn-primary" style="margin-right: 1rem; font-size: 1.1rem; padding: 1rem 2rem;">Browse Apps</a>
            <a href="<?php echo url('/auth/register'); ?>" class="btn btn-outline" style="font-size: 1.1rem; padding: 1rem 2rem; color: white; border-color: white;">Join as Developer</a>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section style="padding: 3rem 0; background: white;">
    <div class="container">
        <div class="grid grid-cols-4 text-center">
            <div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #3b82f6; margin-bottom: 0.5rem;">
                    <?php echo number_format($stats['total_apps'] ?? 0); ?>
                </div>
                <div style="color: #6b7280; font-weight: 500;">Premium Apps</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #10b981; margin-bottom: 0.5rem;">
                    <?php echo number_format($stats['total_developers'] ?? 0); ?>
                </div>
                <div style="color: #6b7280; font-weight: 500;">Developers</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 0.5rem;">
                    <?php echo number_format($stats['total_downloads'] ?? 0); ?>
                </div>
                <div style="color: #6b7280; font-weight: 500;">Downloads</div>
            </div>
            <div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #ef4444; margin-bottom: 0.5rem;">
                    <?php echo number_format($stats['total_users'] ?? 0); ?>
                </div>
                <div style="color: #6b7280; font-weight: 500;">Happy Users</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Apps Section -->
<?php if (!empty($featured_apps)): ?>
<section style="padding: 4rem 0; background: #f8fafc;">
    <div class="container">
        <h2 style="text-align: center; font-size: 2.5rem; font-weight: bold; margin-bottom: 3rem; color: #1f2937;">Featured Applications</h2>
        
        <div class="grid grid-cols-3">
            <?php foreach ($featured_apps as $app): ?>
                <div class="card app-card">
                    <?php if ($app['thumbnail']): ?>
                        <img src="<?php echo htmlspecialchars($app['thumbnail']); ?>" alt="<?php echo htmlspecialchars($app['title']); ?>" class="app-card-image">
                    <?php else: ?>
                        <div class="app-card-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 3rem;">
                            📱
                        </div>
                    <?php endif; ?>
                    
                    <div class="app-card-content">
                        <h3 class="app-card-title">
                            <a href="<?php echo url('/apps/show?id=' . $app['id']); ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($app['title']); ?>
                            </a>
                        </h3>
                        
                        <p class="app-card-description">
                            <?php echo htmlspecialchars($app['short_description'] ?: substr($app['description'], 0, 100) . '...'); ?>
                        </p>
                        
                        <div class="app-card-meta">
                            <span>by <?php echo htmlspecialchars($app['owner_username']); ?></span>
                            <span class="app-card-price <?php echo $app['price'] == 0 ? 'free' : ''; ?>">
                                <?php echo $app['price'] == 0 ? 'Free' : '$' . number_format($app['price'], 2); ?>
                            </span>
                        </div>
                        
                        <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #9ca3af;">
                            <span>⭐ <?php echo $app['rating_average'] ? number_format($app['rating_average'], 1) : 'N/A'; ?></span>
                            <span>📥 <?php echo number_format($app['download_count']); ?> downloads</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center" style="margin-top: 2rem;">
            <a href="<?php echo url('/apps'); ?>" class="btn btn-primary">View All Apps</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<section style="padding: 4rem 0; background: white;">
    <div class="container">
        <h2 style="text-align: center; font-size: 2.5rem; font-weight: bold; margin-bottom: 3rem; color: #1f2937;">Popular Categories</h2>
        
        <div class="grid grid-cols-4">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo url('/apps?category=' . $category['id']); ?>" class="card" style="text-decoration: none; color: inherit; text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;"><?php echo $category['icon'] ?: '📁'; ?></div>
                    <h3 style="font-size: 1.2rem; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h3>
                    <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($category['description']); ?>
                    </p>
                    <div style="color: <?php echo $category['color']; ?>; font-weight: 600; font-size: 0.9rem;">
                        <?php echo $category['app_count']; ?> apps
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Recent Apps Section -->
<?php if (!empty($recent_apps)): ?>
<section style="padding: 4rem 0; background: #f8fafc;">
    <div class="container">
        <h2 style="text-align: center; font-size: 2.5rem; font-weight: bold; margin-bottom: 3rem; color: #1f2937;">Recently Added</h2>
        
        <div class="grid grid-cols-4">
            <?php foreach ($recent_apps as $app): ?>
                <div class="card app-card">
                    <?php if ($app['thumbnail']): ?>
                        <img src="<?php echo htmlspecialchars($app['thumbnail']); ?>" alt="<?php echo htmlspecialchars($app['title']); ?>" class="app-card-image">
                    <?php else: ?>
                        <div class="app-card-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 2rem;">
                            🆕
                        </div>
                    <?php endif; ?>
                    
                    <div class="app-card-content">
                        <h3 class="app-card-title">
                            <a href="<?php echo url('/apps/show?id=' . $app['id']); ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($app['title']); ?>
                            </a>
                        </h3>
                        
                        <div class="app-card-meta">
                            <span><?php echo htmlspecialchars($app['category_name']); ?></span>
                            <span class="app-card-price <?php echo $app['price'] == 0 ? 'free' : ''; ?>">
                                <?php echo $app['price'] == 0 ? 'Free' : '$' . number_format($app['price'], 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Call to Action Section -->
<section style="padding: 4rem 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center;">
    <div class="container">
        <h2 style="font-size: 2.5rem; font-weight: bold; margin-bottom: 1rem;">Ready to Start Building?</h2>
        <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">
            Join thousands of developers who trust AppMart to showcase and monetize their web applications.
        </p>
        
        <div>
            <a href="<?php echo url('/auth/register'); ?>" class="btn btn-primary" style="background: white; color: #667eea; margin-right: 1rem; font-size: 1.1rem; padding: 1rem 2rem;">
                Join as Developer
            </a>
            <a href="<?php echo url('/apps'); ?>" class="btn btn-outline" style="border-color: white; color: white; font-size: 1.1rem; padding: 1rem 2rem;">
                Explore Apps
            </a>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>