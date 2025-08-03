<!-- 📁 C:\xampp\htdocs\BPM\test-sidebar.php -->
<!-- Create at 2508031610 Ver1.00 -->

<?php
/**
 * 사이드바 네비게이션 테스트 페이지
 * 2-3 단계: 동적 사이드바 네비게이션 테스트
 */

// 기본 설정 로드
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/core/bootstrap.php';

// 테스트용 변수 설정
$title = 'EASYCORP BPM - 사이드바 테스트';
$currentModule = get_current_module();
$moduleData = get_module_info($currentModule);

// 테스트 콘텐츠
$content = '
<div style="padding: 40px; max-width: 1200px; margin: 0 auto;">
    <div style="background: white; padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h1 style="color: #3742fa; margin-bottom: 24px; font-size: 2.25rem; font-weight: 700;">
            🎉 EASYCORP BPM 사이드바 네비게이션 테스트
        </h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 32px;">
            <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3742fa;">
                <h3 style="color: #3742fa; margin-bottom: 12px;">✅ 구현 완료 기능</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;">📋 2단계 메뉴 구조 (5개 메인, 15개 서브)</li>
                    <li style="margin-bottom: 8px;">🎨 10개 모듈별 색상 테마</li>
                    <li style="margin-bottom: 8px;">📱 반응형 사이드바 (접힘/펼침)</li>
                    <li style="margin-bottom: 8px;">🔧 동적 메뉴 토글 애니메이션</li>
                    <li style="margin-bottom: 8px;">👤 권한별 메뉴 필터링</li>
                    <li style="margin-bottom: 8px;">🌙 다크모드 + 접근성 지원</li>
                    <li style="margin-bottom: 8px;">📍 활성 메뉴 상태 표시</li>
                </ul>
            </div>
            
            <div style="padding: 20px; background: #fff5f5; border-radius: 8px; border-left: 4px solid #ff6b6b;">
                <h3 style="color: #ff6b6b; margin-bottom: 12px;">🧪 테스트 항목</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;">🎯 현재 모듈: <strong>' . ($moduleData['name'] ?? '대시보드') . '</strong></li>
                    <li style="margin-bottom: 8px;">🎨 모듈 색상: <span style="display: inline-block; width: 16px; height: 16px; background: ' . ($moduleData['color'] ?? '#3742fa') . '; border-radius: 50%; vertical-align: middle;"></span></li>
                    <li style="margin-bottom: 8px;">📂 메인 메뉴: 5개 (대시보드, 조직, 구성원, 업무, 운영)</li>
                    <li style="margin-bottom: 8px;">📄 서브 메뉴: 15개 (각 메인당 3개)</li>
                    <li style="margin-bottom: 8px;">📱 반응형: 브라우저 크기 조정해보세요</li>
                </ul>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="padding: 16px; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; border-radius: 8px;">
                <h4 style="margin-bottom: 8px;">🔴 조직관리</h4>
                <p style="font-size: 0.875rem; opacity: 0.9;">회사 관리, 부서 관리, 조직도</p>
            </div>
            <div style="padding: 16px; background: linear-gradient(135deg, #ff9f43 0%, #f39c12 100%); color: white; border-radius: 8px;">
                <h4 style="margin-bottom: 8px;">🟠 구성원관리</h4>
                <p style="font-size: 0.875rem; opacity: 0.9;">사용자 관리, 권한 관리, 초대 관리</p>
            </div>
            <div style="padding: 16px; background: linear-gradient(135deg, #feca57 0%, #f1c40f 100%); color: #333; border-radius: 8px;">
                <h4 style="margin-bottom: 8px;">🟡 업무관리</h4>
                <p style="font-size: 0.875rem; opacity: 0.8;">Task 관리, Process Map, 업무 Flow</p>
            </div>
            <div style="padding: 16px; background: linear-gradient(135deg, #55a3ff 0%, #3498db 100%); color: white; border-radius: 8px;">
                <h4 style="margin-bottom: 8px;">🔵 운영관리</h4>
                <p style="font-size: 0.875rem; opacity: 0.9;">문서 관리, 직무 분석, 인사 관리</p>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; border-radius: 12px; text-align: center;">
            <h2 style="margin-bottom: 16px; font-size: 1.5rem;">🚀 다음 단계: 브레드크럼 네비게이션</h2>
            <p style="opacity: 0.9; line-height: 1.6;">
                사이드바 개발이 완료되었습니다! 다음으로는 2-4 단계인 브레드크럼 네비게이션을 개발할 예정입니다.<br>
                현재 위치를 명확히 표시하는 브레드크럼 시스템을 구현합니다.
            </p>
        </div>
        
        <div style="margin-top: 32px; padding: 20px; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #3742fa;">
            <h3 style="color: #3742fa; margin-bottom: 12px;">🎮 인터랙션 테스트</h3>
            <p style="margin-bottom: 16px;">사이드바의 각 기능을 테스트해보세요:</p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button onclick="document.getElementById(\'sidebar-toggle-main\').click()" style="padding: 8px 16px; background: #3742fa; color: white; border: none; border-radius: 6px; cursor: pointer;">사이드바 토글</button>
                <button onclick="document.querySelector(\'.nav-item[data-module=\\\"organization\\\"] .main-nav-link\').click()" style="padding: 8px 16px; background: #ff6b6b; color: white; border: none; border-radius: 6px; cursor: pointer;">조직관리 메뉴</button>
                <button onclick="document.querySelector(\'.nav-item[data-module=\\\"workflow\\\"] .main-nav-link\').click()" style="padding: 8px 16px; background: #feca57; color: #333; border: none; border-radius: 6px; cursor: pointer;">업무관리 메뉴</button>
                <button onclick="window.innerWidth <= 768 ? document.getElementById(\'sidebar-overlay\').click() : alert(\'데스크톱에서는 오버레이가 표시되지 않습니다\')" style="padding: 8px 16px; background: #2ed573; color: white; border: none; border-radius: 6px; cursor: pointer;">오버레이 테스트</button>
            </div>
        </div>
        
        <div style="margin-top: 24px; padding: 20px; background: #fff9c4; border-radius: 8px; border: 1px solid #f59e0b;">
            <h3 style="color: #92400e; margin-bottom: 12px;">💡 사용 팁</h3>
            <ul style="margin: 0; padding-left: 20px; color: #92400e;">
                <li style="margin-bottom: 8px;">사이드바 상단의 토글 버튼으로 접힘/펼침 상태를 변경할 수 있습니다</li>
                <li style="margin-bottom: 8px;">메인 메뉴를 클릭하면 서브메뉴가 펼쳐집니다</li>
                <li style="margin-bottom: 8px;">모바일에서는 헤더의 햄버거 메뉴로 사이드바를 열 수 있습니다</li>
                <li style="margin-bottom: 8px;">접힌 상태에서 메뉴에 마우스를 올리면 툴팁이 표시됩니다</li>
                <li style="margin-bottom: 8px;">각 모듈별로 다른 색상 테마가 적용됩니다</li>
            </ul>
        </div>
    </div>
</div>
';

// 레이아웃에 콘텐츠 포함
include __DIR__ . '/views/layouts/main.php';
?>