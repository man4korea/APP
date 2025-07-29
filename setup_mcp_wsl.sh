#!/bin/bash

# WSL/Git Bash용 MCP 서버 설정 스크립트

echo "=== MCP 서버 WSL 환경 설정 시작 ==="

# 현재 디렉토리 확인
CURRENT_DIR=$(pwd)
echo "현재 작업 디렉토리: $CURRENT_DIR"

# Node.js 버전 확인
if command -v node &> /dev/null; then
    echo "Node.js 버전: $(node --version)"
else
    echo "Node.js가 설치되지 않았습니다. 설치를 진행합니다..."
    # WSL에서 Node.js 설치
    curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
    sudo apt install -y nodejs
fi

# NPM 버전 확인
if command -v npm &> /dev/null; then
    echo "NPM 버전: $(npm --version)"
fi

# MCP 관련 패키지 설치
echo "MCP 관련 패키지 설치 중..."

# 필요한 MCP 서버들 설치
npm install -g @anthropic-ai/claude-code

# 로컬 MCP 서버 패키지들 확인/설치
if [ -f "package.json" ]; then
    echo "기존 package.json 발견. 의존성 설치 중..."
    npm install
else
    echo "package.json 파일을 생성합니다..."
    npm init -y
fi

# MCP 서버 설정 디렉토리 생성
mkdir -p ~/.config/claude-desktop

# .env 파일이 있는지 확인
if [ -f ".env" ]; then
    echo ".env 파일 발견. 환경 변수를 로드합니다."
    source .env
else
    echo "경고: .env 파일을 찾을 수 없습니다."
fi

# Git 설정 확인
if git config --global user.name &> /dev/null; then
    echo "Git 사용자: $(git config --global user.name)"
    echo "Git 이메일: $(git config --global user.email)"
else
    echo "Git 설정이 필요합니다."
    echo "다음 명령어로 설정하세요:"
    echo "git config --global user.name \"your-name\""
    echo "git config --global user.email \"your-email@example.com\""
fi

# Claude Desktop 설정 파일 확인
if [ -f "claude_desktop_config.json" ]; then
    echo "Claude Desktop 설정 파일 발견"
    # WSL 경로로 변환된 설정 파일 생성
    python3 -c "
import json
import os

# Windows 경로를 WSL 경로로 변환하는 함수
def windows_to_wsl_path(path):
    if path.startswith('C:'):
        return path.replace('C:', '/mnt/c').replace('\\\\', '/')
    return path

# 기존 설정 파일 읽기
with open('claude_desktop_config.json', 'r', encoding='utf-8') as f:
    config = json.load(f)

# 경로들을 WSL 형식으로 변환
if 'mcpServers' in config:
    for server_name, server_config in config['mcpServers'].items():
        if 'command' in server_config:
            # Node.js 경로는 그대로 유지 (시스템에서 찾음)
            if server_config['command'] == 'node':
                continue
            # 다른 경로들은 변환
            server_config['command'] = windows_to_wsl_path(server_config['command'])
        
        if 'args' in server_config:
            for i, arg in enumerate(server_config['args']):
                if isinstance(arg, str) and ('C:' in arg or '\\\\' in arg):
                    server_config['args'][i] = windows_to_wsl_path(arg)

# WSL용 설정 파일 저장
os.makedirs(os.path.expanduser('~/.config/claude-desktop'), exist_ok=True)
with open(os.path.expanduser('~/.config/claude-desktop/claude_desktop_config.json'), 'w', encoding='utf-8') as f:
    json.dump(config, f, indent=2, ensure_ascii=False)

print('WSL용 Claude Desktop 설정 파일이 생성되었습니다.')
" 2>/dev/null || echo "Python3가 필요합니다. sudo apt install python3 명령으로 설치하세요."
fi

echo "=== MCP 서버 WSL 환경 설정 완료 ==="
echo ""
echo "다음 단계:"
echo "1. WSL에서 Claude Code 실행: claude-code"
echo "2. Git 협업을 위한 리모트 확인: git remote -v"
echo "3. 환경 변수 확인: env | grep -E '(API_KEY|TOKEN)'"
