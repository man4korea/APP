#!/bin/bash

# WSL 환경 MCP 서버 설정 검증 스크립트
# 모든 구성 요소가 올바르게 설정되었는지 확인

echo "======================================"
echo "  WSL 환경 MCP 설정 검증"
echo "======================================"
echo ""

# 기본 정보 수집
CURRENT_DIR=$(pwd)
WSL_DISTRO=$(cat /etc/os-release | grep "^NAME=" | cut -d'"' -f2)
WSL_VERSION=$(cat /etc/os-release | grep "^VERSION=" | cut -d'"' -f2)

echo "🖥️  시스템 정보:"
echo "   WSL 배포판: $WSL_DISTRO $WSL_VERSION"
echo "   작업 디렉토리: $CURRENT_DIR"
echo "   사용자: $(whoami)"
echo "   홈 디렉토리: $HOME"
echo ""

# 1. 기본 도구 검증
echo "=== 1. 기본 개발 도구 검증 ==="
tools=("curl" "wget" "git" "python3" "node" "npm")
for tool in "${tools[@]}"; do
    if command -v "$tool" &> /dev/null; then
        version=$(eval "$tool --version 2>/dev/null | head -1" || echo "Unknown")
        echo "✅ $tool: $version"
    else
        echo "❌ $tool: 설치되지 않음"
    fi
done
echo ""

# 2. Claude Code 검증
echo "=== 2. Claude Code 검증 ==="
if command -v claude-code &> /dev/null; then
    echo "✅ Claude Code 설치됨: $(which claude-code)"
    # Claude Code 버전 확인 시도
    claude_version=$(claude-code --version 2>/dev/null || echo "버전 정보 없음")
    echo "   버전: $claude_version"
else
    echo "❌ Claude Code 설치되지 않음"
    echo "   설치 명령: npm install -g @anthropic-ai/claude-code"
fi
echo ""

# 3. 환경 변수 검증
echo "=== 3. 환경 변수 검증 ==="
if [ -f ".env" ]; then
    echo "✅ .env 파일 존재"
    
    # load_env_wsl.sh 스크립트 테스트
    if [ -f "load_env_wsl.sh" ]; then
        echo "✅ load_env_wsl.sh 스크립트 존재"
        
        # 환경 변수 로드 테스트
        source load_env_wsl.sh >/dev/null 2>&1
        
        # 주요 환경 변수 확인
        env_vars=("GITHUB_USERNAME" "GIT_USER_NAME" "PROJECT_PATH" "DATA_DIR")
        for var in "${env_vars[@]}"; do
            if [[ -n "${!var}" ]]; then
                echo "✅ $var: ${!var}"
            else
                echo "⚠️  $var: 설정되지 않음"
            fi
        done
        
        # API 키 확인 (값은 표시하지 않음)
        api_keys=("OPENAI_API_KEY" "ANTHROPIC_API_KEY" "GITHUB_TOKEN" "GOOGLE_API_KEY")
        echo ""
        echo "API 키 상태:"
        for key in "${api_keys[@]}"; do
            if [[ -n "${!key}" ]]; then
                echo "✅ $key: 설정됨"
            else
                echo "⚠️  $key: 설정되지 않음"
            fi
        done
    else
        echo "❌ load_env_wsl.sh 스크립트 없음"
    fi
else
    echo "❌ .env 파일 없음"
fi
echo ""

# 4. Git 설정 검증
echo "=== 4. Git 설정 검증 ==="
if [ -d ".git" ]; then
    echo "✅ Git 리포지토리 초기화됨"
    echo "   현재 브랜치: $(git branch --show-current 2>/dev/null || echo '알 수 없음')"
    
    # Git 사용자 설정 확인
    git_name=$(git config --global user.name 2>/dev/null)
    git_email=$(git config --global user.email 2>/dev/null)
    
    if [[ -n "$git_name" ]] && [[ -n "$git_email" ]]; then
        echo "✅ Git 사용자 설정: $git_name <$git_email>"
    else
        echo "⚠️  Git 사용자 설정 필요"
    fi
    
    # 원격 저장소 확인
    remote_url=$(git remote get-url origin 2>/dev/null || echo "없음")
    echo "   원격 저장소: $remote_url"
    
    # Git 상태 확인
    if git diff --quiet 2>/dev/null; then
        echo "✅ 작업 디렉토리 깨끗함"
    else
        echo "⚠️  커밋되지 않은 변경사항 있음"
        echo "   git status로 확인하세요"
    fi
else
    echo "❌ Git 리포지토리 초기화되지 않음"
    echo "   git init 명령 필요"
fi
echo ""

# 5. MCP 서버 설정 검증
echo "=== 5. MCP 서버 설정 검증 ==="
claude_config="$HOME/.config/claude-desktop/claude_desktop_config.json"

if [ -f "$claude_config" ]; then
    echo "✅ Claude Desktop 설정 파일 존재"
    echo "   위치: $claude_config"
    
    # JSON 유효성 검사
    if python3 -c "import json; json.load(open('$claude_config'))" 2>/dev/null; then
        echo "✅ 설정 파일 JSON 형식 유효"
        
        # MCP 서버 개수 확인
        server_count=$(python3 -c "
import json
with open('$claude_config', 'r') as f:
    config = json.load(f)
servers = config.get('mcpServers', {})
print(f'서버 개수: {len(servers)}')
for name in servers.keys():
    print(f'  - {name}')
" 2>/dev/null)
        echo "$server_count"
    else
        echo "❌ 설정 파일 JSON 형식 오류"
    fi
else
    echo "❌ Claude Desktop 설정 파일 없음"
    echo "   위치: $claude_config"
    
    # WSL용 설정 파일 확인
    if [ -f "claude_desktop_config_wsl.json" ]; then
        echo "⚠️  WSL용 설정 파일은 존재함: claude_desktop_config_wsl.json"
        echo "   다음 명령으로 복사: cp claude_desktop_config_wsl.json ~/.config/claude-desktop/claude_desktop_config.json"
    fi
fi
echo ""

# 6. AI 협업 디렉토리 구조 검증
echo "=== 6. AI 협업 디렉토리 구조 검증 ==="
ai_dirs=("chatgpt" "gemini" "claude-code" "claude-desktop" "shared")
for dir in "${ai_dirs[@]}"; do
    if [ -d "$dir" ]; then
        echo "✅ $dir/ 디렉토리 존재"
        if [ -f "$dir/README.md" ]; then
            echo "   └── README.md 존재"
        else
            echo "   └── README.md 없음"
        fi
    else
        echo "❌ $dir/ 디렉토리 없음"
    fi
done
echo ""

# 7. 네트워크 연결 테스트
echo "=== 7. 네트워크 연결 테스트 ==="
if ping -c 1 -W 3 google.com >/dev/null 2>&1; then
    echo "✅ 인터넷 연결 정상"
else
    echo "❌ 인터넷 연결 문제"
fi

if ping -c 1 -W 3 github.com >/dev/null 2>&1; then
    echo "✅ GitHub 연결 정상"
else
    echo "❌ GitHub 연결 문제"
fi

# NPM 레지스트리 연결 테스트
if npm ping >/dev/null 2>&1; then
    echo "✅ NPM 레지스트리 연결 정상"
else
    echo "❌ NPM 레지스트리 연결 문제"
fi
echo ""

# 8. 스크립트 파일 검증
echo "=== 8. 스크립트 파일 검증 ==="
scripts=(
    "load_env_wsl.sh"
    "setup_git_collaboration.sh" 
    "start_claude_code_wsl.sh"
    "complete_wsl_setup.sh"
)

for script in "${scripts[@]}"; do
    if [ -f "$script" ]; then
        if [ -x "$script" ]; then
            echo "✅ $script (실행 가능)"
        else
            echo "⚠️  $script (실행 권한 없음)"
            echo "   chmod +x $script 명령 필요"
        fi
    else
        echo "❌ $script 없음"
    fi
done
echo ""

# 9. 종합 평가
echo "======================================"
echo "  📊 종합 평가"
echo "======================================"

# 점수 계산 (간단한 체크리스트 기반)
total_score=0
max_score=10

# 기본 도구 (2점)
if command -v node &> /dev/null && command -v npm &> /dev/null && command -v git &> /dev/null; then
    total_score=$((total_score + 2))
    echo "✅ 기본 개발 도구: 2/2점"
else
    echo "❌ 기본 개발 도구: 0/2점"
fi

# Claude Code (2점)
if command -v claude-code &> /dev/null; then
    total_score=$((total_score + 2))
    echo "✅ Claude Code: 2/2점"
else
    echo "❌ Claude Code: 0/2점"
fi

# 환경 변수 (2점)
if [ -f ".env" ] && [ -f "load_env_wsl.sh" ]; then
    source load_env_wsl.sh >/dev/null 2>&1
    if [[ -n "$GITHUB_USERNAME" ]]; then
        total_score=$((total_score + 2))
        echo "✅ 환경 변수: 2/2점"
    else
        total_score=$((total_score + 1))
        echo "⚠️  환경 변수: 1/2점"
    fi
else
    echo "❌ 환경 변수: 0/2점"
fi

# Git 설정 (2점)
if [ -d ".git" ]; then
    if git config --global user.name >/dev/null 2>&1; then
        total_score=$((total_score + 2))
        echo "✅ Git 설정: 2/2점"
    else
        total_score=$((total_score + 1))
        echo "⚠️  Git 설정: 1/2점"
    fi
else
    echo "❌ Git 설정: 0/2점"
fi

# MCP 설정 (2점)
if [ -f "$HOME/.config/claude-desktop/claude_desktop_config.json" ]; then
    if python3 -c "import json; json.load(open('$HOME/.config/claude-desktop/claude_desktop_config.json'))" 2>/dev/null; then
        total_score=$((total_score + 2))
        echo "✅ MCP 설정: 2/2점"
    else
        total_score=$((total_score + 1))
        echo "⚠️  MCP 설정: 1/2점"
    fi
else
    echo "❌ MCP 설정: 0/2점"
fi

echo ""
echo "🏆 총점: $total_score/$max_score점"

if [ $total_score -eq $max_score ]; then
    echo "🎉 완벽! 모든 설정이 정상적으로 완료되었습니다."
    echo "   Claude Code를 실행할 준비가 되었습니다!"
elif [ $total_score -ge 8 ]; then
    echo "👍 양호! 대부분의 설정이 완료되었습니다."
    echo "   일부 항목을 확인하여 완성하세요."
elif [ $total_score -ge 6 ]; then
    echo "⚠️  보통! 몇 가지 중요한 설정이 누락되었습니다."
    echo "   누락된 항목들을 설정해주세요."
else
    echo "❌ 불완전! 많은 설정이 필요합니다."
    echo "   complete_wsl_setup.sh를 다시 실행해보세요."
fi

echo ""
echo "🚀 다음 단계:"
if [ $total_score -ge 8 ]; then
    echo "1. Claude Code 실행: ./start_claude_code_wsl.sh"
    echo "2. Git 변경사항 커밋 및 푸시"
    echo "3. AI 협업 시작!"
else
    echo "1. 누락된 구성 요소 설치/설정"
    echo "2. complete_wsl_setup.sh 재실행"
    echo "3. 이 스크립트로 재검증"
fi
