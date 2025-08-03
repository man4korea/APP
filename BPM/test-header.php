<!-- 📁 C:\xampp\htdocs\BPM\test-header.php -->
<!-- Create at 2508031555 Ver1.00 -->

<?php
/**
 * 헤더 컴포넌트 테스트 페이지
 * 2-2 단계: 헤더 및 EASYCORP 브랜딩 테스트
 */

// 기본 설정 로드
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/core/bootstrap.php';

// 테스트용 변수 설정
$title = 'EASYCORP BPM - 헤더 테스트';
$currentModule = get_current_module();
$moduleData = get_module_info($currentModule);

// 테스트 콘텐츠
$content = '
<div style="padding: 40px; max-width: 1200px; margin: 0 auto;">
    <div style="background: white; padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h1 style="color: #3742fa; margin-bottom: 24px; font-size: 2.25rem; font-weight: 700;">
            🎉 EASYCORP BPM 헤더 테스트
        </h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 32px;">
            <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3742fa;">
                <h3 style="color: #3742fa; margin-bottom: 12px;">✅ 구현 완료 기능</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;">🎨 EASYCORP 로고 및 브랜딩</li>
                    <li style="margin-bottom: 8px;">🔍 전체 검색 버튼</li>
                    <li style="margin-bottom: 8px;">🔔 알림 시스템</li>
                    <li style="margin-bottom: 8px;">⚙️ 설정 버튼</li>
                    <li style="margin-bottom: 8px;">👤 사용자 메뉴 드롭다운</li>
                    <li style="margin-bottom: 8px;">📱 반응형 디자인</li>
                    <li style="margin-bottom: 8px;">🌙 다크모드 지원</li>
                </ul>
            </div>
            
            <div style="padding: 20px; background: #fff5f5; border-radius: 8px; border-left: 4px solid #ff6b6b;">
                <h3 style="color: #ff6b6b; margin-bottom: 12px;">🧪 테스트 기능</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;">🎯 현재 모듈: <strong>' . ($moduleData['name'] ?? '대시보드') . '</strong></li>
                    <li style="margin-bottom: 8px;">🎨 모듈 색상: <span style="display: inline-block; width: 16px; height: 16px; background: ' . ($moduleData['color'] ?? '#3742fa') . '; border-radius: 50%; vertical-align: middle;"></span></li>
                    <li style="margin-bottom: 8px;">👤 사용자: <strong>홍길동 (관리자)</strong></li>
                    <li style="margin-bottom: 8px;">🏢 회사: <strong>EASYCORP</strong></li>
                    <li style="margin-bottom: 8px;">📱 반응형: 브라우저 크기 조정해보세요</li>
                </ul>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; border-radius: 12px; text-align: center;">
            <h2 style="margin-bottom: 16px; font-size: 1.5rem;">🚀 다음 단계: 동적 사이드바 네비게이션</h2>
            <p style="opacity: 0.9; line-height: 1.6;">
                헤더 개발이 완료되었습니다! 다음으로는 2-3 단계인 동적 사이드바 네비게이션을 개발할 예정입니다.<br>
                2단계 메뉴구조 (메인 5개, 서브 각 3개)로 모든 모듈에 접근할 수 있는 사이드바를 구현합니다.
            </p>
        </div>
        
        <div style="margin-top: 32px; padding: 20px; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #3742fa;">
            <h3 style="color: #3742fa; margin-bottom: 12px;">🎮 인터랙션 테스트</h3>
            <p style="margin-bottom: 16px;">헤더의 각 버튼을 클릭해보세요:</p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button onclick="document.getElementById(\'global-search-btn\').click()" style="padding: 8px 16px; background: #3742fa; color: white; border: none; border-radius: 6px; cursor: pointer;">검색 열기</button>
                <button onclick="alert(\'알림 기능은 추후 구현됩니다\')" style="padding: 8px 16px; background: #ffa502; color: white; border: none; border-radius: 6px; cursor: pointer;">알림 테스트</button>
                <button onclick="document.getElementById(\'user-menu-trigger\').click()" style="padding: 8px 16px; background: #2ed573; color: white; border: none; border-radius: 6px; cursor: pointer;">사용자 메뉴</button>
                <button onclick="document.getElementById(\'sidebar-toggle\').click()" style="padding: 8px 16px; background: #ff6b6b; color: white; border: none; border-radius: 6px; cursor: pointer;">사이드바 토글</button>
            </div>
        </div>
    </div>
</div>
';

// 레이아웃에 콘텐츠 포함
include __DIR__ . '/views/layouts/main.php';
?>