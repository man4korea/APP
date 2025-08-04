<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\views\app_list.php
// Create at 2508041155 Ver1.00

include_once __DIR__ . '/layouts/header.php';

// $apps_data would be passed to this view
$apps_data = $apps_data ?? [];

?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">모든 앱</h2>
        <?php if (empty($apps_data)): ?>
            <div class="alert alert-info" role="alert">
                아직 승인된 앱이 없습니다.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($apps_data as $app): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="<?= htmlspecialchars($app['thumbnail'] ?? 'https://via.placeholder.com/150') ?>" class="card-img-top" alt="...">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($app['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars(mb_strimwidth($app['description'], 0, 100, '...', 'UTF-8')) ?></p>
                                <p class="card-text"><small class="text-muted">가격: <?= htmlspecialchars($app['price']) === '0' ? '무료' : htmlspecialchars($app['price']) . '원' ?></small></p>
                                <a href="/app/detail?id=<?= htmlspecialchars($app['id']) ?>" class="btn btn-primary">상세보기</a>
                            </div>
                            <div class="card-footer">
                                <small class="text-muted">기술스택: <?= htmlspecialchars($app['tech_stack']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include_once __DIR__ . '/layouts/footer.php';
?>