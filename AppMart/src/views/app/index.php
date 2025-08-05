<?php
$content = ob_start();
?>

<section style="padding: 2rem 0; background: #f8fafc;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1 style="font-size: 2rem; font-weight: bold; color: #1f2937; margin-bottom: 0.5rem;">Browse Applications</h1>
                <p style="color: #6b7280;">Discover amazing web applications from talented developers</p>
            </div>
            
            <?php if (controllers\AuthController::isAuthenticated()): ?>
                <?php $user = controllers\AuthController::getUser(); ?>
                <?php if ($user['role'] === 'developer'): ?>
                    <a href="<?php echo url('/apps/create'); ?>" class="btn btn-primary">Upload New App</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Filters & Search -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form method="GET" action="<?php echo url('/apps'); ?>" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="search" class="form-label">Search Apps</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            class="form-input" 
                            placeholder="Search by title, description..."
                            value="<?php echo htmlspecialchars($filters['search']); ?>"
                        >
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-input form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $filters['category'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['parent_id'] ? '— ' : ''; ?><?php echo htmlspecialchars($cat['name']); ?>
                                    <?php if ($cat['app_count'] > 0): ?>
                                        (<?php echo $cat['app_count']; ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="price" class="form-label">Price</label>
                        <select id="price" name="price" class="form-input form-select">
                            <option value="all" <?php echo $filters['price'] === 'all' ? 'selected' : ''; ?>>All Apps</option>
                            <option value="free" <?php echo $filters['price'] === 'free' ? 'selected' : ''; ?>>Free Only</option>
                            <option value="paid" <?php echo $filters['price'] === 'paid' ? 'selected' : ''; ?>>Paid Only</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="sort" class="form-label">Sort By</label>
                        <select id="sort" name="sort" class="form-input form-select">
                            <option value="popular" <?php echo $filters['sort'] === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                            <option value="newest" <?php echo $filters['sort'] === 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="price_low" <?php echo $filters['sort'] === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $filters['sort'] === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo $filters['sort'] === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <p style="color: #6b7280; margin: 0;">
                Showing <?php echo count($apps); ?> of <?php echo $pagination['total_items']; ?> applications
            </p>
            
            <?php if (!empty($filters['search'])): ?>
                <p style="color: #6b7280; margin: 0;">
                    Search results for "<strong><?php echo htmlspecialchars($filters['search']); ?></strong>"
                </p>
            <?php endif; ?>
        </div>

        <!-- Applications Grid -->
        <?php if (!empty($apps)): ?>
            <div class="grid grid-cols-3" style="margin-bottom: 3rem;">
                <?php foreach ($apps as $app): ?>
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
                            
                            <div style="margin-bottom: 1rem;">
                                <?php if ($app['category_name']): ?>
                                    <span style="background: #e0e7ff; color: #3730a3; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500;">
                                        <?php echo htmlspecialchars($app['category_name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="app-card-meta">
                                <span>by <?php echo htmlspecialchars($app['owner_username']); ?></span>
                                <span class="app-card-price <?php echo $app['price'] == 0 ? 'free' : ''; ?>">
                                    <?php echo $app['price'] == 0 ? 'Free' : '$' . number_format($app['price'], 2); ?>
                                </span>
                            </div>
                            
                            <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #9ca3af;">
                                <span>⭐ <?php echo $app['rating_average'] ? number_format($app['rating_average'], 1) : 'N/A'; ?></span>
                                <span>📥 <?php echo number_format($app['download_count']); ?></span>
                                <span>v<?php echo htmlspecialchars($app['version']); ?></span>
                            </div>
                            
                            <div style="margin-top: 1rem;">
                                <a href="<?php echo url('/apps/show?id=' . $app['id']); ?>" class="btn btn-primary" style="width: 100%; text-align: center;">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div style="display: flex; justify-content: center; align-items: center; gap: 1rem;">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="<?php echo url('/apps?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1]))); ?>" class="btn btn-outline">← Previous</a>
                    <?php endif; ?>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <?php
                        $start = max(1, $pagination['current_page'] - 2);
                        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                        
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="<?php echo url('/apps?' . http_build_query(array_merge($filters, ['page' => $i]))); ?>" 
                               class="btn <?php echo $i === $pagination['current_page'] ? 'btn-primary' : 'btn-outline'; ?>"
                               style="min-width: 40px; text-align: center;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="<?php echo url('/apps?' . http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1]))); ?>" class="btn btn-outline">Next →</a>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem; color: #6b7280; font-size: 0.9rem;">
                    Page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- No Results -->
            <div style="text-align: center; padding: 4rem 2rem; color: #6b7280;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
                <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">No applications found</h3>
                <p style="margin-bottom: 2rem;">
                    <?php if (!empty($filters['search'])): ?>
                        No applications match your search criteria. Try adjusting your filters or search terms.
                    <?php else: ?>
                        There are no applications available at the moment.
                    <?php endif; ?>
                </p>
                
                <div>
                    <a href="<?php echo url('/apps'); ?>" class="btn btn-outline">Clear Filters</a>
                    <?php if (controllers\AuthController::isAuthenticated()): ?>
                        <?php $user = controllers\AuthController::getUser(); ?>
                        <?php if ($user['role'] === 'developer'): ?>
                            <a href="<?php echo url('/apps/create'); ?>" class="btn btn-primary" style="margin-left: 1rem;">Upload First App</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>