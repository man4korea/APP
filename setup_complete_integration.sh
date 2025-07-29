#!/bin/bash

# Windows .env 파일과 WSL 완전 통합 설정 스크립트
# 한 번 실행으로 모든 환경 변수 공유 설정 완료

echo "======================================"
echo "  Windows .env ↔ WSL 완전 통합"
echo "======================================"
echo ""

WINDOWS_ENV_PATH="/mnt/c/Users/man4k/OneDrive/문서/APP/.env"

# 1단계: Windows .env 파일 확인
echo "=== 1단계: Windows .env 파일 확인 ==="
if [ -f "$WINDOWS_ENV_PATH" ]; then
    echo "✅ Windows .env 파일 발견"
    echo "   경로: $WINDOWS_ENV_PATH"
    echo "   크기: $(stat -c%s "$WINDOWS_ENV_PATH") bytes"
    
    # API 키 개수 미리 확인
    api_count=$(grep -c "API_KEY\|TOKEN" "$WINDOWS_ENV_PATH" 2>/dev/null || echo "0")
    echo "   API 키/토큰: $api_count개"
else
    echo "❌ Windows .env 파일을 찾을 수 없습니다!"
    echo "   예상 경로: $WINDOWS_ENV_PATH"
    exit 1
fi

echo ""

# 2단계: 기존 WSL .env 파일 백업
echo "=== 2단계: 기존 설정 백업 ==="
if [ -f ".env" ] && [ ! -L ".env" ]; then
    backup_name=".env.backup.$(date +%Y%m%d_%H%M%S)"
    mv ".env" "$backup_name"
    echo "✅ 기존 .env 파일 백업: $backup_name"
elif [ -L ".env" ]; then
    echo "✅ 이미 심볼릭 링크로 설정됨"
else
    echo "✅ 기존 .env 파일 없음 (새로 설정)"
fi

# 홈 디렉토리도 확인
if [ -f "$HOME/.env" ] && [ ! -L "$HOME/.env" ]; then
    backup_name="$HOME/.env.backup.$(date +%Y%m%d_%H%M%S)"
    mv "$HOME/.env" "$backup_name"
    echo "✅ 홈 디렉토리 .env 파일 백업: $backup_name"
fi

echo ""

# 3단계: 심볼릭 링크 생성
echo "=== 3단계: 심볼릭 링크 생성 ==="

# 프로젝트 디렉토리에 심볼릭 링크
ln -sf "$WINDOWS_ENV_PATH" ".env"
echo "✅ 프로젝트 .env → Windows .env 링크 생성"

# 홈 디렉토리에도 심볼릭 링크 (전역 접근용)
ln -sf "$WINDOWS_ENV_PATH" "$HOME/.env"
echo "✅ 홈 디렉토리 ~/.env → Windows .env 링크 생성"

echo ""

# 4단계: 쉘 설정 업데이트
echo "=== 4단계: 쉘 자동 로드 설정 ==="

# .bashrc 설정
bashrc_config="
# Windows .env 파일 자동 로드 (AI 협업 환경)
if [ -f \"$WINDOWS_ENV_PATH\" ]; then
    set -a  # 자동 export 활성화
    source \"$WINDOWS_ENV_PATH\" 2>/dev/null
    set +a  # 자동 export 비활성화
fi"

if ! grep -q "Windows .env 파일 자동 로드" "$HOME/.bashrc" 2>/dev/null; then
    echo "$bashrc_config" >> "$HOME/.bashrc"
    echo "✅ .bashrc에 자동 환경 변수 로드 설정 추가"
else
    echo "✅ .bashrc 설정 이미 존재"
fi

# .profile 설정 (다른 쉘 지원)
if [ -f "$HOME/.profile" ]; then
    if ! grep -q "Windows .env 파일 자동 로드" "$HOME/.profile" 2>/dev/null; then
        echo "$bashrc_config" >> "$HOME/.profile"
        echo "✅ .profile에 자동 환경 변수 로드 설정 추가"
    else
        echo "✅ .profile 설정 이미 존재"
    fi
fi

echo ""

# 5단계: Claude Desktop MCP 설정 업데이트  
echo "=== 5단계: Claude Desktop MCP 설정 ==="

# MCP 설정 디렉토리 생성
mkdir -p ~/.config/claude-desktop

# Windows .env 파일을 직접 참조하는 MCP 설정 생성
cat > ~/.config/claude-desktop/claude_desktop_config.json << 'MCPEOF'
{
  "mcpServers": {
    "playwright-stealth": {
      "command": "npx",
      "args": ["-y", "@pvinis/playwright-stealth-mcp-server"]
    },
    "terminal": {
      "command": "npx", 
      "args": ["-y", "@dillip285/mcp-terminal"]
    },
    "context7-mcp": {
      "command": "npx",
      "args": [
        "-y", "@smithery/cli@latest", "run", "@upstash/context7-mcp",
        "--key", "89871a9a-cf95-4de7-ae49-1d380312c282",
        "--profile", "evolutionary-termite-Omv5KV"
      ]
    },
    "googleSearch": {
      "command": "npx",
      "args": ["-y", "g-search-mcp"]
    },
    "filesystem": {
      "command": "npx",
      "args": [
        "-y", "@modelcontextprotocol/server-filesystem",
        "/mnt/c/Users/man4k/OneDrive/문서/APP"
      ]
    },
    "text-editor": {
      "command": "npx",
      "args": ["mcp-server-text-editor"]
    },
    "github": {
      "command": "npx",
      "args": [
        "-y", "@smithery/cli@latest", "run", "@smithery-ai/github",
        "--key", "89871a9a-cf95-4de7-ae49-1d380312c282",
        "--profile", "evolutionary-termite-Omv5KV"
      ]
    },
    "firebase": {
      "command": "npx",
      "args": ["-y", "firebase-tools@latest", "experimental:mcp"]
    },
    "git": {
      "command": "npx",
      "args": [
        "-y", "@anthropic-ai/mcp-server-git",
        "--repository", "/mnt/c/Users/man4k/OneDrive/문서/APP"
      ]
    },
    "toolbox": {
      "command": "npx",
      "args": ["-y", "@anthropic-ai/mcp-server-toolbox"]
    },
    "server-sequential-thinking": {
      "command": "npx",
      "args": ["-y", "@anthropic-ai/mcp-server-sequential-thinking"]
    },
    "mem0-memory-mcp": {
      "command": "npx", 
      "args": ["-y", "@anthropic-ai/mcp-server-memory"]
    },
    "playwright-test": {
      "command": "npx",
      "args": ["-y", "@anthropic-ai/mcp-server-playwright"]
    },
    "notion-api-mcp": {
      "command": "npx",
      "args": ["-y", "@anthropic-ai/mcp-server-notion"],
      "env": {
        "NOTION_API_KEY": "${NOTION_API_KEY}"
      }
    },
    "youtube-data-mcp-server": {
      "command": "npx", 
      "args": ["-y", "@anthropic-ai/mcp-server-youtube"],
      "env": {
        "YOUTUBE_API_KEY": "${YOUTUBE_API_KEY}"
      }
    },
    "openai-gpt-image-mcp": {
      "command": "npx",
      "args": ["-y", "@anthropic-ai/mcp-server-openai"],
      "env": {
        "OPENAI_API_KEY": "${OPENAI_API_KEY}"
      }
    },
    "shrimp-task-manager": {
      "command": "node",
      "args": ["/mnt/c/Users/man4k/OneDrive/문서/APP/SHRIMP/index.js"],
      "env": {
        "DATA_DIR": "/mnt/c/Users/man4k/OneDrive/문서/APP/SHRIMP",
        "TEMPLATES_USE": "templates_en", 
        "ENABLE_GUI": "true"
      }
    },
    "edit-file-lines": {
      "command": "node",
      "args": ["/mnt/c/Users/man4k/OneDrive/문서/APP/node_modules/@joshuavial/edit-file-lines-mcp-server/dist/index.js"]
    }
  }
}
MCPEOF

echo "✅ Claude Desktop MCP 설정 파일 생성 완료"

echo ""

# 6단계: 환경 변수 로드 및 테스트
echo "=== 6단계: 환경 변수 로드 테스트 ==="

# Windows .env 파일에서 환경 변수 로드
set -a
source "$WINDOWS_ENV_PATH" 2>/dev/null
set +a

echo "📋 주요 환경 변수 확인:"

# 기본 설정 변수
basic_vars=("GITHUB_USERNAME" "GIT_USER_NAME" "GIT_USER_EMAIL" "PROJECT_PATH")
for var in "${basic_vars[@]}"; do
    if [[ -n "${!var}" ]]; then
        echo "✅ $var: ${!var}"
    else
        echo "⚠️  $var: 설정되지 않음"
    fi
done

echo ""
echo "🔑 API 키 확인 (보안상 마스킹):"

# API 키 확인
api_vars=("OPENAI_API_KEY" "ANTHROPIC_API_KEY" "GITHUB_TOKEN" "GOOGLE_API_KEY" "NOTION_API_KEY" "YOUTUBE_API_KEY" "GEMINI_API_KEY")
loaded_keys=0
for var in "${api_vars[@]}"; do
    if [[ -n "${!var}" ]]; then
        # 처음 4자리와 마지막 4자리만 표시
        key_value="${!var}"
        if [[ ${#key_value} -gt 8 ]]; then
            masked="${key_value:0:4}...${key_value: -4}"
        else
            masked="***"
        fi
        echo "✅ $var: $masked"
        ((loaded_keys++))
    else
        echo "❌ $var: 설정되지 않음"
    fi
done

echo ""
echo "📊 로드 완료: $loaded_keys개 API 키 확인됨"

echo ""

# 7단계: Git 설정 자동 적용
echo "=== 7단계: Git 사용자 설정 ==="
if [[ -n "$GIT_USER_NAME" ]] && [[ -n "$GIT_USER_EMAIL" ]]; then
    git config --global user.name "$GIT_USER_NAME"
    git config --global user.email "$GIT_USER_EMAIL"
    echo "✅ Git 사용자 정보 설정 완료"
    echo "   이름: $GIT_USER_NAME"
    echo "   이메일: $GIT_USER_EMAIL"
else
    echo "⚠️  Git 사용자 정보가 .env 파일에 없습니다"
    echo "   GIT_USER_NAME과 GIT_USER_EMAIL을 .env 파일에 추가하세요"
fi

echo ""

# 8단계: load_env_wsl.sh 스크립트 최적화
echo "=== 8단계: 로드 스크립트 최적화 ==="

cat > load_env_wsl.sh << 'LOADEOF'
#!/bin/bash

# 최적화된 Windows .env 파일 로드 스크립트
# Windows와 WSL 간 완전한 환경 변수 공유

WINDOWS_ENV_PATH="/mnt/c/Users/man4k/OneDrive/문서/APP/.env"

echo "🔄 Windows .env 파일 로드 중..."

if [ ! -f "$WINDOWS_ENV_PATH" ]; then
    echo "❌ Windows .env 파일을 찾을 수 없습니다: $WINDOWS_ENV_PATH"
    return 1 2>/dev/null || exit 1
fi

# 환경 변수 로드 (자동 export)
set -a
source "$WINDOWS_ENV_PATH" 2>/dev/null
set +a

echo "✅ 환경 변수 로드 완료!"

# 로드된 API 키 개수 확인
api_count=0
for key in OPENAI_API_KEY ANTHROPIC_API_KEY GITHUB_TOKEN GOOGLE_API_KEY NOTION_API_KEY YOUTUBE_API_KEY GEMINI_API_KEY; do
    [[ -n "${!key}" ]] && ((api_count++))
done

echo "📊 상태: GitHub($GITHUB_USERNAME), Git($GIT_USER_NAME), API키(${api_count}개)"

# Git 설정 자동 적용
if [[ -n "$GIT_USER_NAME" ]] && [[ -n "$GIT_USER_EMAIL" ]]; then
    git config --global user.name "$GIT_USER_NAME" 2>/dev/null
    git config --global user.email "$GIT_USER_EMAIL" 2>/dev/null
fi

# MCP 설정 확인
if [ -f ~/.config/claude-desktop/claude_desktop_config.json ]; then
    echo "✅ Claude Desktop MCP 설정 준비 완료"
else
    echo "⚠️  MCP 설정 파일이 없습니다. setup_complete_integration.sh를 실행하세요."
fi
LOADEOF

chmod +x load_env_wsl.sh
echo "✅ load_env_wsl.sh 스크립트 최적화 완료"

echo ""

# 9단계: 통합 확인 테스트
echo "=== 9단계: 통합 확인 테스트 ==="

# 심볼릭 링크 확인
echo "🔗 심볼릭 링크 상태:"
if [ -L ".env" ]; then
    echo "✅ 프로젝트 .env 링크: $(readlink .env)"
else
    echo "❌ 프로젝트 .env 링크 실패"
fi

if [ -L "$HOME/.env" ]; then
    echo "✅ 홈 .env 링크: $(readlink $HOME/.env)"
else
    echo "❌ 홈 .env 링크 실패"
fi

# 환경 변수 접근 테스트
echo ""
echo "🧪 환경 변수 접근 테스트:"
if [[ -n "$GITHUB_USERNAME" ]]; then
    echo "✅ GitHub Username: $GITHUB_USERNAME"
else
    echo "❌ GitHub Username 로드 실패"
fi

if [[ -n "$OPENAI_API_KEY" ]]; then
    echo "✅ OpenAI API Key: ${OPENAI_API_KEY:0:4}...${OPENAI_API_KEY: -4}"
else
    echo "❌ OpenAI API Key 로드 실패"
fi

echo ""

# 완료 메시지
echo "======================================"
echo "  🎉 Windows .env ↔ WSL 통합 완료!"
echo "======================================"
echo ""
echo "📋 완료된 설정:"
echo "   ✅ Windows .env → WSL 심볼릭 링크"
echo "   ✅ 쉘 자동 로드 설정 (.bashrc/.profile)"
echo "   ✅ Claude Desktop MCP 설정 최적화"
echo "   ✅ Git 사용자 정보 자동 설정"
echo "   ✅ 환경 변수 로드 스크립트 최적화"
echo ""
echo "🚀 사용 방법:"
echo "   1. 새 터미널 열기 → 환경 변수 자동 로드됨"
echo "   2. 환경 변수 수동 로드: source load_env_wsl.sh"
echo "   3. Claude Code 실행: ./start_claude_code_wsl.sh"
echo ""
echo "💡 장점:"
echo "   • Windows에서 .env 파일 수정 → WSL에서 즉시 반영"
echo "   • 하나의 .env 파일로 모든 AI 도구 관리"
echo "   • API 키 중앙 집중식 보안 관리"
echo "   • 실시간 설정 변경 가능"
echo ""
echo "⚡ 다음 단계:"
echo "1. 새 터미널 열어서 환경 변수 자동 로드 확인"
echo "2. ./check_mcp_wsl.sh 실행하여 MCP 서버 상태 확인"
echo "3. ./start_claude_code_wsl.sh 실행하여 Claude Code 시작"
echo ""
echo "🔄 Windows .env 파일 변경 시 WSL에서 바로 사용 가능!"
