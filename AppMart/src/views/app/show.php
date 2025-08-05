<?php
$content = ob_start();
?>

<section style="padding: 2rem 0; background: #f8fafc;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 2rem;">
            <ol style="display: flex; align-items: center; gap: 0.5rem; margin: 0; padding: 0; list-style: none; color: #6b7280;">
                <li><a href="<?php echo url('/'); ?>" style="color: #3b82f6; text-decoration: none;">Home</a></li>
                <li>›</li>
                <li><a href="<?php echo url('/apps'); ?>" style="color: #3b82f6; text-decoration: none;">Apps</a></li>
                <li>›</li>
                <li><?php echo htmlspecialchars($app['title']); ?></li>
            </ol>
        </nav>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem; margin-bottom: 3rem;">
            <!-- Main Content -->
            <div>
                <!-- App Header -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-body">
                        <div style="display: flex; gap: 2rem;">
                            <!-- App Icon/Thumbnail -->
                            <div style="flex-shrink: 0;">
                                <?php if ($app['thumbnail']): ?>
                                    <img src="<?php echo htmlspecialchars($app['thumbnail']); ?>" alt="<?php echo htmlspecialchars($app['title']); ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 1rem; border: 2px solid #e5e7eb;">
                                <?php else: ?>
                                    <div style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 3rem; border-radius: 1rem;">
                                        📱
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- App Info -->
                            <div style="flex: 1;">
                                <h1 style="font-size: 2.5rem; font-weight: bold; color: #1f2937; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($app['title']); ?>
                                </h1>
                                
                                <p style="font-size: 1.1rem; color: #6b7280; margin-bottom: 1rem;">
                                    <?php echo htmlspecialchars($app['short_description']); ?>
                                </p>
                                
                                <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="color: #f59e0b;">⭐</span>
                                        <span style="font-weight: 600;">
                                            <?php echo $app['rating_average'] ? number_format($app['rating_average'], 1) : 'N/A'; ?>
                                        </span>
                                        <span style="color: #6b7280;">
                                            (<?php echo number_format($app['rating_count']); ?> reviews)
                                        </span>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span>📥</span>
                                        <span style="font-weight: 600;"><?php echo number_format($app['download_count']); ?></span>
                                        <span style="color: #6b7280;">downloads</span>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span>📝</span>
                                        <span style="font-weight: 600;">v<?php echo htmlspecialchars($app['version']); ?></span>
                                    </div>
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span style="color: #6b7280;">by</span>
                                    <a href="#" style="color: #3b82f6; text-decoration: none; font-weight: 600;">
                                        <?php echo htmlspecialchars($app['owner_username']); ?>
                                    </a>
                                    
                                    <?php if ($app['category_name']): ?>
                                        <span style="background: #e0e7ff; color: #3730a3; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500;">
                                            <?php echo htmlspecialchars($app['category_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Screenshots -->
                <?php if (!empty($app['screenshots'])): ?>
                    <?php $screenshots = is_array($app['screenshots']) ? $app['screenshots'] : json_decode($app['screenshots'], true); ?>
                    <?php if (is_array($screenshots) && count($screenshots) > 0): ?>
                        <div class="card" style="margin-bottom: 2rem;">
                            <div class="card-header">
                                <h3>Screenshots</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                                    <?php foreach ($screenshots as $screenshot): ?>
                                        <img src="<?php echo htmlspecialchars($screenshot); ?>" alt="Screenshot" style="width: 100%; height: 200px; object-fit: cover; border-radius: 0.5rem; border: 1px solid #e5e7eb; cursor: pointer;" onclick="openScreenshot(this.src)">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Description -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>Description</h3>
                    </div>
                    <div class="card-body">
                        <div style="line-height: 1.7; color: #374151;">
                            <?php echo nl2br(htmlspecialchars($app['description'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Technical Details -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>Technical Details</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                            <div>
                                <h4 style="font-weight: 600; margin-bottom: 1rem; color: #374151;">Tech Stack</h4>
                                <?php if ($app['tech_stack']): ?>
                                    <?php $techStack = is_array($app['tech_stack']) ? $app['tech_stack'] : json_decode($app['tech_stack'], true); ?>
                                    <?php if (is_array($techStack)): ?>
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                            <?php foreach ($techStack as $tech): ?>
                                                <span style="background: #f3f4f6; color: #374151; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">
                                                    <?php echo htmlspecialchars($tech); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <h4 style="font-weight: 600; margin-bottom: 1rem; color: #374151;">Details</h4>
                                <div style="space-y: 0.5rem;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="color: #6b7280;">Version:</span>
                                        <span style="font-weight: 500;">v<?php echo htmlspecialchars($app['version']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="color: #6b7280;">Database:</span>
                                        <span style="font-weight: 500;"><?php echo htmlspecialchars($app['database_type']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="color: #6b7280;">File Size:</span>
                                        <span style="font-weight: 500;"><?php echo formatFileSize($app['file_size']); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="color: #6b7280;">Published:</span>
                                        <span style="font-weight: 500;"><?php echo date('M j, Y', strtotime($app['published_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <?php if ($app['tags']): ?>
                    <?php $tags = is_array($app['tags']) ? $app['tags'] : json_decode($app['tags'], true); ?>
                    <?php if (is_array($tags) && count($tags) > 0): ?>
                        <div class="card" style="margin-bottom: 2rem;">
                            <div class="card-header">
                                <h3>Tags</h3>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    <?php foreach ($tags as $tag): ?>
                                        <a href="<?php echo url('/apps?search=' . urlencode($tag)); ?>" style="background: #dbeafe; color: #1e40af; padding: 0.5rem 1rem; border-radius: 1rem; text-decoration: none; font-size: 0.875rem; font-weight: 500; transition: background-color 0.2s;"
                                           onmouseover="this.style.backgroundColor='#bfdbfe'" onmouseout="this.style.backgroundColor='#dbeafe'">
                                            #<?php echo htmlspecialchars($tag); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Purchase/Download Card -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-body" style="text-align: center;">
                        <div style="margin-bottom: 2rem;">
                            <div style="font-size: 3rem; font-weight: bold; color: <?php echo $app['price'] == 0 ? '#10b981' : '#1f2937'; ?>; margin-bottom: 0.5rem;">
                                <?php echo $app['price'] == 0 ? 'Free' : '$' . number_format($app['price'], 2); ?>
                            </div>
                            <?php if ($app['price'] > 0): ?>
                                <p style="color: #6b7280; margin: 0;">One-time purchase</p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (controllers\AuthController::isAuthenticated()): ?>
                            <button class="btn btn-primary" style="width: 100%; margin-bottom: 1rem; font-size: 1.1rem; padding: 1rem;" onclick="downloadApp(<?php echo $app['id']; ?>)">
                                <?php echo $app['price'] == 0 ? '📥 Download Now' : '🛒 Purchase & Download'; ?>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo url('/auth/login'); ?>" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem; font-size: 1.1rem; padding: 1rem; display: block; text-decoration: none; text-align: center;">
                                <?php echo $app['price'] == 0 ? '📥 Login to Download' : '🛒 Login to Purchase'; ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($app['demo_url']): ?>
                            <a href="<?php echo htmlspecialchars($app['demo_url']); ?>" target="_blank" class="btn btn-outline" style="width: 100%; margin-bottom: 1rem;">
                                🌐 View Demo
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($app['github_url']): ?>
                            <a href="<?php echo htmlspecialchars($app['github_url']); ?>" target="_blank" class="btn btn-outline" style="width: 100%; margin-bottom: 1rem;">
                                🔗 GitHub Repository
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($app['documentation_url']): ?>
                            <a href="<?php echo htmlspecialchars($app['documentation_url']); ?>" target="_blank" class="btn btn-outline" style="width: 100%;">
                                📚 Documentation
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Developer Info -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h3>Developer</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold;">
                                <?php echo strtoupper(substr($app['owner_username'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($app['owner_username']); ?></h4>
                                <p style="color: #6b7280; margin: 0;">Developer</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; color: #6b7280; font-size: 0.9rem;">
                            <span>Member since</span>
                            <span><?php echo date('M Y', strtotime($app['owner_created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Related Apps -->
                <?php if (!empty($related_apps)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3>Related Apps</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($related_apps as $relatedApp): ?>
                                <div style="display: flex; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e5e7eb;">
                                    <?php if ($relatedApp['thumbnail']): ?>
                                        <img src="<?php echo htmlspecialchars($relatedApp['thumbnail']); ?>" alt="<?php echo htmlspecialchars($relatedApp['title']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.5rem;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">📱</div>
                                    <?php endif; ?>
                                    
                                    <div style="flex: 1;">
                                        <h5 style="margin: 0 0 0.25rem 0; font-size: 0.9rem;">
                                            <a href="<?php echo url('/apps/show?id=' . $relatedApp['id']); ?>" style="color: #374151; text-decoration: none;">
                                                <?php echo htmlspecialchars($relatedApp['title']); ?>
                                            </a>
                                        </h5>
                                        <p style="margin: 0; font-size: 0.8rem; color: #6b7280;">
                                            <?php echo $relatedApp['price'] == 0 ? 'Free' : '$' . number_format($relatedApp['price'], 2); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Screenshot Modal -->
<div id="screenshotModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1000; cursor: pointer;" onclick="closeScreenshot()">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 90%; max-height: 90%;">
        <img id="screenshotModalImage" src="" alt="Screenshot" style="max-width: 100%; max-height: 100%; border-radius: 0.5rem;">
    </div>
    <button onclick="closeScreenshot()" style="position: absolute; top: 2rem; right: 2rem; background: rgba(255,255,255,0.2); color: white; border: none; font-size: 2rem; cursor: pointer; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">×</button>
</div>

<script>
function downloadApp(appId) {
    if (confirm('Are you sure you want to download this application?')) {
        // In a real implementation, this would handle the download/purchase process
        alert('Download/purchase functionality would be implemented here');
        
        // Example AJAX call for download
        // fetch('/api/apps/' + appId + '/download', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' }
        // }).then(response => response.json())
        //   .then(data => {
        //       if (data.success) {
        //           window.location.href = data.download_url;
        //       }
        //   });
    }
}

function openScreenshot(src) {
    document.getElementById('screenshotModalImage').src = src;
    document.getElementById('screenshotModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeScreenshot() {
    document.getElementById('screenshotModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}
</script>

<?php
// File size formatting helper function
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>