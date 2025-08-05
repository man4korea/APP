<?php
$content = ob_start();
?>

<section style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; padding: 2rem 0;">
    <div class="container" style="max-width: 400px;">
        <div class="card">
            <div class="card-header text-center">
                <h1 style="margin: 0; color: #1f2937; font-size: 1.8rem;">Welcome Back</h1>
                <p style="margin: 0.5rem 0 0; color: #6b7280;">Sign in to your AppMart account</p>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?php echo url('/auth/login'); ?>" id="login-form">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($_SESSION['auth_old_input']['email'] ?? ''); ?>"
                            required 
                            autocomplete="email"
                            placeholder="Enter your email"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            required 
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >
                    </div>
                    
                    <div class="form-group" style="display: flex; align-items: center; justify-content: space-between;">
                        <label style="display: flex; align-items: center; font-size: 0.9rem; cursor: pointer;">
                            <input type="checkbox" name="remember" style="margin-right: 0.5rem;">
                            Remember me
                        </label>
                        <a href="#" style="font-size: 0.9rem; color: #3b82f6; text-decoration: none;">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                        Sign In
                    </button>
                </form>
                
                <div class="text-center">
                    <p style="color: #6b7280; font-size: 0.9rem;">
                        Don't have an account? 
                        <a href="<?php echo url('/auth/register'); ?>" style="color: #3b82f6; text-decoration: none; font-weight: 600;">Sign up here</a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Demo Accounts Info -->
        <?php if (config('app.debug')): ?>
        <div class="card" style="margin-top: 1.5rem; background: #fef3c7;">
            <div class="card-body">
                <h4 style="margin: 0 0 1rem; color: #92400e; font-size: 1rem;">Demo Accounts</h4>
                <div style="font-size: 0.85rem; color: #92400e; line-height: 1.6;">
                    <p><strong>Admin:</strong> admin@appmart.com / admin123</p>
                    <p><strong>Developer:</strong> developer@appmart.com / admin123</p>
                    <p><strong>User:</strong> user@appmart.com / admin123</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Clear old input after displaying
unset($_SESSION['auth_old_input']);
?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>