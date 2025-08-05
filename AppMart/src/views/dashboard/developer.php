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
                    <p style="color: #6b7280; margin: 0;">Manage your applications and track your success</p>
                </div>
                <a href="<?php echo url('/apps/create'); ?>" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.75rem 1.5rem;">
                    📱 Upload New App
                </a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-4" style="gap: 1.5rem; margin-bottom: 3rem;">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #3b82f6; margin-bottom: 0.5rem;">
                        <?php echo number_format($stats['total_apps'] ?: 0); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Total Apps</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #10b981; margin-bottom: 0.5rem;">
                        <?php echo number_format($stats['total_downloads'] ?: 0); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Total Downloads</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 0.5rem;">
                        <?php echo $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : 'N/A'; ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Avg Rating</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #8b5cf6; margin-bottom: 0.5rem;">
                        $<?php echo number_format($earnings['total_earnings'] ?: 0, 2); ?>
                    </div>
                    <div style="color: #6b7280; font-weight: 500;">Total Earnings</div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Main Content -->
            <div>
                <!-- App Status Overview -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3>App Status Overview</h3>
                        <div style="display: flex; gap: 1rem; font-size: 0.875rem;">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 12px; height: 12px; background: #10b981; border-radius: 50%;"></div>
                                Approved: <?php echo $stats['approved_apps'] ?: 0; ?>
                            </span>
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 12px; height: 12px; background: #f59e0b; border-radius: 50%;"></div>
                                Pending: <?php echo $stats['pending_apps'] ?: 0; ?>
                            </span>
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 50%;"></div>
                                Rejected: <?php echo $stats['rejected_apps'] ?: 0; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- My Applications -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>My Applications</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($apps)): ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #e5e7eb;">
                                            <th style="text-align: left; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">App Name</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Status</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Downloads</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Rating</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Price</th>
                                            <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($apps as $app): ?>
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
                                                                <a href="<?php echo url('/apps/show?id=' . $app['id']); ?>" style="text-decoration: none; color: inherit;">
                                                                    <?php echo htmlspecialchars($app['title']); ?>
                                                                </a>
                                                            </div>
                                                            <div style="font-size: 0.8rem; color: #6b7280;">
                                                                <?php echo htmlspecialchars($app['category_name'] ?: 'Uncategorized'); ?> • v<?php echo htmlspecialchars($app['version']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem;">
                                                    <?php
                                                    $statusColors = [
                                                        'approved' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                                        'pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                                        'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b']
                                                    ];
                                                    $color = $statusColors[$app['status']] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                                                    ?>
                                                    <span style="background: <?php echo $color['bg']; ?>; color: <?php echo $color['text']; ?>; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">
                                                        <?php echo htmlspecialchars($app['status']); ?>
                                                    </span>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem; font-weight: 600;">
                                                    <?php echo number_format($app['download_count']); ?>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem;">
                                                    <?php if ($app['rating_average']): ?>
                                                        <div style="display: flex; align-items: center; justify-content: center; gap: 0.25rem;">
                                                            <span style="color: #f59e0b;">⭐</span>
                                                            <span style="font-weight: 600;"><?php echo number_format($app['rating_average'], 1); ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span style="color: #9ca3af;">No ratings</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem; font-weight: 600;">
                                                    <?php echo $app['price'] == 0 ? 'Free' : '$' . number_format($app['price'], 2); ?>
                                                </td>
                                                <td style="text-align: center; padding: 1rem 0.5rem;">
                                                    <div style="display: flex; justify-content: center; gap: 0.5rem;">
                                                        <a href="<?php echo url('/apps/show?id=' . $app['id']); ?>" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">View</a>
                                                        <a href="#" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Edit</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 4rem 2rem; color: #6b7280;">
                                <div style="font-size: 4rem; margin-bottom: 1rem;">📱</div>
                                <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">No Applications Yet</h3>
                                <p style="margin-bottom: 2rem;">Start building your portfolio by uploading your first application!</p>
                                <a href="<?php echo url('/apps/create'); ?>" class="btn btn-primary">Upload Your First App</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Monthly Downloads Chart -->
                <?php if (!empty($chart_data)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>Download Trends (Last 6 Months)</h3>
                        </div>
                        <div class="card-body">
                            <div style="height: 300px; display: flex; align-items: end; justify-content: space-around; border-bottom: 2px solid #e5e7eb; border-left: 2px solid #e5e7eb; padding: 1rem;">
                                <?php
                                $maxDownloads = max(array_column($chart_data, 'downloads'));
                                foreach ($chart_data as $data):
                                    $height = $maxDownloads > 0 ? ($data['downloads'] / $maxDownloads) * 250 : 0;
                                ?>
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                        <div style="font-size: 0.75rem; font-weight: 600; color: #374151;"><?php echo $data['downloads']; ?></div>
                                        <div style="width: 40px; background: linear-gradient(to top, #3b82f6, #60a5fa); height: <?php echo $height; ?>px; border-radius: 4px 4px 0 0;"></div>
                                        <div style="font-size: 0.75rem; color: #6b7280;"><?php echo date('M', strtotime($data['month'] . '-01')); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Monthly Summary -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>This Month</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #6b7280;">Sales</span>
                                <span style="font-weight: 600; color: #1f2937;"><?php echo number_format($earnings['sales_this_month'] ?: 0); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #6b7280;">Earnings</span>
                                <span style="font-weight: 600; color: #10b981;">$<?php echo number_format($earnings['earnings_this_month'] ?: 0, 2); ?></span>
                            </div>
                            <hr style="border: none; height: 1px; background: #e5e7eb; margin: 0.5rem 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #6b7280;">Total Lifetime</span>
                                <span style="font-weight: 600; color: #8b5cf6;">$<?php echo number_format($earnings['total_earnings'] ?: 0, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Reviews -->
                <?php if (!empty($recent_reviews)): ?>
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3>Recent Reviews</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($recent_reviews as $review): ?>
                                <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #f3f4f6;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                        <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($review['app_title']); ?></div>
                                        <div style="display: flex; align-items: center; gap: 0.25rem;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span style="color: <?php echo $i <= $review['rating'] ? '#f59e0b' : '#d1d5db'; ?>; font-size: 0.8rem;">⭐</span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0.5rem; line-height: 1.4;">
                                        "<?php echo htmlspecialchars(substr($review['content'], 0, 100)); ?><?php echo strlen($review['content']) > 100 ? '...' : ''; ?>"
                                    </p>
                                    <div style="font-size: 0.75rem; color: #9ca3af;">
                                        by <?php echo htmlspecialchars($review['reviewer_username']); ?> • <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                            <a href="<?php echo url('/apps/create'); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">📱 Upload New App</a>
                            <a href="<?php echo url('/dashboard/profile'); ?>" class="btn btn-outline" style="width: 100%; text-align: center;">👤 Edit Profile</a>
                            <a href="#" class="btn btn-outline" style="width: 100%; text-align: center;">📊 View Analytics</a>
                            <a href="#" class="btn btn-outline" style="width: 100%; text-align: center;">💰 Payout Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>