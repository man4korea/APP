<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\views\admin\pending_apps.php
// Create at 2508041200 Ver1.00

include_once __DIR__ . '/../layouts/header.php';

// $pending_apps_data would be passed to this view
$pending_apps_data = $pending_apps_data ?? [];

?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">승인 대기 앱 목록</h2>
        <?php if (empty($pending_apps_data)): ?>
            <div class="alert alert-info" role="alert">
                현재 승인 대기중인 앱이 없습니다.
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>앱 이름</th>
                        <th>개발자 ID</th>
                        <th>등록일</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_apps_data as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['id']) ?></td>
                            <td><a href="/app/detail?id=<?= htmlspecialchars($app['id']) ?>"><?= htmlspecialchars($app['title']) ?></a></td>
                            <td><?= htmlspecialchars($app['owner_id']) ?></td>
                            <td><?= htmlspecialchars($app['created_at']) ?></td>
                            <td>
                                <form action="/admin/approve" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($app['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-success">승인</button>
                                </form>
                                <form action="/admin/reject" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($app['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">반려</button>
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
include_once __DIR__ . '/../layouts/footer.php';
?>