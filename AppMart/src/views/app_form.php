<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\views\app_form.php
// Create at 2508041140 Ver1.00

include_once __DIR__ . '/layouts/header.php';

// For editing, $app_data would be passed to this view
$app_data = $app_data ?? null;

?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><?= $app_data ? '앱 수정' : '앱 등록' ?></div>
            <div class="card-body">
                <form action="<?= $app_data ? '/app/update?id=' . $app_data['id'] : '/app/register' ?>" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">앱 이름</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= $app_data['title'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">설명</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?= $app_data['description'] ?? '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="tech_stack" class="form-label">기술 스택 (쉼표로 구분)</label>
                        <input type="text" class="form-control" id="tech_stack" name="tech_stack" value="<?= $app_data['tech_stack'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="db_type" class="form-label">사용 DB</label>
                        <input type="text" class="form-control" id="db_type" name="db_type" value="<?= $app_data['db_type'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="file_url" class="form-label">파일 또는 링크</label>
                        <input type="text" class="form-control" id="file_url" name="file_url" value="<?= $app_data['file_url'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="thumbnail" class="form-label">썸네일 URL</label>
                        <input type="text" class="form-control" id="thumbnail" name="thumbnail" value="<?= $app_data['thumbnail'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">가격 (0 입력 시 무료)</label>
                        <input type="number" class="form-control" id="price" name="price" value="<?= $app_data['price'] ?? 0 ?>" required min="0">
                    </div>
                    <button type="submit" class="btn btn-primary"><?= $app_data ? '앱 수정' : '앱 등록' ?></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/layouts/footer.php';
?>