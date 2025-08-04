<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\views\my_apps.php
// Create at 2508041150 Ver1.00

include_once __DIR__ . '/layouts/header.php';

// $apps_data would be passed to this view
$apps_data = $apps_data ?? [];

?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">내 앱 목록</h2>
        <?php if (empty($apps_data)): ?>
            <div class="alert alert-info" role="alert">
                아직 등록된 앱이 없습니다. <a href="/app/register">새 앱을 등록</a>해보세요.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>앱 이름</th>
                        <th>상태</th>
                        <th>가격</th>
                        <th>등록일</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apps_data as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['id']) ?></td>
                            <td><?= htmlspecialchars($app['title']) ?></td>
                            <td><?= htmlspecialchars($app['status']) ?></td>
                            <td><?= htmlspecialchars($app['price']) ?></td>
                            <td><?= htmlspecialchars($app['created_at']) ?></td>
                            <td>
                                <a href="/app/update?id=<?= htmlspecialchars($app['id']) ?>" class="btn btn-sm btn-warning">수정</a>
                                <form action="/app/delete" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($app['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('정말로 삭제하시겠습니까?');">삭제</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
include_once __DIR__ . '/layouts/footer.php';
?>