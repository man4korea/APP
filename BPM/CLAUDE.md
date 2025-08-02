<!-- 📁 C:\xampp\htdocs\BPM\CLAUDE.md -->
<!-- Create at 2508022015 Ver3.10 -->

# 📋 Claude Code & Desktop 공통 연결 가이드

## ⚡ 자동 초기화 지침 (Ver 3.10 신규)

### 🚀 BPM 작업폴더 접속 시 필수 수행 사항

**Claude Code/Desktop 모두 반드시 실행:**

1. **📚 지침서 3종 자동 로딩**
   ```bash
   # 순서대로 읽어들이기
   1. CLAUDE.md (본 파일 - 연결 가이드)
   2. BPM_PROJECT_GUIDE.md (공통 지침서)  
   3. BPM_CLAUDE_CODE_GUIDE.md (Claude Code 전용)
   ```

2. **📊 작업 현황 자동 파악 (이중 확인 시스템)**
   ```bash
   # 1차: SHRIMP 시스템에서 작업 확인
   shrimp-task-manager:list_tasks all
   
   # 2차: 로컬 SHRIMP_Tasks.md 파일 확인 (마스터 기준)
   Read: C:\xampp\htdocs\BPM\SHRIMP_Tasks.md
   ```

3. **🔄 현재 Git 상태 확인**
   ```bash
   git status
   ```

4. **✅ 초기화 완료 확인**
   - 3개 지침서 내용 숙지 완료
   - 현재 진행중/대기중 작업 파악 완료 (SHRIMP_Tasks.md 기준)
   - Git 상태 및 변경사항 확인 완료

**⚠️ 중요: 위 4단계 완료 전까지는 어떤 작업도 시작하지 말 것**

## 🎯 지침서 구조 (Ver 3.00 업데이트)

### 📚 지침서 체계
```
BPM/
├── BPM_PROJECT_GUIDE.md          # 📚 공통 지침서 (기본 참조)
├── BPM_CLAUDE_CODE_GUIDE.md      # 🛠️ Claude Code 전용
├── BPM_CLAUDE_DESKTOP_GUIDE.md   # 🖥️ Claude Desktop 전용
├── CLAUDE.md                     # 📋 본 파일 (연결 가이드)
├── SHRIMP_Tasks.md               # 📋 마스터 작업 관리 파일
└── scripts/load-guides.js        # ⚡ 동적 로딩 스크립트
```

### 📋 작업 관리 시스템 (Ver 3.10 신규)

**마스터 작업 파일**: `C:\xampp\htdocs\BPM\SHRIMP_Tasks.md`
- 모든 프로젝트 작업의 **마스터 기준점**
- SHRIMP 시스템과 독립적으로 동작
- 상세한 작업 설명, 의존성, 구현 가이드 포함
- 작업 상태 및 진행상황 실시간 업데이트

**작업 관리 원칙**:
1. **SHRIMP_Tasks.md가 최우선 기준**: SHRIMP 시스템보다 우선
2. **모든 작업 변경은 SHRIMP_Tasks.md 먼저 업데이트**
3. **SHRIMP 시스템은 보조적 역할**: 세부 작업 추적용
4. **작업 시작 전 필수 확인**: SHRIMP_Tasks.md의 최신 내용 확인

### 🔄 동적 로딩 시스템

#### Claude Code에서 사용법
```bash
# 프로젝트 디렉토리 이동
cd C:\xampp\htdocs\BPM

# Node.js 환경에서 지침서 로딩
node -e "
const GuideLoader = require('./scripts/load-guides.js');
loadProjectGuides().then(({guides, loader}) => {
    console.log('지침서 로딩 완료!');
});
"

# 또는 간단한 함수 호출
node -e "require('./scripts/load-guides.js'); loadProjectGuides();"
```

#### 빠른 확인 명령어
```bash
# 작업 현황만 확인
node -e "require('./scripts/load-guides.js'); checkTaskStatus();"

# 색상 테마만 확인  
node -e "require('./scripts/load-guides.js'); getModuleColors();"
```

---

## 🎭 역할 분담 시스템

### 🛠️ Claude Code 담당 영역
- **실제 개발**: 파일 생성/편집, 코드 작성
- **테스트 실행**: Playwright, 단위 테스트
- **Git 관리**: 커밋, 푸시, 브랜치 관리
- **자동 배포**: 스크립트 실행, 파일 동기화
- **SHRIMP 조작**: 작업 생성/수정/완료

### 🖥️ Claude Desktop 담당 영역
- **프로젝트 기획**: 요구사항 분석, 아키텍처 설계
- **코드 리뷰**: 품질 검토, 보안 점검
- **문서화**: 매뉴얼, API 문서 작성
- **복잡한 논의**: 문제 해결, 의사결정
- **SHRIMP 모니터링**: 작업 현황 확인 요청
- **사용자 요청시에는 Claude Code 담당 역할 수행 **
---

## ⚡ 빠른 시작 가이드

### Claude Code 작업 시작
```bash
# 1. 지침서 로딩
await loadProjectGuides()

# 2. 마스터 작업 파일 확인 (최우선)
Read: C:\xampp\htdocs\BPM\SHRIMP_Tasks.md

# 3. SHRIMP 시스템 작업 확인 (보조)
shrimp-task-manager:list_tasks all

# 4. 작업 시작 (SHRIMP_Tasks.md 기준으로 선택)
shrimp-task-manager:execute_task [작업ID]

# 4. 개발 진행 (표준 헤더 적용)
# 📁 C:\xampp\htdocs\BPM\[경로]\[파일명]
# Create at YYMMDDhhmm Ver1.00

## **파일별 주석 형식**
- **HTML/XML**: `<!-- 헤더 -->`
- **PHP/JS/CSS**: `// 헤더`
- **SQL**: `-- 헤더`
- **배치파일**: `REM 헤더`
- **Markdown**: `<!-- 헤더 -->`

```

### Claude Desktop 기획/리뷰
```markdown
## 기본 참조 순서
1. BPM_PROJECT_GUIDE.md (공통 지침)
2. BPM_CLAUDE_DESKTOP_GUIDE.md (Desktop 전용)
3. 필요시 Claude Code에 작업 요청

## 협업 요청 템플릿 사용
- 개발 요청: BPM_CLAUDE_DESKTOP_GUIDE.md 참조
- 리뷰 요청: 체크리스트 활용
```

---

## 🌈 핵심 개발 규칙

### 필수 표준
1. **헤더 형식**: `// 📁 경로 // Create at YYMMDDhhmm Ver1.00`
2. **색상 테마**: 모듈별 무지개 색상 적용
3. **작업 관리**: SHRIMP_Tasks.md 마스터 파일 기준
4. **SHRIMP 연동**: 세부 작업 추적 및 보조 관리
5. **보안**: .env 파일로 중요 정보 분리

### 작업 절차
```
지침서 로딩 → SHRIMP_Tasks.md 확인 → 작업 선택 → 개발 → 테스트 → Git → 배포 → SHRIMP_Tasks.md 업데이트 → 완료
```

---

## 공통 원칙
1. **소통 우선**: 불확실한 점은 즉시 확인
2. **표준 준수**: 헤더, 색상, 네이밍 규칙 엄격 적용
3. **품질 우선**: 기능보다 안정성과 보안 중시
4. **협업 중심**: 개별 작업보다 팀워크 우선
5. **한글 응답**: 모든 응답과 설명은 반드시 한글로 작성

---

## 🎨 모듈별 색상 가이드 (Quick Reference)

```css
🔴 조직관리: #ff6b6b / #fff5f5
🟠 구성원관리: #ff9f43 / #fff8f0
🟡 Task관리: #feca57 / #fffcf0
🟢 문서관리: #55a3ff / #f0fff4
🔵 Process Map: #3742fa / #f0f8ff
🟣 업무Flow: #a55eea / #f8f0ff
🟤 직무분석: #8b4513 / #faf0e6
```

---

## 📝 표준 파일 헤더 (Quick Reference)

```php
// 📁 C:\xampp\htdocs\BPM\[전체경로]\[파일명]
// Create at YYMMDDhhmm Ver1.00

/**
 * [파일 설명]
 * 모듈: [모듈명] (색상: [색상코드])
 * 작성자: [Claude Code/Desktop]
 * 목적: [파일 목적]
 */
```

---

## 🔄 동적 로딩 활용 예시

### JavaScript에서 사용
```javascript
// 지침서 로딩 및 활용
const GuideLoader = require('./scripts/load-guides.js');

async function startDevelopment() {
    // 1. 지침서 로딩
    const { guides, loader } = await loadProjectGuides();
    
    // 2. 현재 환경에 맞는 지침서 선택
    const codeGuide = loader.getGuideForEnvironment('code');
    
    // 3. 작업 현황 확인
    console.log(loader.getTaskSummary());
    
    // 4. 색상 테마 정보 확인
    console.log(loader.getColorThemes());
}

// 함수 실행
startDevelopment();
```

### 터미널에서 빠른 확인
```bash
# 전체 지침서 로딩
node -e "require('./scripts/load-guides.js'); loadProjectGuides();"

# 작업 현황만 확인
node -e "require('./scripts/load-guides.js'); checkTaskStatus();"

# 색상 테마만 확인
node -e "require('./scripts/load-guides.js'); getModuleColors();"
```

---

## 🚀 실전 워크플로우 예시

### Claude Code 일반적인 작업 순서
```bash
# 1. 환경 준비
cd C:\xampp\htdocs\BPM
node -e "require('./scripts/load-guides.js'); loadProjectGuides();"

# 2. 마스터 작업 파일 확인 (최우선)
Read: C:\xampp\htdocs\BPM\SHRIMP_Tasks.md

# 3. SHRIMP 시스템 작업 확인 (보조)
shrimp-task-manager:list_tasks pending

# 4. 작업 선택 및 시작 (SHRIMP_Tasks.md 기준)
shrimp-task-manager:execute_task [작업ID]

# 4. 개발 진행
# - 표준 헤더 적용
# - 모듈별 색상 테마 적용
# - 상세 주석 작성

# 5. 테스트
npm test

# 6. Git 관리
git add .
git commit -m "feat: [작업내용] - [작업ID]"
git push origin main

# 7. 배포
npm run deploy

# 8. 마스터 파일 업데이트 (필수)
Edit: C:\xampp\htdocs\BPM\SHRIMP_Tasks.md
# 작업 상태 업데이트: ⏳ → 🟡 → ✅

# 9. 작업 완료
shrimp-task-manager:verify_task [작업ID]
```

### Claude Desktop 기획/리뷰 순서
```markdown
1. 📚 BPM_PROJECT_GUIDE.md 확인 (공통 규칙)
2. 🖥️ BPM_CLAUDE_DESKTOP_GUIDE.md 확인 (Desktop 전용)
3. 📊 Claude Code에 작업 현황 요청
4. 📝 요구사항 정의 (템플릿 활용)
5. 🛠️ Claude Code에 개발 요청
6. 👀 개발 결과 리뷰 (체크리스트 활용)
7. 📖 문서 업데이트
8. 🎯 다음 단계 계획
```

---

## 🔍 디버깅 및 문제 해결

### 자주 발생하는 문제들

**1. 지침서 로딩 실패**
```bash
# 해결 방법
ls -la BPM_*_GUIDE.md  # 파일 존재 확인
node --version         # Node.js 버전 확인
pwd                    # 현재 디렉토리 확인
```

**2. SHRIMP 작업 관리 오류**
```bash
# 해결 방법
shrimp-task-manager:list_tasks all  # 전체 작업 목록 확인
cat SHRIMP_Tasks.md                  # 수동으로 파일 확인
```

**3. 테스트 실패**
```bash
# 해결 방법
npx playwright install --force  # Playwright 재설치
npm run test -- --debug        # 디버그 모드 실행
cat tests/test-results/*/test-results.json  # 상세 로그 확인
```

**4. 배포 실패**
```bash
# 해결 방법
echo $LOCAL_PATH      # 환경 변수 확인
git status            # Git 상태 확인
cat .env             # 환경 설정 확인
```

---

## 🎓 고급 활용 팁

### 1. 효율적인 개발을 위한 단축키
```bash
# .bashrc 또는 .zshrc에 추가
alias bpm-start="cd C:\xampp\htdocs\BPM && node -e 'require(\"./scripts/load-guides.js\"); loadProjectGuides();'"
alias bmp-tasks="node -e 'require(\"./scripts/load-guides.js\"); checkTaskStatus();'"
alias bpm-colors="node -e 'require(\"./scripts/load-guides.js\"); getModuleColors();'"
alias bpm-test="npm test"
alias bpm-deploy="npm run deploy"
```

### 2. VS Code 통합 (선택사항)
```json
// .vscode/tasks.json
{
    "version": "2.0.0",
    "tasks": [
        {
            "label": "BPM: Load Guides",
            "type": "shell",
            "command": "node",
            "args": ["-e", "require('./scripts/load-guides.js'); loadProjectGuides();"],
            "group": "build"
        }
    ]
}
```

---

## 📞 지원 및 연락

### 긴급 상황 대응
1. **Claude Code 접속 불가**: Claude Desktop으로 전환
2. **로컬 서버 다운**: 웹서버 직접 접속으로 확인
3. **파일 손실**: OneDrive 백업에서 복구
4. **Git 충돌**: 수동 병합 후 재배포

### 학습 리소스
- **Claude Code 문서**: [docs.anthropic.com/claude-code](https://docs.anthropic.com/claude-code)
- **MCP 서버 가이드**: MCP 공식 문서
- **Playwright 문서**: [playwright.dev](https://playwright.dev)
- **XAMPP 가이드**: [apachefriends.org](https://apachefriends.org)

---

## 🌟 버전 업데이트 로그

### Ver 3.10 (2025-08-02)
- 📋 SHRIMP_Tasks.md 마스터 작업 관리 시스템 추가
- 🎯 작업 관리 이중 확인 시스템 구축
- 📊 작업 우선순위: SHRIMP_Tasks.md > SHRIMP 시스템
- 🔄 작업 절차에 마스터 파일 업데이트 단계 추가

### Ver 3.00 (2025-08-02)
- ✨ 동적 지침서 로딩 시스템 추가
- 🎯 Claude Code/Desktop 역할 분담 명확화
- 📚 지침서 구조 3단계로 개편
- ⚡ 빠른 참조 가이드 추가
- 🔧 자동화 스크립트 통합

---

*Last updated: 2025-08-02 20:30 JST*  
*Version: 3.10 - SHRIMP_Tasks.md 마스터 작업 관리 시스템 완성*