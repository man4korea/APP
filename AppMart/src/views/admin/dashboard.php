<?php
// C:\xampp\htdocs\AppMart\src\views\admin\dashboard.php
// Create at 2508051030 Ver1.00
$content = ob_start();
?>

<section style="padding: 2rem 0; background: #f8fafc;">
    <div class="container">
        <!-- Page Header -->
        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 2rem; font-weight: bold; color: #1f2937; margin-bottom: 0.5rem;">
                🛠️ Admin Dashboard
            </h1>
            <p style="color: #6b7280; margin: 0;">Manage AppMart platform and monitor system health</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-4" style="gap: 1.5rem; margin-bottom: 3rem;">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #3b82f6; margin-bottom: 0.5rem;">
                        <?php echo number_format($stats['total_users'] ?: 0); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Total Users</div>
                    <div style="font-size: 0.8rem; color: #10b981; margin-top: 0.25rem;">
                        +<?php echo $stats['new_users_this_month'] ?: 0; ?> this month
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #10b981; margin-bottom: 0.5rem;">
                        <?php echo number_format($stats['total_apps'] ?: 0); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Total Apps</div>
                    <div style="font-size: 0.8rem; color: #f59e0b; margin-top: 0.25rem;">
                        <?php echo $stats['pending_apps'] ?: 0; ?> pending review
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 0.5rem;">
                        $<?php echo number_format($stats['total_revenue'] ?: 0, 2); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Total Revenue</div>
                    <div style="font-size: 0.8rem; color: #10b981; margin-top: 0.25rem;">
                        $<?php echo number_format($stats['revenue_this_month'] ?: 0, 2); ?> this month
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #8b5cf6; margin-bottom: 0.5rem;">
                        <?php echo number_format($stats['total_downloads'] ?: 0); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Total Downloads</div>
                    <div style="font-size: 0.8rem; color: #10b981; margin-top: 0.25rem;">
                        +<?php echo number_format($stats['downloads_this_week'] ?: 0); ?> this week
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Main Content -->
            <div>
                <!-- Pending Applications -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3>🔍 Pending Applications Review</h3>
                        <a href="<?php echo url('/admin/apps/pending'); ?>" class="btn btn-outline" style="font-size: 0.9rem;">
                            View All (<?php echo $stats['pending_apps'] ?: 0; ?>)
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pending_apps)): ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #e5e7eb;">
                                            <th style="text-align: left; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">App Name</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Developer</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Category</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Submitted</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($pending_apps, 0, 5) as $app): ?>
                                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                                <td style="padding: 1rem 0.5rem;">
                                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                        <?php if ($app['thumbnail']): ?>
                                                            <img src="<?php echo htmlspecialchars($app['thumbnail']); ?>" alt="<?php echo htmlspecialchars($app['title']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 0.5rem;">
                                                        <?php else: ?>
                                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">📱</div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <div style="font-weight: 600; color: #1f2937;">
                                                                <?php echo htmlspecialchars($app['title']); ?>
                                                            </div>
                                                            <div style="font-size: 0.8rem; color: #6b7280;">
                                                                v<?php echo htmlspecialchars($app['version']); ?> • $<?php echo number_format($app['price'], 2); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem;">
                                                    <div style="font-weight: 500; color: #374151;">
                                                        <?php echo htmlspecialchars($app['owner_username']); ?>
                                                    </div>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem;">
                                                    <span style="background: #f3f4f6; color: #374151; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                                        <?php echo htmlspecialchars($app['category_name']); ?>
                                                    </span>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem; color: #6b7280; font-size: 0.8rem;">
                                                    <?php echo date('M j', strtotime($app['created_at'])); ?>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem;">
                                                    <div style="display: flex; justify-content: center; gap: 0.5rem;">
                                                        <button onclick="reviewApp(<?php echo $app['id']; ?>, 'approved')" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #10b981; border-color: #10b981;">✓</button>
                                                        <button onclick="reviewApp(<?php echo $app['id']; ?>, 'rejected')" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #ef4444; border-color: #ef4444;">✗</button>
                                                        <a href="<?php echo url('/admin/apps/review?id=' . $app['id']); ?>" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Review</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                                <p style="margin: 0;">No applications pending review!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>📈 Recent Activity</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid #f3f4f6;">
                                    <div style="flex-shrink: 0;">
                                        <?php
                                        $icons = [
                                            'user_registered' => '👤',
                                            'app_uploaded' => '📱',
                                            'app_approved' => '✅',
                                            'app_rejected' => '❌',
                                            'purchase_made' => '💰',
                                            'review_posted' => '⭐'
                                        ];
                                        echo $icons[$activity['type']] ?? '🔔';
                                        ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500; color: #374151;">
                                            <?php echo htmlspecialchars($activity['description']); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #6b7280;">
                                            <?php echo date('M j, Y \a\t g:i A', strtotime($activity['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: #6b7280;">
                                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📊</div>
                                <p style="margin: 0;">No recent activity to display.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- System Health -->
                <div class="card">
                    <div class="card-header">
                        <h3>🖥️ System Health</h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2" style="gap: 1.5rem;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="color: #6b7280;">Database</span>
                                    <span style="color: #10b981; font-weight: 600;">✓ Connected</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="color: #6b7280;">File Storage</span>
                                    <span style="color: #10b981; font-weight: 600;">✓ Available</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #6b7280;">Cache</span>
                                    <span style="color: #10b981; font-weight: 600;">✓ Active</span>
                                </div>
                            </div>
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="color: #6b7280;">Disk Usage</span>
                                    <span style="color: #374151; font-weight: 600;"><?php echo $system_info['disk_usage'] ?? '45%'; ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="color: #6b7280;">Memory</span>
                                    <span style="color: #374151; font-weight: 600;"><?php echo $system_info['memory_usage'] ?? '67%'; ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #6b7280;">Uptime</span>
                                    <span style="color: #374151; font-weight: 600;"><?php echo $system_info['uptime'] ?? '15 days'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Quick Actions -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>⚡ Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <a href="<?php echo url('/admin/apps/pending'); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">
                                🔍 Review Apps (<?php echo $stats['pending_apps'] ?: 0; ?>)
                            </a>
                            <a href="<?php echo url('/admin/users'); ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                                👥 Manage Users
                            </a>
                            <a href="<?php echo url('/admin/categories'); ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                                📂 Manage Categories
                            </a>
                            <a href="<?php echo url('/admin/reports'); ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                                📊 View Reports
                            </a>
                            <a href="<?php echo url('/admin/settings'); ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                                ⚙️ System Settings
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Latest Reviews -->
                <?php if (!empty($latest_reviews)): ?>
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3>💬 Latest Reviews</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach (array_slice($latest_reviews, 0, 3) as $review): ?>
                                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #f3f4f6;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                        <div style="font-weight: 600; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars($review['app_title']); ?>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.25rem;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span style="color: <?php echo $i <= $review['rating'] ? '#f59e0b' : '#d1d5db'; ?>; font-size: 0.7rem;">⭐</span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0.5rem; line-height: 1.3;">
                                        "<?php echo htmlspecialchars(substr($review['content'], 0, 80)); ?>..."
                                    </p>
                                    <div style="font-size: 0.75rem; color: #9ca3af;">
                                        by <?php echo htmlspecialchars($review['reviewer_username']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- System Alerts -->
                <div class="card">
                    <div class="card-header">
                        <h3>🚨 System Alerts</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($alerts)): ?>
                            <?php foreach ($alerts as $alert): ?>
                                <div style="background: <?php echo $alert['type'] === 'warning' ? '#fef3c7' : '#fee2e2'; ?>; color: <?php echo $alert['type'] === 'warning' ? '#92400e' : '#991b1b'; ?>; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 0.75rem; font-size: 0.8rem;">
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;">
                                        <?php echo $alert['type'] === 'warning' ? '⚠️' : '🚨'; ?> 
                                        <?php echo htmlspecialchars($alert['title']); ?>
                                    </div>
                                    <div><?php echo htmlspecialchars($alert['message']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 1rem; color: #10b981;">
                                <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">✅</div>
                                <p style="margin: 0; font-size: 0.9rem;">All systems operational</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function reviewApp(appId, action) {
    const actionText = action === 'approved' ? 'approve' : 'reject';
    
    if (confirm(`Are you sure you want to ${actionText} this application?`)) {
        // Show loading state
        const buttons = document.querySelectorAll(`button[onclick*="${appId}"]`);
        buttons.forEach(btn => {
            btn.disabled = true;
            btn.textContent = '⏳';
        });
        
        // Submit the action
        fetch(`<?php echo url('/admin/apps/quick-review'); ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?php echo csrf_token(); ?>'
            },
            body: JSON.stringify({
                app_id: appId,
                action: action
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row or update UI
                const row = document.querySelector(`button[onclick*="${appId}"]`).closest('tr');
                row.style.opacity = '0.5';
                row.style.transition = 'opacity 0.3s';
                
                setTimeout(() => {
                    row.remove();
                    
                    // Update stats counter
                    const pendingCount = document.querySelector('.btn[href*="pending"]');
                    if (pendingCount) {
                        const currentCount = parseInt(pendingCount.textContent.match(/\d+/)[0]) - 1;
                        pendingCount.innerHTML = pendingCount.innerHTML.replace(/\d+/, currentCount);
                    }
                }, 300);
                
                // Show success message
                showNotification(`Application ${actionText}d successfully!`, 'success');
            } else {
                showNotification(data.message || `Failed to ${actionText} application`, 'error');
                
                // Re-enable buttons
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.textContent = btn.onclick.includes('approved') ? '✓' : '✗';
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(`Error: Failed to ${actionText} application`, 'error');
            
            // Re-enable buttons
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.textContent = btn.onclick.includes('approved') ? '✓' : '✗';
            });
        });
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#dcfce7' : '#fee2e2'};
        color: ${type === 'success' ? '#166534' : '#991b1b'};
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        border: 1px solid ${type === 'success' ? '#bbf7d0' : '#fecaca'};
        z-index: 1000;
        font-weight: 500;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        notification.style.transition = 'all 0.3s';
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>