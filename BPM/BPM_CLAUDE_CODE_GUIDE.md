<!-- 📁 C:\xampp\htdocs\BPM\BPM_CLAUDE_CODE_GUIDE.md -->
<!-- Create at 2508022015 Ver1.10 -->

# 🛠️ Claude Code 전용 개발 지침서

## ⚡ 자동 초기화 완료 (Ver 1.10 신규)

### 🎯 초기화 체크리스트 (최종 단계)
```bash
✅ CLAUDE.md 읽기 완료
✅ BPM_PROJECT_GUIDE.md 읽기 완료  
✅ BPM_CLAUDE_CODE_GUIDE.md 읽기 완료 (현재 파일)
🔄 SHRIMP 작업 현황 파악 진행중...
🔄 Git 상태 확인 진행중...
```

**⚡ 다음 즉시 실행 필요:**
1. `shrimp-task-manager:list_tasks all` - 작업 현황 파악
2. `git status` - Git 상태 확인
3. 작업 시작 전 최종 확인 완료

---

## 📋 지침서 동적 로딩

### 자동 로딩 스크립트
```javascript
// 지침서 자동 로딩 함수
async function loadProjectGuides() {
    const fs = require('fs').promises;
    try {
        // 공통 지침서 로딩
        const commonGuide = await fs.readFile('C:\\xampp\\htdocs\\BPM\\BPM_PROJECT_GUIDE.md', 'utf8');
        
        // Claude Code 전용 지침서 로딩 (본 파일)
        const codeGuide = await fs.readFile('C:\\xampp\\htdocs\\BPM\\BPM_CLAUDE_CODE_GUIDE.md', 'utf8');
        
        // 작업 관리 현황 로딩
        const tasks = await fs.readFile('C:\\xampp\\htdocs\\BPM\\SHRIMP_Tasks.md', 'utf8');
        
        console.log('📚 지침서 로딩 완료!');
        return { commonGuide, codeGuide, tasks };
    } catch (error) {
        console.error('❌ 지침서 로딩 실패:', error.message);
        return null;
    }
}

// 사용법: await loadProjectGuides();
```

---

## ⚡ Claude Code 작업 시작법

### 1단계: 환경 확인
```bash
# 프로젝트 디렉토리 이동
cd C:\xampp\htdocs\BPM

# 지침서 최신 확인
ls -la BPM_*_GUIDE.md

# 환경 설정 확인
cat .env
```

### 2단계: 작업 관리 확인
```bash
# 현재 작업 상황
shrimp-task-manager:list_tasks all

# 특정 작업 상세 정보
shrimp-task-manager:get_task_detail [작업ID]
```

### 3단계: 개발 시작
```bash
# 작업 실행
shrimp-task-manager:execute_task [작업ID]
```

---

## 🔧 활용 가능한 MCP 도구

### ⭐ 핵심 도구
- **shrimp-task-manager**: 작업 관리 시스템
- **Filesystem**: 파일 생성/편집/검색
- **text-editor**: 정밀 텍스트 편집

### 🔄 버전 관리
- **git**: Git 명령어 실행
- **github**: GitHub 연동

### 🧪 테스트 및 자동화
- **playwright-stealth**: 웹 테스트 자동화, ** 대안 puppeteer**
- **terminal**: 시스템 명령어 실행
- **desktop-commander**: 시스템 관리

### 🌐 웹 도구
- **web_search**: 웹 검색
- **web_fetch**: 웹 페이지 가져오기

---

## 📁 표준 개발 워크플로우

### Phase 1: 작업 준비
```bash
# 1. 지침서 동적 로딩
await loadProjectGuides()

# 2. 현재 작업 확인
shrimp-task-manager:list_tasks pending

# 3. Git 상태 확인
git status
```

### Phase 2: 개발 진행
```bash
# 1. 작업 시작
shrimp-task-manager:execute_task [작업ID]

# 2. 파일 생성/편집 (표준 헤더 적용)
# 📁 C:\xampp\htdocs\BPM\[경로]\[파일명]
# Create at YYMMDDhhmm Ver1.00

# 3. 실시간 확인
cat [생성파일]
```

### Phase 3: 테스트 및 검증
```bash
# 1. 코드 문법 검사
php -l [PHP파일]
npm run lint

# 2. 자동화 테스트
npm test

# 3. Playwright 테스트 (필요시)
npx playwright test
```

### Phase 4: 버전 관리
```bash
# 1. Git 추가
git add .

# 2. 커밋 (표준 형식)
git commit -m "feat: [작업내용] - [작업ID]"

# 3. 푸시
git push origin main
```

### Phase 5: 배포 및 완료
```bash
# 1. 자동 배포
npm run deploy

# 2. 작업 완료 검증
shrimp-task-manager:verify_task [작업ID]
```

---

## 🎨 모듈별 개발 패턴

### PHP 모듈 개발
```php
<?php
// 📁 C:\xampp\htdocs\BPM\modules\[모듈명]\index.php
// Create at YYMMDDhhmm Ver1.00

/**
 * [모듈명] 모듈 - 메인 컨트롤러
 * 색상 테마: [해당색상] (#색상코드)
 */

// 1. 공통 설정 로드
require_once '../../includes/config.php';
require_once '../../shared/auth.php';

// 2. 모듈별 설정
$module_config = [
    'name' => '[모듈명]',
    'color' => '[색상코드]',
    'theme' => '[테마명].css'
];

// 3. 메인 로직
// 상세한 주석과 함께 구현

// 4. 뷰 렌더링
include 'views/index.php';
?>
```

### CSS 테마 개발
```css
/* 📁 C:\xampp\htdocs\BPM\assets\css\themes\[모듈명].css */
/* Create at YYMMDDhhmm Ver1.00 */

/**
 * [모듈명] 모듈 전용 테마
 * 기본 색상: [색상코드]
 * 배경 색상: [배경색상코드]
 */

:root {
    --module-primary: [기본색상];
    --module-background: [배경색상];
    --module-hover: [호버색상];
}

/* 모듈별 스타일 정의 */
.module-[모듈명] {
    background-color: var(--module-background);
    border-left: 4px solid var(--module-primary);
}
```

### JavaScript 모듈 개발
```javascript
// 📁 C:\xampp\htdocs\BPM\assets\js\modules\[모듈명].js
// Create at YYMMDDhhmm Ver1.00

/**
 * [모듈명] 모듈 JavaScript
 * 담당: 프론트엔드 로직 및 AJAX 통신
 */

class ModuleManager {
    constructor(moduleName, themeColor) {
        this.moduleName = moduleName;
        this.themeColor = themeColor;
        this.init();
    }
    
    /**
     * 모듈 초기화
     */
    init() {
        // 상세 구현 및 주석
    }
}

// 모듈 인스턴스 생성
const moduleInstance = new ModuleManager('[모듈명]', '[색상코드]');
```

---

## 🧪 테스트 자동화 가이드

### Playwright 테스트 스크립트
```javascript
// 📁 C:\xampp\htdocs\BPM\tests\[모듈명]-test.js
// Create at YYMMDDhhmm Ver1.00

const { test, expect } = require('@playwright/test');

test.describe('[모듈명] 모듈 테스트', () => {
    test('모듈 로딩 확인', async ({ page }) => {
        // 1. 페이지 접속
        await page.goto('http://localhost/BPM/modules/[모듈명]');
        
        // 2. 색상 테마 확인
        const element = page.locator('.module-[모듈명]');
        await expect(element).toHaveCSS('background-color', '[RGB값]');
        
        // 3. 기능 테스트
        // 상세 테스트 로직
    });
});
```

### 테스트 실행
```bash
# 전체 테스트
npm test

# 특정 모듈 테스트
npx playwright test tests/[모듈명]-test.js

# 디버그 모드
npx playwright test --debug

# 헤드리스 모드 (백그라운드)
npx playwright test --headed=false
```

---

## 🚀 배포 자동화

### 배포 스크립트 활용
```bash
# 전체 자동 배포 (테스트 포함)
npm run deploy:full

# 테스트 없이 빠른 배포
npm run deploy:quick

# OneDrive 백업만
npm run backup

# 웹호스팅 FTP 배포만
npm run deploy:ftp
```

### 배포 전 체크리스트
```bash
# 1. 문법 오류 확인
find . -name "*.php" -exec php -l {} \;

# 2. 테스트 실행
npm test

# 3. Git 상태 확인
git status

# 4. 환경 설정 확인 (웹호스팅 환경)
cat .env
echo "데이터베이스: bpmapp"
echo "FTP 서버: 112.175.185.148"
echo "사이트 URL: http://bpmapp.dothome.co.kr"

# 5. 권한 확인
ls -la
```

---

## 🆘 문제 해결 가이드

### 일반적인 오류

**XAMPP 연결 문제**:
```bash
# Apache 상태 확인
systemctl status apache2

# MySQL 상태 확인  
systemctl status mysql

# 포트 충돌 확인
netstat -tulpn | grep :80
netstat -tulpn | grep :3306
```

**MCP 도구 오류**:
```bash
# SHRIMP 상태 확인
shrimp-task-manager:list_tasks all

# Filesystem 권한 확인
ls -la C:\xampp\htdocs\BPM

# Git 상태 확인 (Git 경로: C:\Program Files\Git)
"C:\Program Files\Git\bin\git.exe" status
"C:\Program Files\Git\bin\git.exe" remote -v
echo "GitHub 저장소: https://github.com/man4korea/ai-collaboration"
```

**테스트 실패**:
```bash
# Playwright 재설치
npx playwright install --force

# 테스트 로그 확인
cat tests/test-results/*/test-results.json

# 스크린샷 확인
ls tests/screenshots/
```

### 디버그 명령어
```bash
# 상세 로그 출력
DEBUG=* npm test

# PHP 오류 로그
tail -f /var/log/apache2/error.log

# 실시간 파일 감시
tail -f logs/*.log
```

---

## 💡 개발 팁

### 효율적인 작업 관리
1. **작업 시작 전**: 반드시 `SHRIMP_Tasks.md` 확인
2. **파일 생성 시**: 표준 헤더와 상세 주석 필수
3. **테스트 주기**: 기능 완성 시마다 실행
4. **Git 커밋**: 작은 단위로 자주 커밋

### 코드 품질 관리
1. **표준 준수**: 헤더, 색상 테마, 네이밍 규칙
2. **주석 작성**: 비전문가도 이해할 수 있도록
3. **보안 고려**: .env 파일 활용, 입력값 검증
4. **성능 최적화**: 불필요한 쿼리, 파일 로딩 최소화

---

*Last updated: 2025-08-02 20:02 JST*  
*Version: 1.00 - Claude Code 전용*