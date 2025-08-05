<?php
// C:\xampp\htdocs\AppMart\src\views\admin\apps.php
// Create at 2508051030 Ver1.00
$content = ob_start();
?>

<section style="padding: 2rem 0; background: #f8fafc;">
    <div class="container">
        <!-- Page Header -->
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: bold; color: #1f2937; margin-bottom: 0.5rem;">
                        📱 Manage Applications
                    </h1>
                    <p style="color: #6b7280; margin: 0;">Review, approve, and manage all applications on the platform</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="exportApps()">📊 Export</button>
                    <a href="<?php echo url('/admin/categories'); ?>" class="btn btn-outline">📂 Categories</a>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label for="search" class="form-label">Search</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            class="form-input" 
                            placeholder="App name, developer..."
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-input">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo ($_GET['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo ($_GET['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="draft" <?php echo ($_GET['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-input">
                            <option value="">All Categories</option>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($_GET['category'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sort" class="form-label">Sort By</label>
                        <select id="sort" name="sort" class="form-input">
                            <option value="created_at_desc" <?php echo ($_GET['sort'] ?? '') === 'created_at_desc' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="created_at_asc" <?php echo ($_GET['sort'] ?? '') === 'created_at_asc' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="title_asc" <?php echo ($_GET['sort'] ?? '') === 'title_asc' ? 'selected' : ''; ?>>Name A-Z</option>
                            <option value="downloads_desc" <?php echo ($_GET['sort'] ?? '') === 'downloads_desc' ? 'selected' : ''; ?>>Most Downloads</option>
                            <option value="price_desc" <?php echo ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : ''; ?>>Highest Price</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">🔍 Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['admin_success'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid #bbf7d0;">
                <?php echo htmlspecialchars($_SESSION['admin_success']); ?>
                <?php unset($_SESSION['admin_success']); ?>
            </div>
        <?php endif; ?>

        <!-- Status Summary -->
        <div class="grid grid-cols-4" style="gap: 1rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 0.25rem;">
                        <?php echo number_format($status_counts['pending'] ?? 0); ?>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">Pending Review</div>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #10b981; margin-bottom: 0.25rem;">
                        <?php echo number_format($status_counts['approved'] ?? 0); ?>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">Approved</div>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #ef4444; margin-bottom: 0.25rem;">
                        <?php echo number_format($status_counts['rejected'] ?? 0); ?>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">Rejected</div>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #6b7280; margin-bottom: 0.25rem;">
                        <?php echo number_format($status_counts['draft'] ?? 0); ?>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">Drafts</div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>All Applications (<?php echo number_format($total_count ?? 0); ?>)</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-outline" style="font-size: 0.8rem;" onclick="selectAll()">Select All</button>
                    <button class="btn btn-outline" style="font-size: 0.8rem;" onclick="bulkAction('approve')" id="bulkApprove" disabled>Bulk Approve</button>
                    <button class="btn btn-outline" style="font-size: 0.8rem;" onclick="bulkAction('reject')" id="bulkReject" disabled>Bulk Reject</button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($apps)): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e5e7eb;">
                                    <th style="text-align: left; padding: 1rem 0.5rem; color: #374151; font-weight: 600; width: 40px;">
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                    </th>
                                    <th style="text-align: left; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Application</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Developer</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Category</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Status</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Price</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Downloads</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Submitted</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($apps as $app): ?>
                                    <tr style="border-bottom: 1px solid #f3f4f6;" data-app-id="<?php echo $app['id']; ?>">
                                        <td style="padding: 1rem 0.5rem;">
                                            <input type="checkbox" class="app-checkbox" value="<?php echo $app['id']; ?>" onchange="updateBulkActions()">
                                        </td>
                                        <td style="padding: 1rem 0.5rem;">
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <?php if ($app['thumbnail']): ?>
                                                    <img src="<?php echo htmlspecialchars($app['thumbnail']); ?>" alt="<?php echo htmlspecialchars($app['title']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.5rem;">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">📱</div>
                                                <?php endif; ?>
                                                <div>
                                                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;">
                                                        <a href="<?php echo url('/admin/apps/review?id=' . $app['id']); ?>" style="text-decoration: none; color: inherit;">
                                                            <?php echo htmlspecialchars($app['title']); ?>
                                                        </a>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                                        v<?php echo htmlspecialchars($app['version']); ?>
                                                        <?php if ($app['short_description']): ?>
                                                            • <?php echo htmlspecialchars(substr($app['short_description'], 0, 50)); ?>...
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem;">
                                            <div style="font-weight: 500; color: #374151;">
                                                <a href="<?php echo url('/admin/users/view?id=' . $app['owner_id']); ?>" style="text-decoration: none; color: inherit;">
                                                    <?php echo htmlspecialchars($app['owner_username']); ?>
                                                </a>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #6b7280;">
                                                <?php echo htmlspecialchars($app['owner_email'] ?? ''); ?>
                                            </div>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem;">
                                            <span style="background: #f3f4f6; color: #374151; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                                <?php echo htmlspecialchars($app['category_name'] ?? 'Uncategorized'); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem;">
                                            <?php
                                            $statusColors = [
                                                'approved' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                                'pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                                'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                                'draft' => ['bg' => '#f3f4f6', 'text' => '#6b7280']
                                            ];
                                            $color = $statusColors[$app['status']] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                                            ?>
                                            <span style="background: <?php echo $color['bg']; ?>; color: <?php echo $color['text']; ?>; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">
                                                <?php echo htmlspecialchars($app['status']); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem; font-weight: 600;">
                                            <?php echo $app['price'] == 0 ? 'Free' : '$' . number_format($app['price'], 2); ?>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem; font-weight: 600; color: #6b7280;">
                                            <?php echo number_format($app['download_count'] ?? 0); ?>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem; color: #6b7280; font-size: 0.8rem;">
                                            <?php echo date('M j, Y', strtotime($app['created_at'])); ?>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem;">
                                            <div style="display: flex; justify-content: center; gap: 0.25rem; flex-wrap: wrap;">
                                                <a href="<?php echo url('/admin/apps/review?id=' . $app['id']); ?>" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Review</a>
                                                <?php if ($app['status'] === 'pending'): ?>
                                                    <button onclick="quickAction(<?php echo $app['id']; ?>, 'approved')" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #10b981; border-color: #10b981;">✓</button>
                                                    <button onclick="quickAction(<?php echo $app['id']; ?>, 'rejected')" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #ef4444; border-color: #ef4444;">✗</button>
                                                <?php elseif ($app['status'] === 'approved'): ?>
                                                    <button onclick="quickAction(<?php echo $app['id']; ?>, 'rejected')" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #ef4444; border-color: #ef4444;">Reject</button>
                                                <?php elseif ($app['status'] === 'rejected'): ?>
                                                    <button onclick="quickAction(<?php echo $app['id']; ?>, 'approved')" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #10b981; border-color: #10b981;">Approve</button>
                                                <?php endif; ?>
                                                <button onclick="deleteApp(<?php echo $app['id']; ?>)" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #ef4444; border-color: #ef4444;">🗑️</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                        <div style="display: flex; justify-content: center; align-items: center; margin-top: 2rem; gap: 0.5rem;">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])); ?>" class="btn btn-outline" style="padding: 0.5rem 0.75rem;">← Prev</a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                <?php if ($i == $pagination['current_page']): ?>
                                    <span class="btn btn-primary" style="padding: 0.5rem 0.75rem;"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="btn btn-outline" style="padding: 0.5rem 0.75rem;"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])); ?>" class="btn btn-outline" style="padding: 0.5rem 0.75rem;">Next →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem 2rem; color: #6b7280;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📱</div>
                        <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">No Applications Found</h3>
                        <p style="margin: 0;">No applications match your current filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
let selectedApps = [];

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.app-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateBulkActions();
}

function selectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    selectAllCheckbox.checked = true;
    toggleSelectAll();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.app-checkbox:checked');
    const bulkApprove = document.getElementById('bulkApprove');
    const bulkReject = document.getElementById('bulkReject');
    
    selectedApps = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedApps.length > 0) {
        bulkApprove.disabled = false;
        bulkReject.disabled = false;
        bulkApprove.textContent = `Bulk Approve (${selectedApps.length})`;
        bulkReject.textContent = `Bulk Reject (${selectedApps.length})`;
    } else {
        bulkApprove.disabled = true;
        bulkReject.disabled = true;
        bulkApprove.textContent = 'Bulk Approve';
        bulkReject.textContent = 'Bulk Reject';
    }
}

function bulkAction(action) {
    if (selectedApps.length === 0) return;
    
    const actionText = action === 'approve' ? 'approve' : 'reject';
    
    if (confirm(`Are you sure you want to ${actionText} ${selectedApps.length} selected applications?`)) {
        // Show loading state
        const bulkButtons = document.querySelectorAll('#bulkApprove, #bulkReject');
        bulkButtons.forEach(btn => {
            btn.disabled = true;
            btn.textContent = '⏳ Processing...';
        });
        
        // Submit bulk action
        fetch(`<?php echo url('/admin/apps/bulk-action'); ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?php echo csrf_token(); ?>'
            },
            body: JSON.stringify({
                app_ids: selectedApps,
                action: action
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(`${data.count} applications ${actionText}d successfully!`, 'success');
                
                // Remove processed rows
                selectedApps.forEach(appId => {
                    const row = document.querySelector(`tr[data-app-id="${appId}"]`);
                    if (row) {
                        row.style.opacity = '0.5';
                        setTimeout(() => row.remove(), 300);
                    }
                });
                
                // Reset selection
                selectedApps = [];
                document.getElementById('selectAllCheckbox').checked = false;
                updateBulkActions();
                
                // Reload page after delay to update counts
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || `Failed to ${actionText} applications`, 'error');
                
                // Re-enable buttons
                bulkButtons.forEach(btn => {
                    btn.disabled = false;
                });
                updateBulkActions();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(`Error: Failed to ${actionText} applications`, 'error');
            
            // Re-enable buttons
            bulkButtons.forEach(btn => {
                btn.disabled = false;
            });
            updateBulkActions();
        });
    }
}

function quickAction(appId, action) {
    const actionText = action === 'approved' ? 'approve' : 'reject';
    
    if (confirm(`Are you sure you want to ${actionText} this application?`)) {
        const row = document.querySelector(`tr[data-app-id="${appId}"]`);
        const buttons = row.querySelectorAll('button');
        
        // Show loading state
        buttons.forEach(btn => {
            btn.disabled = true;
            if (btn.onclick && btn.onclick.toString().includes(action)) {
                btn.textContent = '⏳';
            }
        });
        
        // Submit action
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
                showNotification(`Application ${actionText}d successfully!`, 'success');
                
                // Update row status
                const statusCell = row.querySelector('td:nth-child(5) span');
                const statusColors = {
                    'approved': { bg: '#dcfce7', text: '#166534' },
                    'rejected': { bg: '#fee2e2', text: '#991b1b' }
                };
                const color = statusColors[action];
                
                statusCell.style.background = color.bg;
                statusCell.style.color = color.text;
                statusCell.textContent = action.charAt(0).toUpperCase() + action.slice(1);
                
                // Update action buttons
                const actionsCell = row.querySelector('td:last-child div');
                if (action === 'approved') {
                    actionsCell.innerHTML = `
                        <a href="<?php echo url('/admin/apps/review?id='); ?>${appId}" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Review</a>
                        <button onclick="quickAction(${appId}, 'rejected')" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #ef4444; border-color: #ef4444;">Reject</button>
                        <button onclick="deleteApp(${appId})" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #ef4444; border-color: #ef4444;">🗑️</button>
                    `;
                } else {
                    actionsCell.innerHTML = `
                        <a href="<?php echo url('/admin/apps/review?id='); ?>${appId}" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Review</a>
                        <button onclick="quickAction(${appId}, 'approved')" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #10b981; border-color: #10b981;">Approve</button>
                        <button onclick="deleteApp(${appId})" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #ef4444; border-color: #ef4444;">🗑️</button>
                    `;
                }
            } else {
                showNotification(data.message || `Failed to ${actionText} application`, 'error');
                
                // Re-enable buttons
                buttons.forEach(btn => {
                    btn.disabled = false;
                    if (btn.onclick && btn.onclick.toString().includes('approved')) {
                        btn.textContent = '✓';
                    } else if (btn.onclick && btn.onclick.toString().includes('rejected')) {
                        btn.textContent = '✗';
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification(`Error: Failed to ${actionText} application`, 'error');
            
            // Re-enable buttons
            buttons.forEach(btn => {
                btn.disabled = false;
                if (btn.onclick && btn.onclick.toString().includes('approved')) {
                    btn.textContent = '✓';
                } else if (btn.onclick && btn.onclick.toString().includes('rejected')) {
                    btn.textContent = '✗';
                }
            });
        });
    }
}

function deleteApp(appId) {
    if (confirm('Are you sure you want to permanently delete this application? This action cannot be undone.')) {
        if (confirm('This will permanently delete the application and all associated data. Type "DELETE" to confirm.')) {
            const confirmation = prompt('Please type "DELETE" to confirm:');
            if (confirmation === 'DELETE') {
                const row = document.querySelector(`tr[data-app-id="${appId}"]`);
                row.style.opacity = '0.5';
                
                fetch(`<?php echo url('/admin/apps/delete'); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo csrf_token(); ?>'
                    },
                    body: JSON.stringify({ app_id: appId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Application deleted successfully!', 'success');
                        setTimeout(() => row.remove(), 300);
                    } else {
                        showNotification(data.message || 'Failed to delete application', 'error');
                        row.style.opacity = '1';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error: Failed to delete application', 'error');
                    row.style.opacity = '1';
                });
            }
        }
    }
}

function exportApps() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    
    showNotification('Preparing export...', 'success');
    window.open('?' + params.toString(), '_blank');
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
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateBulkActions();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>