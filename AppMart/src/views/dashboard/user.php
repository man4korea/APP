<?php
$content = ob_start();
?>

<section style="padding: 2rem 0; background: #f8fafc;">
    <div class="container">
        <!-- Welcome Header -->
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: bold; color: #1f2937; margin-bottom: 0.5rem;">
                        👋 Welcome back, <?php echo htmlspecialchars($user['first_name'] ?: $user['username']); ?>!
                    </h1>
                    <p style="color: #6b7280; margin: 0;">Discover and manage your applications</p>
                </div>
                <a href="<?php echo url('/apps'); ?>" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.75rem 1.5rem;">
                    🔍 Browse Apps
                </a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-3" style="gap: 1.5rem; margin-bottom: 3rem;">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #3b82f6; margin-bottom: 0.5rem;">
                        <?php echo number_format($stats['total_purchases'] ?: 0); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Apps Purchased</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #10b981; margin-bottom: 0.5rem;">
                        $<?php echo number_format($stats['total_spent'] ?: 0, 2); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Total Spent</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 0.5rem;">
                        <?php echo number_format($stats['purchases_this_month'] ?: 0); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">This Month</div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Main Content -->
            <div>
                <!-- My Applications -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>My Applications</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($purchases)): ?>
                            <div class="grid grid-cols-2" style="gap: 1.5rem;">
                                <?php foreach ($purchases as $purchase): ?>
                                    <div class="card" style="border: 1px solid #e5e7eb;">
                                        <div style="display: flex; gap: 1rem; padding: 1rem;">
                                            <?php if ($purchase['thumbnail']): ?>
                                                <img src="<?php echo htmlspecialchars($purchase['thumbnail']); ?>" alt="<?php echo htmlspecialchars($purchase['title']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.5rem; flex-shrink: 0;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; flex-shrink: 0;">📱</div>
                                            <?php endif; ?>
                                            
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="font-weight: 600; margin-bottom: 0.5rem; font-size: 1rem;">
                                                    <a href="<?php echo url('/apps/show?id=' . $purchase['application_id']); ?>" style="text-decoration: none; color: #1f2937;">
                                                        <?php echo htmlspecialchars($purchase['title']); ?>
                                                    </a>
                                                </h4>
                                                <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0.5rem;">
                                                    by <?php echo htmlspecialchars($purchase['owner_username']); ?> • v<?php echo htmlspecialchars($purchase['version']); ?>
                                                </p>
                                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;">
                                                    <span style="color: #6b7280;">
                                                        Purchased: <?php echo date('M j, Y', strtotime($purchase['purchased_at'])); ?>
                                                    </span>
                                                    <?php if ($purchase['amount'] > 0): ?>
                                                        <span style="font-weight: 600; color: #10b981;">
                                                            $<?php echo number_format($purchase['amount'], 2); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="font-weight: 600; color: #3b82f6;">Free</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem;">
                                                    <a href="<?php echo url('/apps/show?id=' . $purchase['application_id']); ?>" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; flex: 1; text-align: center;">
                                                        View Details
                                                    </a>
                                                    <a href="#" class="btn btn-primary" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; flex: 1; text-align: center;" onclick="downloadApp(<?php echo $purchase['application_id']; ?>)">
                                                        📥 Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 4rem 2rem; color: #6b7280;">
                                <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
                                <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">No Applications Yet</h3>
                                <p style="margin-bottom: 2rem;">Explore our marketplace and find amazing applications to enhance your projects!</p>
                                <a href="<?php echo url('/apps'); ?>" class="btn btn-primary">Browse Applications</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- My Reviews -->
                <?php if (!empty($user_reviews)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>My Reviews</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($user_reviews as $review): ?>
                                <div style="border-bottom: 1px solid #f3f4f6; padding-bottom: 1rem; margin-bottom: 1rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                        <h4 style="font-weight: 600; font-size: 1rem; margin: 0;">
                                            <a href="<?php echo url('/apps/show?slug=' . $review['app_slug']); ?>" style="text-decoration: none; color: #1f2937;">
                                                <?php echo htmlspecialchars($review['app_title']); ?>
                                            </a>
                                        </h4>
                                        <div style="display: flex; align-items: center; gap: 0.25rem;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span style="color: <?php echo $i <= $review['rating'] ? '#f59e0b' : '#d1d5db'; ?>; font-size: 0.9rem;">⭐</span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($review['title']): ?>
                                        <h5 style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0.5rem; color: #374151;">
                                            "<?php echo htmlspecialchars($review['title']); ?>"
                                        </h5>
                                    <?php endif; ?>
                                    
                                    <p style="color: #6b7280; font-size: 0.9rem; line-height: 1.5; margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars($review['content']); ?>
                                    </p>
                                    
                                    <div style="display: flex; justify-content: between; align-items: center; font-size: 0.8rem; color: #9ca3af;">
                                        <span>Posted on <?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                                        <?php if ($review['status'] === 'pending'): ?>
                                            <span style="background: #fef3c7; color: #92400e; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">Pending Review</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Account Status -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>Account Status</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold;">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-weight: 600; color: #1f2937;">
                                    <?php echo htmlspecialchars($user['first_name'] && $user['last_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['username']); ?>
                                </h4>
                                <p style="margin: 0; color: #6b7280; font-size: 0.9rem; text-transform: capitalize;">
                                    <?php echo htmlspecialchars($user['role']); ?> Account
                                </p>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Member since:</span>
                                <span style="font-weight: 500;"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Email:</span>
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommended Apps -->
                <?php if (!empty($recommended_apps)): ?>
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3>Recommended for You</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach (array_slice($recommended_apps, 0, 4) as $app): ?>
                                <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #f3f4f6;">
                                    <?php if ($app['thumbnail']): ?>
                                        <img src="<?php echo htmlspecialchars($app['thumbnail']); ?>" alt="<?php echo htmlspecialchars($app['title']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.5rem; flex-shrink: 0;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; flex-shrink: 0;">📱</div>
                                    <?php endif; ?>
                                    
                                    <div style="flex: 1; min-width: 0;">
                                        <h5 style="font-weight: 600; font-size: 0.9rem; margin-bottom: 0.25rem;">
                                            <a href="<?php echo url('/apps/show?id=' . $app['id']); ?>" style="text-decoration: none; color: #1f2937;">
                                                <?php echo htmlspecialchars($app['title']); ?>
                                            </a>
                                        </h5>
                                        <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0.25rem;">
                                            by <?php echo htmlspecialchars($app['owner_username']); ?>
                                        </p>
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;">
                                            <span style="color: #10b981; font-weight: 600;">
                                                <?php echo $app['price'] == 0 ? 'Free' : '$' . number_format($app['price'], 2); ?>
                                            </span>
                                            <?php if ($app['rating_average']): ?>
                                                <span style="display: flex; align-items: center; gap: 0.125rem;">
                                                    <span style="color: #f59e0b;">⭐</span>
                                                    <span style="color: #6b7280;"><?php echo number_format($app['rating_average'], 1); ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <a href="<?php echo url('/apps'); ?>" class="btn btn-outline" style="width: 100%; text-align: center; font-size: 0.9rem;">
                                View All Apps
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <a href="<?php echo url('/apps'); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">🔍 Browse Apps</a>
                            <a href="<?php echo url('/dashboard/profile'); ?>" class="btn btn-outline" style="width: 100%; text-align: center;">👤 Edit Profile</a>
                            <?php if ($user['role'] !== 'developer'): ?>
                                <a href="#" class="btn btn-outline" style="width: 100%; text-align: center;" onclick="upgradeToDeveloper()">🚀 Become Developer</a>
                            <?php endif; ?>
                            <a href="#" class="btn btn-outline" style="width: 100%; text-align: center;">📧 Contact Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function downloadApp(appId) {
    if (confirm('Start downloading this application?')) {
        // In a real implementation, this would handle the download process
        window.open('/apps/download?id=' + appId, '_blank');
    }
}

function upgradeToDeveloper() {
    if (confirm('Would you like to upgrade to a developer account? This will allow you to upload and sell your own applications.')) {
        // In a real implementation, this would handle the account upgrade
        alert('Account upgrade feature coming soon! Please contact support for manual upgrade.');
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>