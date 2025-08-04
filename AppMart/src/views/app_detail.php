<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\views\app_detail.php
// Create at 2508041156 Ver1.00

include_once __DIR__ . '/layouts/header.php';

// $app_data would be passed to this view
$app_data = $app_data ?? null;

if (!$app_data) {
    echo "<div class=\"alert alert-danger\" role=\"alert\">앱을 찾을 수 없습니다.</div>";
    include_once __DIR__ . '/layouts/footer.php';
    exit();
}

?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card mb-3">
            <img src="<?= htmlspecialchars($app_data['thumbnail'] ?? 'https://via.placeholder.com/300') ?>" class="card-img-top" alt="...">
            <div class="card-body">
                <h1 class="card-title"><?= htmlspecialchars($app_data['title']) ?></h1>
                <p class="card-text"><strong>기술 스택:</strong> <?= htmlspecialchars($app_data['tech_stack']) ?></p>
                <p class="card-text"><strong>사용 DB:</strong> <?= htmlspecialchars($app_data['db_type']) ?></p>
                <p class="card-text"><strong>가격:</strong> <?= htmlspecialchars($app_data['price']) === '0' ? '무료' : htmlspecialchars($app_data['price']) . '원' ?></p>
                <hr>
                <p class="card-text"><?= nl2br(htmlspecialchars($app_data['description'])) ?></p>
                
                <?php if ($app_data['price'] == 0): // 무료 앱인 경우 다운로드 버튼 표시 ?>
                    <a href="/app/download?id=<?= htmlspecialchars($app_data['id']) ?>" class="btn btn-success">다운로드</a>
                <?php else: // 유료 앱인 경우 결제 버튼 표시 (향후 구현) ?>
                    <button class="btn btn-primary" disabled>결제하기 (준비중)</button>
                <?php endif; ?>

                <a href="/" class="btn btn-secondary">목록으로 돌아가기</a>
            </div>
            <div class="card-footer text-muted">
                등록일: <?= htmlspecialchars($app_data['created_at']) ?>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/layouts/footer.php';
?>
