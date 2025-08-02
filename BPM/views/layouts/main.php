<!-- 📁 C:\xampp\htdocs\BPM\views\layouts\main.php -->
<!-- Create at 2508022322 Ver1.00 -->

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $title ?? 'BPM Total Business Process Management' ?></title>
    
    <!-- CSS 파일 로드 -->
    <link rel="stylesheet" href="<?= asset_path('css/base.css') ?>">
    <link rel="stylesheet" href="<?= asset_path('css/variables.css') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset_path('images/favicon.png') ?>">
    
    <!-- 추가 CSS -->
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= asset_path("css/{$style}.css") ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- CSRF 토큰 -->
    <meta name="csrf-token" content="<?= Security::getInstance()->generateCSRFToken() ?>">
</head>
<body class="<?= $bodyClass ?? '' ?>">
    <!-- 메인 레이아웃 컨테이너 -->
    <div id="app" class="app-container">
        
        <!-- 헤더 영역 (추후 추가) -->
        <?php if (!isset($hideHeader) || !$hideHeader): ?>
            <!-- header 컴포넌트는 다음 작업에서 추가 -->
        <?php endif; ?>
        
        <!-- 사이드바 영역 (추후 추가) -->
        <?php if (!isset($hideSidebar) || !$hideSidebar): ?>
            <!-- sidebar 컴포넌트는 다음 작업에서 추가 -->
        <?php endif; ?>
        
        <!-- 메인 콘텐츠 영역 -->
        <main id="main-content" class="main-content">
            
            <!-- 브레드크럼 영역 (추후 추가) -->
            <?php if (!isset($hideBreadcrumb) || !$hideBreadcrumb): ?>
                <!-- breadcrumb 컴포넌트는 다음 작업에서 추가 -->
            <?php endif; ?>
            
            <!-- 페이지 콘텐츠 -->
            <div class="page-content">
                <?= $content ?? '' ?>
            </div>
            
        </main>
        
    </div>
    
    <!-- JavaScript 파일 로드 -->
    <script>
        // 전역 설정
        window.BPM = {
            baseUrl: '<?= APP_URL ?>',
            apiUrl: '<?= APP_URL ?>/api/v1',
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            currentUser: <?= json_encode(current_user()) ?>,
            debug: <?= APP_DEBUG ? 'true' : 'false' ?>
        };
    </script>
    
    <!-- 기본 JavaScript -->
    <script src="<?= asset_path('js/app.js') ?>"></script>
    
    <!-- 추가 JavaScript -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= asset_path("js/{$script}.js") ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- 페이지별 추가 스크립트 -->
    <?php if (isset($pageScript)): ?>
        <script><?= $pageScript ?></script>
    <?php endif; ?>
    
</body>
</html>