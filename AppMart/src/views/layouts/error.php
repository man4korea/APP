<?php
$content = ob_start();
?>

<section style="min-height: 70vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; padding: 2rem 0;">
    <div class="container text-center" style="max-width: 600px;">
        <div style="font-size: 8rem; margin-bottom: 1rem;">
            <?php
            $emoji = match($code ?? 404) {
                404 => '🔍',
                403 => '🚫',
                500 => '⚠️',
                503 => '🔧',
                default => '❓'
            };
            echo $emoji;
            ?>
        </div>
        
        <h1 style="font-size: 3rem; font-weight: bold; color: #1f2937; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($title ?? 'Error'); ?>
        </h1>
        
        <p style="font-size: 1.2rem; color: #6b7280; margin-bottom: 2rem; line-height: 1.6;">
            <?php echo htmlspecialchars($message ?? 'An unexpected error occurred.'); ?>
        </p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo url('/'); ?>" class="btn btn-primary">Go Home</a>
            <a href="javascript:history.back()" class="btn btn-outline">Go Back</a>
            
            <?php if (($code ?? 404) === 404): ?>
                <a href="<?php echo url('/apps'); ?>" class="btn btn-secondary">Browse Apps</a>
            <?php endif; ?>
        </div>
        
        <?php if (config('app.debug') && isset($debug_info)): ?>
            <div style="margin-top: 3rem; padding: 1.5rem; background: #fef2f2; border-radius: 0.5rem; text-align: left;">
                <h3 style="color: #dc2626; margin-bottom: 1rem; font-size: 1.1rem;">Debug Information</h3>
                <pre style="background: #1f2937; color: #f9fafb; padding: 1rem; border-radius: 0.25rem; overflow-x: auto; font-size: 0.8rem;">
<?php echo htmlspecialchars($debug_info); ?>
                </pre>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/app.php';
?>