#!/bin/bash

# Git 협업 환경 설정 스크립트 (WSL용)
# ChatGPT, Gemini, Claude Code Windows, Desktop Claude 간 협업 설정

echo "=== Git 협업 환경 설정 (WSL) ==="

# 환경 변수 로드
if [ -f "load_env_wsl.sh" ]; then
    source load_env_wsl.sh
else
    echo "Warning: load_env_wsl.sh not found. Loading .env directly..."
    if [ -f ".env" ]; then
        source .env
    else
        echo "Error: No environment configuration found!"
        exit 1
    fi
fi

# Git 리포지토리 초기화 확인
if [ ! -d ".git" ]; then
    echo "Git 리포지토리를 초기화합니다..."
    git init
    
    # 기본 브랜치를 main으로 설정
    git config --global init.defaultBranch main
    git checkout -b main 2>/dev/null || true
fi

# Git 원격 저장소 설정
if [[ -n "$GITHUB_URL" ]]; then
    echo "Git 원격 저장소 설정 중..."
    
    # 기존 origin 제거 (있다면)
    git remote remove origin 2>/dev/null || true
    
    # 새 origin 추가
    git remote add origin "$GITHUB_URL"
    echo "✓ 원격 저장소 설정: $GITHUB_URL"
else
    echo "Warning: GITHUB_URL이 설정되지 않았습니다."
fi

# .gitignore 설정 확인 및 업데이트
echo "Git ignore 설정 중..."
cat > .gitignore << 'EOF'
# Dependencies
node_modules/
npm-debug.log*
yarn-debug.log*
yarn-error.log*

# Environment variables
.env.local
.env.development.local
.env.test.local
.env.production.local

# API Keys (보안상 중요한 파일들)
.env.sensitive
*.key
*.pem

# OS generated files
.DS_Store
.DS_Store?
._*
.Spotlight-V100
.Trashes
ehthumbs.db
Thumbs.db

# IDE
.vscode/settings.json
.idea/
*.swp
*.swo
*~

# Logs
logs/
*.log

# Runtime data
pids
*.pid
*.seed
*.pid.lock

# Coverage directory used by tools like istanbul
coverage/

# Build outputs
dist/
build/
*.tgz

# MCP temporary files
.mcp-temp/
.claude-temp/

# AI collaboration files (임시)
*.tmp
.ai-temp/

# Windows specific
desktop.ini
$RECYCLE.BIN/

# Claude Desktop config (사용자별로 다를 수 있음)
# claude_desktop_config.json은 공유하되, 개인 설정은 별도 관리
EOF

# AI 협업을 위한 README 파일 생성/업데이트
echo "AI 협업용 README 파일 업데이트 중..."
cat > AI_COLLABORATION_README.md << 'EOF'
# AI 협업 환경 설정

이 프로젝트는 여러 AI 도구들이 협업할 수 있도록 구성되었습니다.

## 참여 AI 도구들
- **ChatGPT**: Deep Research 기능 활용
- **Gemini**: GitHub 연동 기능 활용
- **Claude Code (Windows)**: VS Code 통합 개발
- **Claude Desktop**: MCP 서버 활용

## 환경 설정

### Windows 환경
```bash
# Claude Desktop 실행 후 MCP 서버 자동 로드
# 설정 파일: claude_desktop_config.json
```

### WSL 환경
```bash
# 환경 변수 로드
source load_env_wsl.sh

# Claude Code 실행
claude-code

# Git 협업 설정
source setup_git_collaboration.sh
```

## 협업 워크플로우

1. **작업 브랜치 생성**
   ```bash
   git checkout -b feature/새기능명
   ```

2. **AI별 작업 영역**
   - `chatgpt/`: ChatGPT 작업 결과물
   - `gemini/`: Gemini 분석 결과
   - `claude-code/`: Claude Code 개발 결과
   - `claude-desktop/`: Claude Desktop MCP 작업

3. **변경사항 커밋**
   ```bash
   git add .
   git commit -m "[AI이름] 작업 내용 설명"
   git push origin feature/새기능명
   ```

4. **Pull Request 생성**
   - GitHub에서 PR 생성
   - 다른 AI 도구들이 리뷰 및 추가 작업 수행

## 보안 고려사항
- API 키는 .env 파일에 저장하되 Git에 커밋하지 않음
- 민감한 정보는 .gitignore에 추가
- 개인별 설정은 로컬에만 유지

## MCP 서버 설정
- Windows용: `claude_desktop_config.json`
- WSL용: `claude_desktop_config_wsl.json`

## 문제 해결
- 환경 변수 로드 안됨: `source load_env_wsl.sh` 재실행
- Git 권한 문제: GitHub token 확인
- MCP 서버 연결 실패: 설정 파일 경로 확인
EOF

# AI 협업용 디렉토리 구조 생성
echo "AI 협업 디렉토리 구조 생성 중..."
mkdir -p {chatgpt,gemini,claude-code,claude-desktop,shared}

# 각 AI 도구별 README 생성
cat > chatgpt/README.md << 'EOF'
# ChatGPT 작업 영역

ChatGPT의 Deep Research 기능을 활용한 작업 결과물을 저장합니다.

## 주요 기능
- 심층 리서치 및 분석
- 문서 작성 및 정리
- 아이디어 브레인스토밍

## 작업 결과물
- 리서치 보고서
- 기획 문서
- 분석 결과
EOF

cat > gemini/README.md << 'EOF'
# Gemini 작업 영역

Gemini의 GitHub 연동 기능을 활용한 작업 결과물을 저장합니다.

## 주요 기능
- GitHub 이슈 관리
- 코드 리뷰 및 분석
- 프로젝트 관리

## 작업 결과물
- 코드 리뷰 보고서
- 이슈 분석 결과
- 프로젝트 진행 상황
EOF

cat > claude-code/README.md << 'EOF'
# Claude Code 작업 영역

Claude Code의 VS Code 통합 기능을 활용한 개발 작업 결과물을 저장합니다.

## 주요 기능
- 실시간 코드 개발
- 터미널 명령 실행
- 파일 시스템 관리

## 작업 결과물
- 소스 코드
- 설정 파일
- 개발 도구 설정
EOF

cat > claude-desktop/README.md << 'EOF'
# Claude Desktop 작업 영역

Claude Desktop의 MCP 서버 기능을 활용한 작업 결과물을 저장합니다.

## 주요 기능
- MCP 서버 통합
- 외부 API 연동
- 자동화 스크립트

## 작업 결과물
- MCP 설정 파일
- API 연동 결과
- 자동화 스크립트
EOF

cat > shared/README.md << 'EOF'
# 공유 작업 영역

모든 AI 도구들이 공통으로 사용하는 리소스를 저장합니다.

## 포함 내용
- 공통 설정 파일
- 공유 라이브러리
- 문서 템플릿
- 협업 가이드라인
EOF

# Git 상태 확인
echo ""
echo "=== Git 상태 확인 ==="
git status

# 초기 커밋 (필요한 경우)
if ! git rev-parse --verify HEAD >/dev/null 2>&1; then
    echo ""
    echo "초기 커밋을 생성합니다..."
    git add .
    git commit -m "Initial commit: AI 협업 환경 설정 완료

- MCP 서버 WSL 환경 설정
- AI 도구별 작업 영역 구성
- Git 협업 워크플로우 설정
- 보안 설정 적용"
    
    echo "✓ 초기 커밋 완료"
fi

echo ""
echo "=== Git 협업 환경 설정 완료! ==="
echo ""
echo "다음 단계:"
echo "1. git push origin main              # 원격 저장소에 푸시"
echo "2. claude-code                       # Claude Code 실행"
echo "3. source load_env_wsl.sh           # 환경 변수 재로드 (필요시)"
echo ""
echo "협업 워크플로우:"
echo "• 새 기능 작업: git checkout -b feature/기능명"
echo "• 변경사항 커밋: git add . && git commit -m '[AI이름] 작업 내용'"
echo "• 원격 푸시: git push origin 브랜치명"
echo "• Pull Request 생성 후 다른 AI들과 협업"
