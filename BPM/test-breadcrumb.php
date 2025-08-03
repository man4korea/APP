<!-- 📁 C:\xampp\htdocs\BPM\test-breadcrumb.php -->
<!-- Create at 2508031635 Ver1.00 -->

<?php
/**
 * 브레드크럼 네비게이션 테스트 페이지
 * 2-4 단계: 브레드크럼 네비게이션 테스트
 */

// 기본 설정 로드
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/core/bootstrap.php';

// 테스트용 변수 설정
$title = 'EASYCORP BPM - 브레드크럼 테스트';
$currentModule = get_current_module();
$moduleData = get_module_info($currentModule);

// 테스트 콘텐츠
$content = '
<div style="padding: 40px; max-width: 1200px; margin: 0 auto;">
    <div style="background: white; padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h1 style="color: #3742fa; margin-bottom: 24px; font-size: 2.25rem; font-weight: 700;">
            🧭 EASYCORP BPM 브레드크럼 네비게이션 테스트
        </h1>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 32px;">
            <div style="padding: 20px; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #3742fa;">
                <h3 style="color: #3742fa; margin-bottom: 12px;">✅ 구현 완료 기능</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;">🏠 현재 위치 자동 감지</li>
                    <li style="margin-bottom: 8px;">🎨 모듈별 동적 색상 테마</li>
                    <li style="margin-bottom: 8px;">📱 반응형 브레드크럼 (모바일 최적화)</li>
                    <li style="margin-bottom: 8px;">🔗 구조화 데이터 (Schema.org)</li>
                    <li style="margin-bottom: 8px;">⭐ 즐겨찾기 기능</li>
                    <li style="margin-bottom: 8px;">🔄 새로고침 버튼</li>
                    <li style="margin-bottom: 8px;">⌨️ 키보드 네비게이션 지원</li>
                    <li style="margin-bottom: 8px;">🌙 다크모드 + 접근성 지원</li>
                </ul>
            </div>
            
            <div style="padding: 20px; background: #fff5f5; border-radius: 8px; border-left: 4px solid #ff6b6b;">
                <h3 style="color: #ff6b6b; margin-bottom: 12px;">🧪 테스트 항목</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 8px;">🎯 현재 모듈: <strong>' . ($moduleData['name'] ?? '대시보드') . '</strong></li>
                    <li style="margin-bottom: 8px;">🎨 모듈 색상: <span style="display: inline-block; width: 16px; height: 16px; background: ' . ($moduleData['color'] ?? '#3742fa') . '; border-radius: 50%; vertical-align: middle;"></span></li>
                    <li style="margin-bottom: 8px;">📍 브레드크럼 경로 추적</li>
                    <li style="margin-bottom: 8px;">🖱️ 호버 인터랙션</li>
                    <li style="margin-bottom: 8px;">⭐ 즐겨찾기 토글</li>
                    <li style="margin-bottom: 8px;">📱 모바일 반응형</li>
                </ul>
            </div>
        </div>
        
        <div style="margin-bottom: 32px;">
            <h2 style="color: #3742fa; margin-bottom: 16px;">🌈 모듈별 색상 테마 테스트</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div style="padding: 16px; background: linear-gradient(135deg, #3742fa 0%, #5a67d8 100%); color: white; border-radius: 8px; text-align: center;">
                    <h4 style="margin-bottom: 8px;">🏠 대시보드</h4>
                    <p style="font-size: 0.875rem; opacity: 0.9;">#3742fa</p>
                </div>
                <div style="padding: 16px; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; border-radius: 8px; text-align: center;">
                    <h4 style="margin-bottom: 8px;">🔴 조직관리</h4>
                    <p style="font-size: 0.875rem; opacity: 0.9;">#ff6b6b</p>
                </div>
                <div style="padding: 16px; background: linear-gradient(135deg, #ff9f43 0%, #f39c12 100%); color: white; border-radius: 8px; text-align: center;">
                    <h4 style="margin-bottom: 8px;">🟠 구성원관리</h4>
                    <p style="font-size: 0.875rem; opacity: 0.9;">#ff9f43</p>
                </div>
                <div style="padding: 16px; background: linear-gradient(135deg, #feca57 0%, #f1c40f 100%); color: #333; border-radius: 8px; text-align: center;">
                    <h4 style="margin-bottom: 8px;">🟡 업무관리</h4>
                    <p style="font-size: 0.875rem; opacity: 0.8;">#feca57</p>
                </div>
                <div style="padding: 16px; background: linear-gradient(135deg, #55a3ff 0%, #3498db 100%); color: white; border-radius: 8px; text-align: center;">
                    <h4 style="margin-bottom: 8px;">🔵 운영관리</h4>
                    <p style="font-size: 0.875rem; opacity: 0.9;">#55a3ff</p>
                </div>
            </div>
        </div>
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; border-radius: 12px; text-align: center; margin-bottom: 32px;">
            <h2 style="margin-bottom: 16px; font-size: 1.5rem;">🚀 다음 단계: 레이아웃 통합 및 테마전환 완성</h2>
            <p style="opacity: 0.9; line-height: 1.6;">
                브레드크럼 개발이 완료되었습니다! 다음으로는 2-5 단계인 레이아웃 통합 및 테마전환 시스템을 완성할 예정입니다.<br>
                모든 컴포넌트가 통합된 완전한 UI 시스템을 구현합니다.
            </p>
        </div>
        
        <div style="margin-bottom: 32px; padding: 20px; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #3742fa;">
            <h3 style="color: #3742fa; margin-bottom: 12px;">🎮 브레드크럼 인터랙션 테스트</h3>
            <p style="margin-bottom: 16px;">브레드크럼의 각 기능을 테스트해보세요:</p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button onclick="testModuleTheme(\"dashboard\")" style="padding: 8px 16px; background: #3742fa; color: white; border: none; border-radius: 6px; cursor: pointer;">대시보드 테마</button>
                <button onclick="testModuleTheme(\"organization\")" style="padding: 8px 16px; background: #ff6b6b; color: white; border: none; border-radius: 6px; cursor: pointer;">조직관리 테마</button>
                <button onclick="testModuleTheme(\"workflow\")" style="padding: 8px 16px; background: #feca57; color: #333; border: none; border-radius: 6px; cursor: pointer;">업무관리 테마</button>
                <button onclick="document.getElementById(\"bookmark-toggle\").click()" style="padding: 8px 16px; background: #fbbf24; color: white; border: none; border-radius: 6px; cursor: pointer;">즐겨찾기 토글</button>
                <button onclick="location.reload()" style="padding: 8px 16px; background: #2ed573; color: white; border: none; border-radius: 6px; cursor: pointer;">새로고침 테스트</button>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="padding: 16px; background: #fff9c4; border-radius: 8px; border: 1px solid #f59e0b;">
                <h4 style="color: #92400e; margin-bottom: 8px;">📱 모바일 테스트</h4>
                <p style="font-size: 0.875rem; color: #92400e; margin: 0;">
                    브라우저 창을 좁게 만들어 모바일 반응형을 확인하세요. 첫 번째와 마지막 브레드크럼만 표시됩니다.
                </p>
            </div>
            <div style="padding: 16px; background: #f0fff4; border-radius: 8px; border: 1px solid #10b981;">
                <h4 style="color: #047857; margin-bottom: 8px;">⌨️ 키보드 테스트</h4>
                <p style="font-size: 0.875rem; color: #047857; margin: 0;">
                    Tab 키로 브레드크럼 이동, 화살표 키로 네비게이션, Home/End 키로 처음/끝 이동이 가능합니다.
                </p>
            </div>
            <div style="padding: 16px; background: #fef3c7; border-radius: 8px; border: 1px solid #f59e0b;">
                <h4 style="color: #92400e; margin-bottom: 8px;">⭐ 즐겨찾기 테스트</h4>
                <p style="font-size: 0.875rem; color: #92400e; margin: 0;">
                    브레드크럼 우측의 별표 버튼을 클릭하여 현재 페이지를 즐겨찾기에 추가/제거할 수 있습니다.
                </p>
            </div>
        </div>
        
        <div style="margin-top: 24px; padding: 20px; background: #e0f2fe; border-radius: 8px; border: 1px solid #0ea5e9;">
            <h3 style="color: #0c4a6e; margin-bottom: 12px;">💡 브레드크럼 기능 가이드</h3>
            <ul style="margin: 0; padding-left: 20px; color: #0c4a6e;">
                <li style="margin-bottom: 8px;">현재 위치가 자동으로 감지되어 브레드크럼에 표시됩니다</li>
                <li style="margin-bottom: 8px;">각 모듈별로 다른 색상 테마가 적용됩니다</li>
                <li style="margin-bottom: 8px;">브레드크럼 링크를 클릭하여 상위 페이지로 이동할 수 있습니다</li>
                <li style="margin-bottom: 8px;">모바일에서는 첫 번째와 마지막 항목만 표시됩니다</li>
                <li style="margin-bottom: 8px;">즐겨찾기 기능으로 자주 방문하는 페이지를 저장할 수 있습니다</li>
                <li style="margin-bottom: 8px;">Schema.org 구조화 데이터로 SEO가 최적화되어 있습니다</li>
            </ul>
        </div>
        
        <div style="margin-top: 24px; padding: 20px; background: #f3f4f6; border-radius: 8px; text-align: center;">
            <h3 style="color: #374151; margin-bottom: 12px;">🔧 개발자 정보</h3>
            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">
                브레드크럼 컴포넌트: <code>views/components/breadcrumb.php</code><br>
                스타일시트: <code>assets/css/breadcrumb.css</code><br>
                JavaScript: <code>assets/js/breadcrumb.js</code><br>
                총 구현 라인 수: 약 900줄 (PHP + CSS + JS)
            </p>
        </div>
    </div>
</div>

<script>
// 모듈 테마 테스트 함수
function testModuleTheme(module) {
    if (window.updateBreadcrumbTheme) {
        window.updateBreadcrumbTheme(module);
        
        // 시각적 피드백
        const colors = {
            "dashboard": "#3742fa",
            "organization": "#ff6b6b", 
            "workflow": "#feca57"
        };
        
        document.body.style.borderTop = `4px solid ${colors[module]}`;
        setTimeout(() => {
            document.body.style.borderTop = "";
        }, 2000);
        
        console.log(`브레드크럼 테마 변경: ${module}`);
    }
}

// 페이지 로드시 브레드크럼 초기화 확인
document.addEventListener("DOMContentLoaded", function() {
    console.log("브레드크럼 테스트 페이지 로드 완료");
    
    // 브레드크럼 존재 확인
    const breadcrumb = document.querySelector(".breadcrumb-nav");
    if (breadcrumb) {
        console.log("✅ 브레드크럼 컴포넌트 정상 로드됨");
        
        // 브레드크럼 아이템 개수 확인
        const items = breadcrumb.querySelectorAll(".breadcrumb-item");
        console.log(`📍 브레드크럼 아이템 수: ${items.length}개`);
        
        // 현재 모듈 확인
        const currentModule = breadcrumb.dataset.module;
        console.log(`🎨 현재 모듈: ${currentModule}`);
    } else {
        console.error("❌ 브레드크럼 컴포넌트 로드 실패");
    }
    
    // JavaScript 기능 확인
    if (window.breadcrumbManager || window.updateBreadcrumbTheme) {
        console.log("✅ 브레드크럼 JavaScript 기능 정상 로드됨");
    } else {
        console.warn("⚠️ 브레드크럼 JavaScript 기능 로드 확인 필요");
    }
});
</script>
';

// 레이아웃에 콘텐츠 포함
include __DIR__ . '/views/layouts/main.php';
?>