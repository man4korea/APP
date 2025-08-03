# 📦 Claude Code 완전 환경 복제 설치 가이드

<!-- 📁 C:\xampp\htdocs\BPM\CLAUDE_CODE_SETUP_PACKAGE\00_MASTER_INSTALLATION_GUIDE.md -->
<!-- Create at 2508031200 Ver1.00 -->

## 🎯 목적
집 노트북의 Claude Code MCP 서버 및 SuperClaude 에이전트 환경을 회사 노트북에 완전히 동일하게 복제 설치

## 📋 설치 전 체크리스트

### ✅ 기본 요구사항 확인
- [ ] Windows 10/11 64비트
- [ ] Node.js 18+ 설치됨
- [ ] Python 3.8+ 설치됨  
- [ ] Git 설치됨
- [ ] Claude Desktop 앱 설치됨
- [ ] 관리자 권한 확보

### 📁 폴더 구조 확인
```
C:\xampp\htdocs\BPM\
├── CLAUDE_CODE_SETUP_PACKAGE\     # 📦 이 폴더 전체를 복사
│   ├── 00_MASTER_INSTALLATION_GUIDE.md
│   ├── 01_auto_install_mcp.bat
│   ├── 02_install_superclaude.bat
│   ├── 03_setup_agents.bat
│   ├── 04_verify_installation.bat
│   ├── config\
│   │   ├── claude_desktop_settings.json
│   │   ├── mcp_servers_config.json
│   │   └── environment_variables.env
│   └── agents\
│       ├── code-reviewer\
│       ├── security-reviewer\
│       ├── tech-lead\
│       ├── ux-reviewer\
│       └── code-simplifier\
```

---

## 🚀 자동 설치 실행 순서

### Step 1: 설치 패키지 준비
```bash
# 1. USB나 OneDrive로 CLAUDE_CODE_SETUP_PACKAGE 폴더 복사
# 2. 회사 노트북의 동일한 경로에 붙여넣기
C:\xampp\htdocs\BPM\CLAUDE_CODE_SETUP_PACKAGE\
```

### Step 2: 관리자 권한으로 배치 파일 실행
```cmd
# PowerShell을 관리자 권한으로 실행 후:
cd C:\xampp\htdocs\BPM\CLAUDE_CODE_SETUP_PACKAGE

# 자동 설치 시작 (순서대로 실행)
.\01_auto_install_mcp.bat
.\02_install_superclaude.bat  
.\03_setup_agents.bat
.\04_verify_installation.bat
```

---

## 📜 각 배치 파일 상세 설명

### 🔧 01_auto_install_mcp.bat
- **목적**: 모든 MCP 서버 자동 설치
- **설치 대상**: 
  - shrimp-task-manager
  - playwright-stealth
  - filesystem
  - text-editor
  - memory
  - github
  - puppeteer
  - sequential-thinking
  - terminal (weidwonder)
  - ide

### 🤖 02_install_superclaude.bat  
- **목적**: SuperClaude CLI 도구 설치
- **기능**: AI 기반 커밋, 리뷰, 문서화 자동화

### 👥 03_setup_agents.bat
- **목적**: 5개 전문 에이전트 설치
- **에이전트**: code-reviewer, security-reviewer, tech-lead, ux-reviewer, code-simplifier

### ✅ 04_verify_installation.bat
- **목적**: 설치 완료 검증 및 테스트

---

## 🛠️ 수동 설치 방법 (백업용)

### MCP 서버 개별 설치
```bash
# shrimp-task-manager
npx @modelcontextprotocol/cli install @shrimpai/shrimp-task-manager

# playwright-stealth  
npx @modelcontextprotocol/cli install @agentic/mcp-playwright

# filesystem
npx @modelcontextprotocol/cli install @modelcontextprotocol/server-filesystem

# memory
npx @modelcontextprotocol/cli install @modelcontextprotocol/server-memory

# github
npx @modelcontextprotocol/cli install @modelcontextprotocol/server-github

# sequential-thinking
npx @modelcontextprotocol/cli install @modelcontextprotocol/server-sequential-thinking

# terminal
npx @modelcontextprotocol/cli install @weidwonder/terminal-mcp-server

# text-editor
npx @modelcontextprotocol/cli install @modelcontextprotocol/server-text-editor

# puppeteer
npx @modelcontextprotocol/cli install @modelcontextprotocol/server-puppeteer

# IDE
npx @modelcontextprotocol/cli install @modelcontextprotocol/server-ide
```

### SuperClaude 수동 설치
```bash
npm install -g superclaude
```

---

## 🔧 설정 파일 위치

### Claude Desktop 설정
```
# Windows 경로
%APPDATA%\Claude\settings.json

# 또는 (앱 버전에 따라)
%APPDATA%\Claude\claude_desktop_config.json
```

### MCP 설정 파일 위치
```
# 기본 위치
%APPDATA%\Claude\.mcp.json

# 또는 
C:\Users\[사용자명]\.claude\settings.json
```

---

## 🎯 설치 후 필수 설정

### 1. 환경 변수 설정
```env
# API 키들 (.env 파일에 추가)
GITHUB_TOKEN=your_github_token
OPENAI_API_KEY=your_openai_key
ANTHROPIC_API_KEY=your_anthropic_key
```

### 2. Claude Desktop 재시작
```bash
# Claude Desktop 완전 종료 후 재시작
taskkill /f /im "Claude.exe"
# Claude Desktop 앱 다시 시작
```

### 3. 권한 설정 확인
```bash
# PowerShell에서 실행 정책 확인
Get-ExecutionPolicy

# 필요시 정책 변경
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

---

## 🧪 설치 검증 방법

### MCP 서버 동작 확인
```bash
# Claude Code에서 각 도구 테스트
- shrimp-task-manager:list_tasks all
- mcp__filesystem__list_allowed_directories
- mcp__github__search_repositories query:"test"
- mcp__playwright__playwright_navigate url:"https://google.com"
```

### SuperClaude 동작 확인
```bash
superclaude --version
superclaude commit -i
```

### 에이전트 동작 확인
```bash
claude agents list
claude agents tech-lead
claude agents code-reviewer
```

---

## 🚨 문제 해결 가이드

### 일반적인 문제들

#### 1. MCP 서버 연결 실패
```bash
# 해결방법
1. Claude Desktop 완전 재시작
2. .mcp.json 파일 확인
3. Node.js 버전 확인 (18+)
4. 포트 충돌 확인
```

#### 2. 권한 오류
```bash
# 해결방법
1. PowerShell 관리자 권한 실행
2. 실행 정책 변경: Set-ExecutionPolicy RemoteSigned
3. UAC 설정 확인
```

#### 3. 에이전트 인식 안됨
```bash
# 해결방법
1. agents 폴더 위치 확인
2. 각 에이전트 폴더 내 필수 파일 확인:
   - config.json
   - system-prompt.md
   - test.js
```

#### 4. SuperClaude 설치 실패
```bash
# 해결방법
1. npm 캐시 클리어: npm cache clean --force
2. 글로벌 설치 재시도: npm install -g superclaude --force
3. Node.js 재설치
```

---

## 📞 지원 및 백업

### 설정 백업 방법
```bash
# 중요 설정 파일들 백업
copy "%APPDATA%\Claude\settings.json" "backup\"
copy "%APPDATA%\Claude\.mcp.json" "backup\"
copy "C:\xampp\htdocs\BPM\agents\*" "backup\agents\"
```

### 롤백 방법
```bash
# 문제 발생시 원본 설정으로 복원
.\05_rollback_installation.bat
```

---

## 📈 설치 완료 후 다음 단계

### 1. BPM 프로젝트 동기화
```bash
# Git에서 최신 코드 받기
cd C:\xampp\htdocs\BPM
git pull origin main
```

### 2. 개발 환경 설정
```bash
# Composer 및 NPM 의존성 설치
composer install
npm install
```

### 3. 첫 번째 작업 시작
```bash
# Claude Code에서 다음 명령 실행
cd C:\xampp\htdocs\BPM
shrimp-task-manager:list_tasks all
```

---

## 🎉 설치 완료 확인

✅ **모든 설치가 완료되면 다음을 확인:**

1. **MCP 서버**: 10개 모든 서버 연결됨
2. **SuperClaude**: `superclaude --version` 정상 응답
3. **에이전트**: `claude agents list` 5개 에이전트 표시
4. **BPM 프로젝트**: SHRIMP 작업 목록 정상 조회
5. **권한**: 모든 도구 정상 동작

**🎯 이제 집과 회사에서 동일한 Claude Code 환경에서 BPM 프로젝트 개발을 계속할 수 있습니다!**

---

*Last updated: 2025-08-03 12:00 JST*  
*Version: 1.00 - 완전 환경 복제 가이드*