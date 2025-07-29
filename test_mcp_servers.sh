#!/bin/bash

# 개별 MCP 서버 실행 테스트 스크립트

echo "======================================"
echo "  개별 MCP 서버 실행 테스트"
echo "======================================"
echo ""

# 환경 변수 로드
if [ -f ".env" ]; then
    source .env
    echo "✅ 환경 변수 로드 완료"
else
    echo "⚠️  .env 파일이 없습니다. 일부 서버는 실행되지 않을 수 있습니다."
fi
echo ""

# 테스트할 MCP 서버 목록
declare -A mcp_servers=(
    ["filesystem"]="npx -y @modelcontextprotocol/server-filesystem /mnt/c/Users/man4k/OneDrive/문서/APP"
    ["git"]="npx -y @anthropic-ai/mcp-server-git --repository /mnt/c/Users/man4k/OneDrive/문서/APP"
    ["toolbox"]="npx -y @anthropic-ai/mcp-server-toolbox"
    ["playwright-stealth"]="npx -y @pvinis/playwright-stealth-mcp-server"
    ["terminal"]="npx -y @dillip285/mcp-terminal"
    ["text-editor"]="npx mcp-server-text-editor"
    ["googleSearch"]="npx -y g-search-mcp"
)

# API 키가 필요한 서버들
declare -A api_servers=(
    ["openai"]="npx -y @anthropic-ai/mcp-server-openai"
    ["notion"]="npx -y @anthropic-ai/mcp-server-notion"
    ["youtube"]="npx -y @anthropic-ai/mcp-server-youtube"
)

echo "=== MCP 서버 개별 실행 테스트 ==="
echo ""

# 기본 서버들 테스트
echo "🔧 기본 MCP 서버 테스트:"
for server_name in "${!mcp_servers[@]}"; do
    command="${mcp_servers[$server_name]}"
    echo -n "  $server_name: "
    
    # 5초 타임아웃으로 help 명령 실행
    if timeout 5 bash -c "$command --help" >/dev/null 2>&1; then
        echo "✅ 실행 가능"
    else
        # 패키지 존재 여부 확인
        package_name=$(echo "$command" | awk '{print $3}')
        if timeout 10 npm view "$package_name" version >/dev/null 2>&1; then
            echo "⚠️  패키지 존재하나 실행 실패"
        else
            echo "❌ 패키지 없음 또는 실행 불가"
        fi
    fi
done

echo ""
echo "🔑 API 키 필요 서버 테스트:"

# OpenAI 서버 테스트
echo -n "  openai: "
if [[ -n "$OPENAI_API_KEY" ]]; then
    if timeout 5 bash -c "OPENAI_API_KEY='$OPENAI_API_KEY' ${api_servers[openai]} --help" >/dev/null 2>&1; then
        echo "✅ 실행 가능 (API 키 설정됨)"
    else
        echo "⚠️  API 키 있으나 실행 실패"
    fi
else
    echo "❌ OPENAI_API_KEY 설정 필요"
fi

# Notion 서버 테스트
echo -n "  notion: "
if [[ -n "$NOTION_API_KEY" ]]; then
    if timeout 5 bash -c "NOTION_API_KEY='$NOTION_API_KEY' ${api_servers[notion]} --help" >/dev/null 2>&1; then
        echo "✅ 실행 가능 (API 키 설정됨)"
    else
        echo "⚠️  API 키 있으나 실행 실패"
    fi
else
    echo "❌ NOTION_API_KEY 설정 필요"
fi

# YouTube 서버 테스트
echo -n "  youtube: "
if [[ -n "$YOUTUBE_API_KEY" ]]; then
    if timeout 5 bash -c "YOUTUBE_API_KEY='$YOUTUBE_API_KEY' ${api_servers[youtube]} --help" >/dev/null 2>&1; then
        echo "✅ 실행 가능 (API 키 설정됨)"
    else
        echo "⚠️  API 키 있으나 실행 실패"
    fi
else
    echo "❌ YOUTUBE_API_KEY 설정 필요"
fi

echo ""
echo "🏗️  로컬 서버 테스트:"

# Shrimp Task Manager 테스트
echo -n "  shrimp-task-manager: "
shrimp_path="/mnt/c/Users/man4k/OneDrive/문서/APP/SHRIMP/index.js"
if [ -f "$shrimp_path" ]; then
    if timeout 5 node "$shrimp_path" --help >/dev/null 2>&1; then
        echo "✅ 실행 가능"
    else
        echo "⚠️  파일 존재하나 실행 실패"
    fi
else
    echo "❌ Shrimp 서버 파일 없음: $shrimp_path"
fi

# Edit File Lines 서버 테스트  
echo -n "  edit-file-lines: "
edit_path="/mnt/c/Users/man4k/OneDrive/문서/APP/node_modules/@joshuavial/edit-file-lines-mcp-server/dist/index.js"
if [ -f "$edit_path" ]; then
    if timeout 5 node "$edit_path" --help >/dev/null 2>&1; then
        echo "✅ 실행 가능"
    else
        echo "⚠️  파일 존재하나 실행 실패"
    fi
else
    echo "❌ Edit File Lines 서버 없음"
    echo "    설치: npm install @joshuavial/edit-file-lines-mcp-server"
fi

echo ""
echo "=== 상세 실행 테스트 (선택적) ==="
echo ""
echo "개별 서버를 실제로 실행해보려면 다음 명령을 사용하세요:"
echo ""

echo "1. 파일시스템 서버:"
echo "   npx -y @modelcontextprotocol/server-filesystem /mnt/c/Users/man4k/OneDrive/문서/APP"
echo ""

echo "2. Git 서버:"
echo "   npx -y @anthropic-ai/mcp-server-git --repository /mnt/c/Users/man4k/OneDrive/문서/APP"
echo ""

echo "3. 도구 서버:"
echo "   npx -y @anthropic-ai/mcp-server-toolbox"
echo ""

echo "4. 터미널 서버:"
echo "   npx -y @dillip285/mcp-terminal"
echo ""

echo "⚠️  주의: 실제 실행 시 서버가 대기 상태가 됩니다."
echo "    Ctrl+C로 중단할 수 있습니다."
echo ""

echo "=== 실시간 연결 테스트 ==="
echo ""
read -p "실시간으로 MCP 서버 연결을 테스트해보시겠습니까? (y/N): " test_realtime

if [[ $test_realtime =~ ^[Yy]$ ]]; then
    echo ""
    echo "파일시스템 서버를 5초간 실행합니다..."
    echo "서버가 시작되면 Claude Code에서 연결할 수 있습니다."
    echo ""
    
    # 파일시스템 서버 5초간 실행
    timeout 5 npx -y @modelcontextprotocol/server-filesystem /mnt/c/Users/man4k/OneDrive/문서/APP &
    server_pid=$!
    
    sleep 1
    if ps -p $server_pid > /dev/null; then
        echo "✅ 파일시스템 서버가 성공적으로 시작되었습니다!"
        echo "   프로세스 ID: $server_pid"
        sleep 4
        echo "✅ 테스트 완료. 서버를 종료합니다."
    else
        echo "❌ 파일시스템 서버 시작 실패"
    fi
    
    wait $server_pid 2>/dev/null
fi

echo ""
echo "======================================"
echo "  📋 MCP 서버 테스트 완료"
echo "======================================"
echo ""
echo "다음 단계:"
echo "1. Claude Code 실행: ./start_claude_code_wsl.sh"
echo "2. Claude Code에서 MCP 서버 연결 확인"
echo "3. 파일 시스템, Git 등 기능 테스트"
echo ""
echo "문제 해결:"
echo "- 패키지 설치: npm install -g <패키지명>"
echo "- API 키 설정: .env 파일 편집"
echo "- 권한 문제: sudo 없이 실행하세요"
