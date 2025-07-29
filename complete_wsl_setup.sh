#!/bin/bash

# WSL 환경 MCP 서버 완전 설정 스크립트
# Claude Desktop 환경의 MCP 서버를 WSL 환경에 완전히 이식

echo "======================================"
echo "  WSL 환경 MCP 서버 완전 설정"
echo "  Claude Desktop → WSL 환경 이식"
echo "======================================"
echo ""

# 실행 권한 확인
if [ "$EUID" -eq 0 ]; then
    echo "⚠️  root 권한으로 실행하지 마세요. 일반 사용자로 실행해주세요."
    exit 1
fi

# 현재 디렉토리 확인
CURRENT_DIR=$(pwd)
if [[ ! "$CURRENT_DIR" =~ .*APP$ ]]; then
    echo "⚠️  APP 디렉토리에서 실행해주세요."
    echo "현재 위치: $CURRENT_DIR"
    echo "이동 명령: cd /mnt/c/Users/man4k/OneDrive/문서/APP"
    exit 1
fi

echo "✅ 작업 디렉토리 확인: $CURRENT_DIR"
echo ""

# 1단계: 시스템 업데이트 및 기본 도구 설치
echo "=== 1단계: 시스템 환경 준비 ==="
echo "시스템 패키지 업데이트 중..."
sudo apt update -y >/dev/null 2>&1 && echo "✅ 패키지 목록 업데이트 완료"

echo "기본 개발 도구 설치 중..."
sudo apt install -y curl wget git build-essential python3 python3-pip >/dev/null 2>&1 && echo "✅ 기본 도구 설치 완료"

# 2단계: Node.js 설치 (최신 LTS)
echo ""
echo "=== 2단계: Node.js 환경 설정 ==="
if ! command -v node &> /dev/null; then
    echo "Node.js 설치 중..."
    curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash - >/dev/null 2>&1
    sudo apt install -y nodejs >/dev/null 2>&1
    echo "✅ Node.js 설치 완료: $(node --version)"
else
    echo "✅ Node.js 이미 설치됨: $(node --version)"
fi

if ! command -v npm &> /dev/null; then
    echo "❌ NPM 설치 실패"
    exit 1
else
    echo "✅ NPM 확인: $(npm --version)"
fi

# 3단계: Claude Code 설치
echo ""
echo "=== 3단계: Claude Code 설치 ==="
if ! command -v claude-code &> /dev/null; then
    echo "Claude Code 설치 중..."
    npm install -g @anthropic-ai/claude-code >/dev/null 2>&1
    if command -v claude-code &> /dev/null; then
        echo "✅ Claude Code 설치 완료"
    else
        echo "❌ Claude Code 설치 실패. 수동 설치가 필요합니다."
        echo "명령: npm install -g @anthropic-ai/claude-code"
    fi
else
    echo "✅ Claude Code 이미 설치됨"
fi

# 4단계: 환경 변수 설정
echo ""
echo "=== 4단계: 환경 변수 설정 ==="
if [ -f ".env" ]; then
    echo "✅ .env 파일 발견"
    
    # 환경 변수 로드 스크립트 실행 권한 부여
    chmod +x load_env_wsl.sh 2>/dev/null
    chmod +x setup_git_collaboration.sh 2>/dev/null
    chmod +x setup_mcp_wsl.sh 2>/dev/null
    chmod +x start_claude_code_wsl.sh 2>/dev/null
    
    echo "✅ 스크립트 실행 권한 설정 완료"
    
    # 환경 변수 로드 테스트
    source load_env_wsl.sh >/dev/null 2>&1
    if [[ -n "$GITHUB_USERNAME" ]]; then
        echo "✅ 환경 변수 로드 성공"
    else
        echo "⚠️  환경 변수 로드에 문제가 있을 수 있습니다."
    fi
else
    echo "❌ .env 파일을 찾을 수 없습니다!"
    exit 1
fi

# 5단계: Git 설정
echo ""
echo "=== 5단계: Git 환경 설정 ==="
source load_env_wsl.sh >/dev/null 2>&1

if [[ -n "$GIT_USER_NAME" ]] && [[ -n "$GIT_USER_EMAIL" ]]; then
    git config --global user.name "$GIT_USER_NAME" 2>/dev/null
    git config --global user.email "$GIT_USER_EMAIL" 2>/dev/null
    echo "✅ Git 사용자 설정: $GIT_USER_NAME <$GIT_USER_EMAIL>"
else
    echo "⚠️  Git 사용자 정보가 환경 변수에 없습니다."
    echo "수동 설정 필요: git config --global user.name '이름'"
    echo "               git config --global user.email '이메일'"
fi

# Git 리포지토리 확인
if [ ! -d ".git" ]; then
    echo "Git 리포지토리 초기화 중..."
    git init >/dev/null 2>&1
    git config --global init.defaultBranch main 2>/dev/null
    git checkout -b main >/dev/null 2>&1
    echo "✅ Git 리포지토리 초기화 완료"
else
    echo "✅ Git 리포지토리 존재 확인"
fi

# 6단계: MCP 서버 설정
echo ""
echo "=== 6단계: MCP 서버 설정 ==="
mkdir -p ~/.config/claude-desktop

if [ -f "claude_desktop_config_wsl.json" ]; then
    cp claude_desktop_config_wsl.json ~/.config/claude-desktop/claude_desktop_config.json
    echo "✅ WSL용 Claude Desktop 설정 파일 복사 완료"
    
    # 설정 파일 검증
    server_count=$(python3 -c "
import json
try:
    with open('$HOME/.config/claude-desktop/claude_desktop_config.json', 'r') as f:
        config = json.load(f)
    print(len(config.get('mcpServers', {})))
except Exception as e:
    print('0')
" 2>/dev/null || echo "0")
    
    echo "✅ MCP 서버 설정 개수: $server_count개"
else
    echo "❌ WSL용 설정 파일(claude_desktop_config_wsl.json)을 찾을 수 없습니다!"
fi

# 7단계: 협업 환경 구성
echo ""
echo "=== 7단계: AI 협업 환경 구성 ==="
source setup_git_collaboration.sh >/dev/null 2>&1

if [ -d "chatgpt" ] && [ -d "gemini" ] && [ -d "claude-code" ] && [ -d "claude-desktop" ]; then
    echo "✅ AI 협업 디렉토리 구조 생성 완료"
else
    echo "⚠️  협업 디렉토리 생성에 문제가 있습니다."
fi

# 8단계: 최종 검증
echo ""
echo "=== 8단계: 설정 검증 ==="

# 필수 명령어 확인
commands=("node" "npm" "git" "python3")
for cmd in "${commands[@]}"; do
    if command -v "$cmd" &> /dev/null; then
        echo "✅ $cmd: $(which $cmd)"
    else
        echo "❌ $cmd: 설치되지 않음"
    fi
done

# Claude Code 확인
if command -v claude-code &> /dev/null; then
    echo "✅ claude-code: $(which claude-code)"
else
    echo "❌ claude-code: 설치되지 않음"
fi

# 설정 파일들 확인
config_files=(
    ".env"
    "claude_desktop_config_wsl.json"
    "load_env_wsl.sh"
    "setup_git_collaboration.sh"
    "start_claude_code_wsl.sh"
    "$HOME/.config/claude-desktop/claude_desktop_config.json"
)

echo ""
echo "설정 파일 확인:"
for file in "${config_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file"
    fi
done

# 9단계: 사용 가이드 출력
echo ""
echo "======================================"
echo "  🎉 WSL 환경 MCP 서버 설정 완료!"
echo "======================================"
echo ""
echo "📋 사용 방법:"
echo ""
echo "1. 환경 변수 로드:"
echo "   source load_env_wsl.sh"
echo ""
echo "2. Claude Code 실행:"
echo "   ./start_claude_code_wsl.sh"
echo "   또는"
echo "   claude-code"
echo ""
echo "3. Git 협업 설정:"
echo "   source setup_git_collaboration.sh"
echo ""
echo "4. 개별 AI 작업 영역:"
echo "   - chatgpt/     : ChatGPT 작업물"
echo "   - gemini/      : Gemini 작업물"
echo "   - claude-code/ : Claude Code 작업물"  
echo "   - claude-desktop/ : Claude Desktop 작업물"
echo "   - shared/      : 공통 리소스"
echo ""
echo "🔧 문제 해결:"
echo "- 환경 변수 문제: source load_env_wsl.sh 재실행"
echo "- Git 권한 문제: GitHub token 확인"
echo "- MCP 연결 실패: 설정 파일 경로 확인"
echo ""
echo "🚀 다음 단계:"
echo "1. WSL 터미널에서 다음 명령 실행:"
echo "   cd /mnt/c/Users/man4k/OneDrive/문서/APP"
echo "   source load_env_wsl.sh"
echo "   ./start_claude_code_wsl.sh"
echo ""
echo "2. Git 원격 저장소에 변경사항 푸시:"
echo "   git add ."
echo "   git commit -m \"WSL 환경 MCP 서버 설정 완료\""
echo "   git push origin main"
echo ""
echo "✨ AI 협업 환경이 준비되었습니다!"
echo "   ChatGPT, Gemini, Claude Code, Claude Desktop이"
echo "   모두 같은 Git 저장소를 통해 협업할 수 있습니다."
