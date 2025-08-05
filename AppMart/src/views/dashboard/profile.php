<?php
$content = ob_start();
?>

<section style="padding: 2rem 0; background: #f8fafc;">
    <div class="container">
        <!-- Page Header -->
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <a href="<?php echo url('/dashboard'); ?>" style="color: #6b7280; text-decoration: none; font-size: 1.2rem;">←</a>
                <h1 style="font-size: 2rem; font-weight: bold; color: #1f2937; margin: 0;">My Profile</h1>
            </div>
            <p style="color: #6b7280; margin: 0;">Manage your account information and settings</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h3>Profile Information</h3>
                </div>
                <div class="card-body">
                    <!-- Flash Messages -->
                    <?php if (isset($_SESSION['profile_success'])): ?>
                        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
                            <?php echo htmlspecialchars($_SESSION['profile_success']); ?>
                            <?php unset($_SESSION['profile_success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['profile_errors'])): ?>
                        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #fecaca;">
                            <ul style="margin: 0; padding-left: 1rem;">
                                <?php foreach ($_SESSION['profile_errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php unset($_SESSION['profile_errors']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo url('/dashboard/profile'); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        
                        <!-- Profile Picture Placeholder -->
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: bold;">
                                <?php echo strtoupper(substr($user_data['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-weight: 600;">@<?php echo htmlspecialchars($user_data['username']); ?></h4>
                                <p style="margin: 0; color: #6b7280; text-transform: capitalize;"><?php echo htmlspecialchars($user_data['role']); ?> Account</p>
                                <button type="button" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.25rem 0.75rem; margin-top: 0.5rem;" disabled>
                                    Change Photo (Coming Soon)
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2" style="gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    class="form-input" 
                                    required
                                    value="<?php echo htmlspecialchars($user_data['first_name'] ?? ''); ?>"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    class="form-input" 
                                    required
                                    value="<?php echo htmlspecialchars($user_data['last_name'] ?? ''); ?>"
                                >
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="email" class="form-label">Email Address</label>
                            <input 
                                type="email" 
                                id="email" 
                                class="form-input" 
                                disabled
                                value="<?php echo htmlspecialchars($user_data['email']); ?>"
                                style="background: #f9fafb; color: #6b7280;"
                            >
                            <small style="color: #6b7280; font-size: 0.8rem;">Email cannot be changed. Contact support if needed.</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea 
                                id="bio" 
                                name="bio" 
                                class="form-input" 
                                rows="4"
                                placeholder="Tell others about yourself..."
                                style="resize: vertical; min-height: 100px;"
                            ><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="website" class="form-label">Website</label>
                            <input 
                                type="url" 
                                id="website" 
                                name="website" 
                                class="form-input" 
                                placeholder="https://your-website.com"
                                value="<?php echo htmlspecialchars($user_data['website'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label for="github_username" class="form-label">GitHub Username</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 0.9rem;">github.com/</span>
                                <input 
                                    type="text" 
                                    id="github_username" 
                                    name="github_username" 
                                    class="form-input" 
                                    placeholder="username"
                                    style="padding-left: 5.5rem;"
                                    value="<?php echo htmlspecialchars($user_data['github_username'] ?? ''); ?>"
                                >
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                💾 Save Changes
                            </button>
                            <a href="<?php echo url('/dashboard'); ?>" class="btn btn-outline" style="flex: 1; text-align: center;">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card">
                <div class="card-header">
                    <h3>Security Settings</h3>
                </div>
                <div class="card-body">
                    <!-- Password Change Flash Messages -->
                    <?php if (isset($_SESSION['password_success'])): ?>
                        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
                            <?php echo htmlspecialchars($_SESSION['password_success']); ?>
                            <?php unset($_SESSION['password_success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['password_errors'])): ?>
                        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border: 1px solid #fecaca;">
                            <ul style="margin: 0; padding-left: 1rem;">
                                <?php foreach ($_SESSION['password_errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php unset($_SESSION['password_errors']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Account Info -->
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="color: #6b7280; font-size: 0.9rem;">Account created:</span>
                            <span style="font-weight: 500;"><?php echo date('M j, Y', strtotime($user_data['created_at'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="color: #6b7280; font-size: 0.9rem;">Last updated:</span>
                            <span style="font-weight: 500;"><?php echo date('M j, Y', strtotime($user_data['updated_at'])); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280; font-size: 0.9rem;">Account status:</span>
                            <span style="background: #dcfce7; color: #166534; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; font-weight: 600;">Active</span>
                        </div>
                    </div>

                    <!-- Change Password Form -->
                    <form method="POST" action="<?php echo url('/dashboard/change-password'); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                class="form-input" 
                                required
                                placeholder="Enter your current password"
                            >
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
                                class="form-input" 
                                required
                                placeholder="Enter new password (min 6 characters)"
                                minlength="6"
                            >
                        </div>

                        <div class="form-group" style="margin-bottom: 2rem;">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                required
                                placeholder="Confirm your new password"
                                minlength="6"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                            🔒 Change Password
                        </button>
                    </form>

                    <hr style="border: none; height: 1px; background: #e5e7eb; margin: 1.5rem 0;">

                    <!-- Additional Security Options -->
                    <div>
                        <h4 style="font-weight: 600; margin-bottom: 1rem; color: #374151;">Additional Security</h4>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <button class="btn btn-outline" style="width: 100%; text-align: center;" disabled>
                                🔐 Enable Two-Factor Authentication (Coming Soon)
                            </button>
                            <button class="btn btn-outline" style="width: 100%; text-align: center;" disabled>
                                📱 Manage Login Sessions (Coming Soon)
                            </button>
                            <button class="btn btn-outline" style="width: 100%; text-align: center; color: #ef4444; border-color: #ef4444;" onclick="deleteAccount()">
                                🗑️ Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword && confirmPassword && newPassword !== confirmPassword) {
        this.style.borderColor = '#ef4444';
        this.style.background = '#fef2f2';
    } else {
        this.style.borderColor = '';
        this.style.background = '';
    }
});

function deleteAccount() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone and will permanently remove all your data.')) {
        if (confirm('This will permanently delete your account, all your applications (if you\'re a developer), and purchase history. Type "DELETE" to confirm.')) {
            const confirmation = prompt('Please type "DELETE" to confirm account deletion:');
            if (confirmation === 'DELETE') {
                alert('Account deletion feature will be implemented in the next update. Please contact support to delete your account.');
                // In a real implementation, this would redirect to account deletion endpoint
                // window.location.href = '/dashboard/delete-account';
            }
        }
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>