#!/bin/bash

# WSL 환경에서 Claude Code 실행 스크립트
# MCP 서버와 환경 변수를 모두 로드한 후 Claude Code 실행

echo "=== Claude Code WSL 실행 준비 ==="

# 현재 디렉토리 확인
CURRENT_DIR=$(pwd)
echo "작업 디렉토리: $CURRENT_DIR"

# 환경 변수 로드
echo "환경 변수 로드 중..."
if [ -f "load_env_wsl.sh" ]; then
    source load_env_wsl.sh
else
    echo "Warning: load_env_wsl.sh not found. .env 파일을 직접 로드합니다."
    if [ -f ".env" ]; then
        # Windows 경로를 WSL 경로로 변환하면서 .env 로드
        while IFS= read -r line || [[ -n "$line" ]]; do
            if [[ $line =~ ^[[:space:]]*# ]] || [[ -z "${line// }" ]]; then
                continue
            fi
            if [[ $line =~ ^[A-Za-z_][A-Za-z0-9_]*= ]]; then
                # Windows 경로를 WSL 경로로 변환
                if [[ $line =~ C:\\\\.*\\\\.*\\\\APP ]]; then
                    line=$(echo "$line" | sed 's|C:\\\\Users\\\\man4k\\\\OneDrive\\\\문서\\\\APP|/mnt/c/Users/man4k/OneDrive/문서/APP|g')
                    line=$(echo "$line" | sed 's|\\\\|/|g')
                fi
                export "$line"
            fi
        done < ".env"
        echo "✓ .env 파일 로드 완료"
    else
        echo "Error: .env 파일을 찾을 수 없습니다!"
        exit 1
    fi
fi

# Node.js 및 NPM 버전 확인
echo ""
echo "=== 개발 환경 확인 ==="
if command -v node &> /dev/null; then
    echo "Node.js: $(node --version)"
else
    echo "❌ Node.js가 설치되지 않았습니다."
    echo "설치 명령: curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash - && sudo apt install -y nodejs"
    exit 1
fi

if command -v npm &> /dev/null; then
    echo "NPM: $(npm --version)"
else
    echo "❌ NPM이 설치되지 않았습니다."
    exit 1
fi

# Claude Code 설치 확인
echo ""
echo "=== Claude Code 확인 ==="
if command -v claude-code &> /dev/null; then
    echo "✓ Claude Code가 설치되어 있습니다."
    echo "버전: $(claude-code --version 2>/dev/null || echo 'Unknown')"
else
    echo "Claude Code를 설치합니다..."
    npm install -g @anthropic-ai/claude-code
    if command -v claude-code &> /dev/null; then
        echo "✓ Claude Code 설치 완료"
    else
        echo "❌ Claude Code 설치 실패"
        exit 1
    fi
fi

# Git 상태 확인
echo ""
echo "=== Git 상태 확인 ==="
if [ -d ".git" ]; then
    echo "✓ Git 리포지토리 확인됨"
    echo "현재 브랜치: $(git branch --show-current 2>/dev/null || echo 'Unknown')"
    echo "원격 저장소: $(git remote get-url origin 2>/dev/null || echo 'Not configured')"
    
    # 변경사항 확인
    if ! git diff --quiet; then
        echo "⚠️  커밋되지 않은 변경사항이 있습니다:"
        git status --porcelain
    else
        echo "✓ 작업 디렉토리가 깨끗합니다."
    fi
else
    echo "⚠️  Git 리포지토리가 초기화되지 않았습니다."
    echo "git init 명령으로 초기화하거나 setup_git_collaboration.sh를 실행하세요."
fi

# 네트워크 연결 테스트
echo ""
echo "=== 네트워크 연결 테스트 ==="
if ping -c 1 google.com &> /dev/null; then
    echo "✓ 인터넷 연결 확인됨"
else
    echo "⚠️  인터넷 연결을 확인해주세요."
fi

# MCP 서버 설정 확인
echo ""
echo "=== MCP 서버 설정 확인 ==="
if [ -f "$HOME/.config/claude-desktop/claude_desktop_config.json" ]; then
    echo "✓ Claude Desktop 설정 파일 존재: ~/.config/claude-desktop/claude_desktop_config.json"
    
    # 설정 파일의 서버 개수 확인
    server_count=$(python3 -c "
import json
try:
    with open('$HOME/.config/claude-desktop/claude_desktop_config.json', 'r') as f:
        config = json.load(f)
    print(len(config.get('mcpServers', {})))
except:
    print('0')
" 2>/dev/null || echo "0")
    echo "설정된 MCP 서버 개수: $server_count"
else
    echo "⚠️  Claude Desktop 설정 파일이 없습니다."
    if [ -f "claude_desktop_config_wsl.json" ]; then
        echo "WSL용 설정 파일을 복사합니다..."
        mkdir -p ~/.config/claude-desktop
        cp claude_desktop_config_wsl.json ~/.config/claude-desktop/claude_desktop_config.json
        echo "✓ 설정 파일 복사 완료"
    fi
fi

# 실행 전 최종 확인
echo ""
echo "=== 실행 준비 완료 ==="
echo "프로젝트 경로: $CURRENT_DIR"
echo "환경 변수 로드: ✓"
echo "개발 도구: Node.js $(node --version), NPM $(npm --version)"
echo "Claude Code: $(command -v claude-code &> /dev/null && echo '✓' || echo '❌')"
echo "Git 리포지토리: $([ -d '.git' ] && echo '✓' || echo '❌')"
echo "MCP 설정: $([ -f '$HOME/.config/claude-desktop/claude_desktop_config.json' ] && echo '✓' || echo '❌')"
echo ""

# Claude Code 실행 옵션 선택
echo "Claude Code 실행 옵션을 선택하세요:"
echo "1) 일반 실행 (claude-code)"
echo "2) 특정 파일로 시작 (claude-code <파일명>)"
echo "3) 디버그 모드 (claude-code --debug)"
echo "4) 환경 정보만 확인하고 종료"
echo ""
read -p "선택 (1-4): " choice

case $choice in
    1)
        echo "Claude Code를 실행합니다..."
        exec claude-code
        ;;
    2)
        read -p "시작할 파일명을 입력하세요: " filename
        if [ -f "$filename" ]; then
            echo "Claude Code를 $filename 파일로 시작합니다..."
            exec claude-code "$filename"
        else
            echo "파일을 찾을 수 없습니다: $filename"
            echo "일반 모드로 실행합니다..."
            exec claude-code
        fi
        ;;
    3)
        echo "Claude Code를 디버그 모드로 실행합니다..."
        exec claude-code --debug
        ;;
    4)
        echo "환경 정보 확인 완료. 스크립트를 종료합니다."
        exit 0
        ;;
    *)
        echo "잘못된 선택입니다. 일반 모드로 실행합니다..."
        exec claude-code
        ;;
esac
