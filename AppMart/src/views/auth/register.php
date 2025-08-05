<?php
$content = ob_start();
?>

<section style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; padding: 2rem 0;">
    <div class="container" style="max-width: 500px;">
        <div class="card">
            <div class="card-header text-center">
                <h1 style="margin: 0; color: #1f2937; font-size: 1.8rem;">Join AppMart</h1>
                <p style="margin: 0.5rem 0 0; color: #6b7280;">Create your account and start exploring</p>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?php echo url('/auth/register'); ?>" id="register-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                class="form-input" 
                                value="<?php echo htmlspecialchars($_SESSION['auth_old_input']['first_name'] ?? ''); ?>"
                                required 
                                placeholder="John"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                class="form-input" 
                                value="<?php echo htmlspecialchars($_SESSION['auth_old_input']['last_name'] ?? ''); ?>"
                                required 
                                placeholder="Doe"
                            >
                        </div>
                    </div>
                    
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
                            placeholder="john@example.com"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($_SESSION['auth_old_input']['username'] ?? ''); ?>"
                            required 
                            pattern="[a-zA-Z0-9_]+"
                            title="Username can only contain letters, numbers, and underscores"
                            placeholder="johndoe"
                        >
                        <small style="color: #6b7280; font-size: 0.8rem;">Only letters, numbers, and underscores allowed</small>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                required 
                                minlength="6"
                                autocomplete="new-password"
                                placeholder="••••••••"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                required 
                                minlength="6"
                                autocomplete="new-password"
                                placeholder="••••••••"
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">I want to join as</label>
                        <select id="role" name="role" class="form-input form-select" required>
                            <option value="user" <?php echo ($_SESSION['auth_old_input']['role'] ?? '') === 'user' ? 'selected' : ''; ?>>
                                User - Browse and download apps
                            </option>
                            <option value="developer" <?php echo ($_SESSION['auth_old_input']['role'] ?? '') === 'developer' ? 'selected' : ''; ?>>
                                Developer - Upload and sell apps
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; font-size: 0.9rem; cursor: pointer;">
                            <input type="checkbox" name="agree_terms" required style="margin-right: 0.5rem; margin-top: 0.1rem;">
                            <span>I agree to the <a href="#" style="color: #3b82f6; text-decoration: none;">Terms of Service</a> and <a href="#" style="color: #3b82f6; text-decoration: none;">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                        Create Account
                    </button>
                </form>
                
                <div class="text-center">
                    <p style="color: #6b7280; font-size: 0.9rem;">
                        Already have an account? 
                        <a href="<?php echo url('/auth/login'); ?>" style="color: #3b82f6; text-decoration: none; font-weight: 600;">Sign in here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);
});
</script>

<?php
// Clear old input after displaying
unset($_SESSION['auth_old_input']);
?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>