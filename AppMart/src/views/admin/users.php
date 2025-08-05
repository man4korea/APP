<?php
// C:\xampp\htdocs\AppMart\src\views\admin\users.php
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
                        👥 Manage Users
                    </h1>
                    <p style="color: #6b7280; margin: 0;">View and manage all user accounts on the platform</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" onclick="exportUsers()">📊 Export</button>
                    <button class="btn btn-primary" onclick="createUser()">➕ Add User</button>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label for="search" class="form-label">Search</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            class="form-input" 
                            placeholder="Username, email, name..."
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-input">
                            <option value="">All Roles</option>
                            <option value="user" <?php echo ($_GET['role'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="developer" <?php echo ($_GET['role'] ?? '') === 'developer' ? 'selected' : ''; ?>>Developer</option>
                            <option value="admin" <?php echo ($_GET['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sort" class="form-label">Sort By</label>
                        <select id="sort" name="sort" class="form-input">
                            <option value="created_at_desc" <?php echo ($_GET['sort'] ?? '') === 'created_at_desc' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="created_at_asc" <?php echo ($_GET['sort'] ?? '') === 'created_at_asc' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="username_asc" <?php echo ($_GET['sort'] ?? '') === 'username_asc' ? 'selected' : ''; ?>>Username A-Z</option>
                            <option value="email_asc" <?php echo ($_GET['sort'] ?? '') === 'email_asc' ? 'selected' : ''; ?>>Email A-Z</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">🔍 Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Stats -->
        <div class="grid grid-cols-4" style="gap: 1rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #3b82f6; margin-bottom: 0.25rem;">
                        <?php echo number_format($stats['total_users'] ?? 0); ?>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">Total Users</div>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #10b981; margin-bottom: 0.25rem;">
                        <?php echo number_format($stats['developers'] ?? 0); ?>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">Developers</div>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 0.25rem;">
                        <?php echo number_format($stats['active_this_month'] ?? 0); ?>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">Active This Month</div>
                </div>
            </div>
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 1.5rem; font-weight: bold; color: #8b5cf6; margin-bottom: 0.25rem;">
                        <?php echo number_format($stats['new_this_week'] ?? 0); ?>
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">New This Week</div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>All Users (<?php echo number_format($total_count ?? 0); ?>)</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-outline" style="font-size: 0.8rem;" onclick="selectAll()">Select All</button>
                    <button class="btn btn-outline" style="font-size: 0.8rem;" onclick="bulkAction('suspend')" id="bulkSuspend" disabled>Bulk Suspend</button>
                    <button class="btn btn-outline" style="font-size: 0.8rem;" onclick="bulkAction('activate')" id="bulkActivate" disabled>Bulk Activate</button>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($users)): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e5e7eb;">
                                    <th style="text-align: left; padding: 1rem 0.5rem; color: #374151; font-weight: 600; width: 40px;">
                                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                    </th>
                                    <th style="text-align: left; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">User</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Role</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Apps</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Purchases</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Joined</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Status</th>
                                    <th style="text-align: center; padding: 1rem 0.5rem; color: #374151; font-weight: 600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr style="border-bottom: 1px solid #f3f4f6;" data-user-id="<?php echo $user['id']; ?>">
                                        <td style="padding: 1rem 0.5rem;">
                                            <input type="checkbox" class="user-checkbox" value="<?php echo $user['id']; ?>" onchange="updateBulkActions()">
                                        </td>
                                        <td style="padding: 1rem 0.5rem;">
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1rem; font-weight: bold;">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;">
                                                        <?php echo htmlspecialchars($user['first_name'] && $user['last_name'] ? $user['first_name'] . ' ' . $user['last_name'] : $user['username']); ?>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                                        @<?php echo htmlspecialchars($user['username']); ?>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #6b7280;">
                                                        <?php echo htmlspecialchars($user['email']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem;">
                                            <?php
                                            $roleColors = [
                                                'admin' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                                'developer' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
                                                'user' => ['bg' => '#f3f4f6', 'text' => '#374151']
                                            ];
                                            $color = $roleColors[$user['role']] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                                            ?>
                                            <span style="background: <?php echo $color['bg']; ?>; color: <?php echo $color['text']; ?>; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">
                                                <?php echo htmlspecialchars($user['role']); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem; font-weight: 600; color: #6b7280;">
                                            <?php echo number_format($user['app_count'] ?? 0); ?>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem; font-weight: 600; color: #6b7280;">
                                            <?php echo number_format($user['purchase_count'] ?? 0); ?>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem; color: #6b7280; font-size: 0.8rem;">
                                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem;">
                                            <span style="background: <?php echo ($user['status'] ?? 'active') === 'active' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo ($user['status'] ?? 'active') === 'active' ? '#166534' : '#991b1b'; ?>; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600; text-transform: capitalize;">
                                                <?php echo htmlspecialchars($user['status'] ?? 'active'); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0.5rem;">
                                            <div style="display: flex; justify-content: center; gap: 0.25rem; flex-wrap: wrap;">
                                                <a href="<?php echo url('/admin/users/view?id=' . $user['id']); ?>" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">View</a>
                                                <?php if ($user['role'] !== 'admin'): ?>
                                                    <button onclick="changeRole(<?php echo $user['id']; ?>)" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Role</button>
                                                    <?php if (($user['status'] ?? 'active') === 'active'): ?>
                                                        <button onclick="suspendUser(<?php echo $user['id']; ?>)" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #ef4444; border-color: #ef4444;">Suspend</button>
                                                    <?php else: ?>
                                                        <button onclick="activateUser(<?php echo $user['id']; ?>)" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: #10b981; border-color: #10b981;">Activate</button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
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
                        <div style="font-size: 4rem; margin-bottom: 1rem;">👥</div>
                        <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem; color: #374151;">No Users Found</h3>
                        <p style="margin: 0;">No users match your current filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
let selectedUsers = [];

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
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
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkSuspend = document.getElementById('bulkSuspend');
    const bulkActivate = document.getElementById('bulkActivate');
    
    selectedUsers = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedUsers.length > 0) {
        bulkSuspend.disabled = false;
        bulkActivate.disabled = false;
        bulkSuspend.textContent = `Bulk Suspend (${selectedUsers.length})`;
        bulkActivate.textContent = `Bulk Activate (${selectedUsers.length})`;
    } else {
        bulkSuspend.disabled = true;
        bulkActivate.disabled = true;
        bulkSuspend.textContent = 'Bulk Suspend';
        bulkActivate.textContent = 'Bulk Activate';
    }
}

function bulkAction(action) {
    if (selectedUsers.length === 0) return;
    
    const actionText = action === 'suspend' ? 'suspend' : 'activate';
    
    if (confirm(`Are you sure you want to ${actionText} ${selectedUsers.length} selected users?`)) {
        showNotification(`${actionText.charAt(0).toUpperCase() + actionText.slice(1)}ing users...`, 'success');
        
        // In a real implementation, make API call here
        setTimeout(() => {
            showNotification(`${selectedUsers.length} users ${actionText}d successfully!`, 'success');
            window.location.reload();
        }, 1000);
    }
}

function suspendUser(userId) {
    if (confirm('Are you sure you want to suspend this user? They will not be able to access the platform.')) {
        showNotification('Suspending user...', 'success');
        
        // In a real implementation, make API call here
        setTimeout(() => {
            showNotification('User suspended successfully!', 'success');
            
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const statusCell = row.querySelector('td:nth-child(7) span');
            statusCell.style.background = '#fee2e2';
            statusCell.style.color = '#991b1b';
            statusCell.textContent = 'Suspended';
            
            const actionCell = row.querySelector('td:last-child div');
            actionCell.innerHTML = actionCell.innerHTML.replace('Suspend', 'Activate').replace('#ef4444', '#10b981');
        }, 500);
    }
}

function activateUser(userId) {
    if (confirm('Are you sure you want to activate this user?')) {
        showNotification('Activating user...', 'success');
        
        // In a real implementation, make API call here
        setTimeout(() => {
            showNotification('User activated successfully!', 'success');
            
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const statusCell = row.querySelector('td:nth-child(7) span');
            statusCell.style.background = '#dcfce7';
            statusCell.style.color = '#166534';
            statusCell.textContent = 'Active';
            
            const actionCell = row.querySelector('td:last-child div');
            actionCell.innerHTML = actionCell.innerHTML.replace('Activate', 'Suspend').replace('#10b981', '#ef4444');
        }, 500);
    }
}

function changeRole(userId) {
    const newRole = prompt('Enter new role (user, developer, admin):');
    if (newRole && ['user', 'developer', 'admin'].includes(newRole)) {
        if (confirm(`Change user role to "${newRole}"?`)) {
            showNotification('Updating user role...', 'success');
            
            // In a real implementation, make API call here
            setTimeout(() => {
                showNotification('User role updated successfully!', 'success');
                
                const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                const roleCell = row.querySelector('td:nth-child(3) span');
                const roleColors = {
                    'admin': { bg: '#fee2e2', text: '#991b1b' },
                    'developer': { bg: '#dbeafe', text: '#1e40af' },
                    'user': { bg: '#f3f4f6', text: '#374151' }
                };
                const color = roleColors[newRole];
                
                roleCell.style.background = color.bg;
                roleCell.style.color = color.text;
                roleCell.textContent = newRole.charAt(0).toUpperCase() + newRole.slice(1);
            }, 500);
        }
    } else if (newRole) {
        alert('Invalid role. Please use: user, developer, or admin');
    }
}

function createUser() {
    alert('Create user functionality will be implemented in the next update.');
}

function exportUsers() {
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