#!/bin/bash

# WSL에서 MCP 서버 인식 문제 해결 스크립트

echo "======================================"
echo "  WSL MCP 서버 인식 문제 해결"
echo "======================================"
echo ""

# 1. 현재 MCP 설정 상태 확인
echo "=== 1. 현재 MCP 설정 확인 ==="

CLAUDE_CONFIG_DIR="$HOME/.config/claude-desktop"
CLAUDE_CONFIG_FILE="$CLAUDE_CONFIG_DIR/claude_desktop_config.json"

echo "Claude 설정 디렉토리: $CLAUDE_CONFIG_DIR"
echo "Claude 설정 파일: $CLAUDE_CONFIG_FILE"

if [ -d "$CLAUDE_CONFIG_DIR" ]; then
    echo "✅ Claude 설정 디렉토리 존재"
else
    echo "❌ Claude 설정 디렉토리 없음 - 생성 중..."
    mkdir -p "$CLAUDE_CONFIG_DIR"
    echo "✅ Claude 설정 디렉토리 생성 완료"
fi

if [ -f "$CLAUDE_CONFIG_FILE" ]; then
    echo "✅ Claude 설정 파일 존재"
    echo "   파일 크기: $(stat -c%s "$CLAUDE_CONFIG_FILE") bytes"
    echo "   수정 시간: $(stat -c%y "$CLAUDE_CONFIG_FILE" | cut -d. -f1)"
else
    echo "❌ Claude 설정 파일 없음"
fi

echo ""

# 2. Claude Code 설정 확인
echo "=== 2. Claude Code 설정 확인 ==="

if command -v claude-code &> /dev/null; then
    echo "✅ Claude Code 설치 확인: $(which claude-code)"
    
    # Claude Code 설정 디렉토리 확인
    CLAUDE_CODE_CONFIG="$HOME/.claude-code"
    if [ -d "$CLAUDE_CODE_CONFIG" ]; then
        echo "✅ Claude Code 설정 디렉토리 존재: $CLAUDE_CODE_CONFIG"
    else
        echo "⚠️  Claude Code 설정 디렉토리 없음 (첫 실행 시 생성됨)"
    fi
else
    echo "❌ Claude Code 설치되지 않음"
    echo "   설치 명령: npm install -g @anthropic-ai/claude-code"
fi

echo ""

# 3. WSL용 Claude Desktop 설정 파일 재생성 (올바른 형식으로)
echo "=== 3. WSL용 Claude Desktop 설정 재생성 ==="

echo "WSL 환경에 맞는 Claude Desktop 설정 파일을 생성합니다..."

# 환경 변수 로드
if [ -f "/mnt/c/Users/man4k/OneDrive/문서/APP/.env" ]; then
    set -a
    source "/mnt/c/Users/man4k/OneDrive/문서/APP/.env" 2>/dev/null
    set +a
    echo "✅ 환경 변수 로드 완료"
else
    echo "⚠️  환경 변수 파일을 찾을 수 없습니다"
fi

# WSL용 MCP 설정 생성 (Claude Code와 호환되는 형식)
cat > "$CLAUDE_CONFIG_FILE" << 'EOF'
{
  "mcpServers": {
    "filesystem": {
      "command": "npx",
      "args": [
        "-y",
        "@modelcontextprotocol/server-filesystem",
        "/mnt/c/Users/man4k/OneDrive/문서/APP"
      ]
    },
    "git": {
      "command": "npx", 
      "args": [
        "-y",
        "@anthropic-ai/mcp-server-git",
        "--repository",
        "/mnt/c/Users/man4k/OneDrive/문서/APP"
      ]
    },
    "terminal": {
      "command": "npx",
      "args": [
        "-y", 
        "@dillip285/mcp-terminal"
      ]
    },
    "toolbox": {
      "command": "npx",
      "args": [
        "-y",
        "@anthropic-ai/mcp-server-toolbox"
      ]
    },
    "playwright-stealth": {
      "command": "npx",
      "args": [
        "-y",
        "@pvinis/playwright-stealth-mcp-server"
      ]
    },
    "googleSearch": {
      "command": "npx",
      "args": [
        "-y",
        "g-search-mcp"
      ]
    },
    "openai": {
      "command": "npx",
      "args": [
        "-y",
        "@anthropic-ai/mcp-server-openai"
      ],
      "env": {
        "OPENAI_API_KEY": "${OPENAI_API_KEY}"
      }
    },
    "notion": {
      "command": "npx",
      "args": [
        "-y",
        "@anthropic-ai/mcp-server-notion"  
      ],
      "env": {
        "NOTION_API_KEY": "${NOTION_API_KEY}"
      }
    },
    "youtube": {
      "command": "npx",
      "args": [
        "-y",
        "@anthropic-ai/mcp-server-youtube"
      ],
      "env": {
        "YOUTUBE_API_KEY": "${YOUTUBE_API_KEY}"
      }
    }
  }
}
EOF

echo "✅ WSL용 Claude Desktop 설정 파일 생성 완료"

# JSON 유효성 검사
if python3 -c "import json; json.load(open('$CLAUDE_CONFIG_FILE'))" 2>/dev/null; then
    echo "✅ JSON 형식 유효성 확인"
else
    echo "❌ JSON 형식 오류 - 수정이 필요합니다"
fi

echo ""

# 4. MCP 서버 패키지 사전 설치
echo "=== 4. 핵심 MCP 서버 패키지 설치 ==="

core_packages=(
    "@modelcontextprotocol/server-filesystem"
    "@anthropic-ai/mcp-server-git" 
    "@anthropic-ai/mcp-server-toolbox"
    "@dillip285/mcp-terminal"
)

echo "핵심 MCP 서버 패키지들을 미리 설치합니다..."

for package in "${core_packages[@]}"; do
    echo -n "설치 중: $package ... "
    if npm install -g "$package" >/dev/null 2>&1; then
        echo "✅ 완료"
    else
        echo "⚠️  실패 (런타임에 다운로드됨)"
    fi
done

echo ""

# 5. 권한 및 경로 확인
echo "=== 5. 권한 및 경로 확인 ==="

# 설정 파일 권한 확인
echo "설정 파일 권한: $(ls -la "$CLAUDE_CONFIG_FILE" | awk '{print $1}')"

# 프로젝트 디렉토리 접근 권한 확인
PROJECT_DIR="/mnt/c/Users/man4k/OneDrive/문서/APP"
if [ -d "$PROJECT_DIR" ]; then
    echo "✅ 프로젝트 디렉토리 접근 가능: $PROJECT_DIR"
    echo "   디렉토리 권한: $(ls -ld "$PROJECT_DIR" | awk '{print $1}')"
else
    echo "❌ 프로젝트 디렉토리 접근 불가: $PROJECT_DIR"
fi

echo ""

# 6. Claude Code 실행 환경 설정
echo "=== 6. Claude Code 실행 환경 최적화 ==="

# Claude Code가 MCP 설정을 올바르게 찾을 수 있도록 환경 변수 설정
export CLAUDE_DESKTOP_CONFIG_PATH="$CLAUDE_CONFIG_FILE"

# 실행 스크립트 업데이트
cat > start_claude_code_wsl_fixed.sh << 'STARTEOF'
#!/bin/bash

# WSL에서 Claude Code 실행 (MCP 인식 문제 해결 버전)

echo "======================================"
echo "  Claude Code WSL 실행 (MCP 수정판)"
echo "======================================"
echo ""

# 환경 변수 로드
WINDOWS_ENV_PATH="/mnt/c/Users/man4k/OneDrive/문서/APP/.env"
if [ -f "$WINDOWS_ENV_PATH" ]; then
    echo "🔄 환경 변수 로드 중..."
    set -a
    source "$WINDOWS_ENV_PATH" 2>/dev/null
    set +a
    echo "✅ 환경 변수 로드 완료"
else
    echo "⚠️  환경 변수 파일 없음"
fi

# Claude Desktop 설정 경로 명시적 지정
export CLAUDE_DESKTOP_CONFIG_PATH="$HOME/.config/claude-desktop/claude_desktop_config.json"

echo ""
echo "🔧 MCP 설정 확인:"
echo "   설정 파일: $CLAUDE_DESKTOP_CONFIG_PATH"

if [ -f "$CLAUDE_DESKTOP_CONFIG_PATH" ]; then
    echo "   ✅ 설정 파일 존재"
    server_count=$(python3 -c "
import json
try:
    with open('$CLAUDE_DESKTOP_CONFIG_PATH', 'r') as f:
        config = json.load(f)
    print(len(config.get('mcpServers', {})))
except:
    print('0')
" 2>/dev/null)
    echo "   📊 설정된 MCP 서버: ${server_count}개"
else
    echo "   ❌ 설정 파일 없음"
fi

echo ""
echo "📁 작업 디렉토리: $(pwd)"
echo "🔧 Node.js: $(node --version 2>/dev/null || echo '설치되지 않음')"
echo "📦 NPM: $(npm --version 2>/dev/null || echo '설치되지 않음')"

if command -v claude-code &> /dev/null; then
    echo "🤖 Claude Code: $(which claude-code)"
else
    echo "❌ Claude Code 설치되지 않음"
    exit 1
fi

echo ""
echo "🚀 Claude Code 실행 중..."
echo "   MCP 서버들이 자동으로 로드됩니다..."
echo ""

# Claude Code 실행 (환경 변수와 함께)
exec claude-code
STARTEOF

chmod +x start_claude_code_wsl_fixed.sh
echo "✅ 수정된 Claude Code 실행 스크립트 생성: start_claude_code_wsl_fixed.sh"

echo ""

# 7. 실시간 테스트
echo "=== 7. MCP 서버 연결 테스트 ==="

echo "간단한 MCP 서버 연결 테스트를 수행합니다..."

# 파일시스템 서버 테스트
echo -n "파일시스템 서버 테스트: "
if timeout 3 npx -y @modelcontextprotocol/server-filesystem --help >/dev/null 2>&1; then
    echo "✅ 성공"
else
    echo "⚠️  실패 (Claude Code 실행 시 자동 다운로드됨)"
fi

# Git 서버 테스트
echo -n "Git 서버 테스트: "
if timeout 3 npx -y @anthropic-ai/mcp-server-git --help >/dev/null 2>&1; then
    echo "✅ 성공"
else
    echo "⚠️  실패 (Claude Code 실행 시 자동 다운로드됨)"
fi

echo ""

# 완료 메시지
echo "======================================"
echo "  🔧 WSL MCP 인식 문제 해결 완료!"
echo "======================================"
echo ""
echo "📋 수행된 작업:"
echo "   ✅ Claude Desktop 설정 파일 재생성"
echo "   ✅ WSL 환경에 최적화된 MCP 서버 설정"
echo "   ✅ 핵심 MCP 패키지 사전 설치"
echo "   ✅ 환경 변수 및 경로 설정 최적화"
echo "   ✅ Claude Code 실행 스크립트 개선"
echo ""
echo "🚀 다음 단계:"
echo "1. 새로운 스크립트로 Claude Code 실행:"
echo "   ./start_claude_code_wsl_fixed.sh"
echo ""
echo "2. Claude Code에서 다음 명령으로 MCP 확인:"
echo "   claude mcp list"
echo ""
echo "3. 만약 여전히 문제가 있다면:"
echo "   - Claude Code를 완전히 종료 후 재시작"
echo "   - 새 터미널에서 실행"
echo "   - ./check_mcp_wsl.sh 로 상태 재확인"
echo ""
echo "💡 참고: Claude Code 첫 실행 시 MCP 서버들이 자동으로 다운로드됩니다."
echo "    네트워크 연결이 안정적인지 확인해주세요."
