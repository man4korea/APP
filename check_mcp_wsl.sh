#!/bin/bash

# WSL 환경에서 MCP 서버 설치 상태 확인 스크립트

echo "======================================"
echo "  WSL MCP 서버 설치 상태 확인"
echo "======================================"
echo ""

# 1. Claude Desktop 설정 파일 확인
echo "=== 1. Claude Desktop 설정 파일 확인 ==="
claude_config="$HOME/.config/claude-desktop/claude_desktop_config.json"

if [ -f "$claude_config" ]; then
    echo "✅ Claude Desktop 설정 파일 존재: $claude_config"
    
    # JSON 파일 유효성 검사
    if python3 -c "import json; json.load(open('$claude_config'))" 2>/dev/null; then
        echo "✅ 설정 파일 JSON 형식 유효"
        
        # MCP 서버 목록 출력
        echo ""
        echo "📋 설정된 MCP 서버 목록:"
        python3 -c "
import json
with open('$claude_config', 'r') as f:
    config = json.load(f)
servers = config.get('mcpServers', {})
print(f'총 {len(servers)}개 서버 설정됨:')
for i, (name, server_config) in enumerate(servers.items(), 1):
    command = server_config.get('command', 'N/A')
    print(f'{i:2d}. {name:<25} → {command}')
"
    else
        echo "❌ 설정 파일 JSON 형식 오류"
    fi
else
    echo "❌ Claude Desktop 설정 파일 없음"
    echo "   예상 위치: $claude_config"
    echo "   WSL용 설정 파일 복사 필요: cp claude_desktop_config_wsl.json ~/.config/claude-desktop/claude_desktop_config.json"
fi
echo ""

# 2. MCP 서버별 실행 가능 여부 확인
echo "=== 2. MCP 서버 실행 가능 여부 확인 ==="

if [ -f "$claude_config" ]; then
    # Python으로 각 서버의 실행 가능성 확인
    python3 << 'EOF'
import json
import subprocess
import shutil
import os

def check_command_exists(cmd):
    """명령어가 시스템에 존재하는지 확인"""
    return shutil.which(cmd) is not None

def check_npm_package(package):
    """NPM 패키지가 글로벌로 설치되어 있는지 확인"""
    try:
        result = subprocess.run(['npm', 'list', '-g', '--depth=0'], 
                              capture_output=True, text=True, timeout=10)
        return package in result.stdout
    except:
        return False

def check_npx_package(package):
    """npx로 패키지 실행 가능한지 확인"""
    try:
        result = subprocess.run(['npx', '-y', '--help'], 
                              capture_output=True, text=True, timeout=5)
        return result.returncode == 0
    except:
        return False

config_file = os.path.expanduser('~/.config/claude-desktop/claude_desktop_config.json')
try:
    with open(config_file, 'r') as f:
        config = json.load(f)
    
    servers = config.get('mcpServers', {})
    
    for name, server_config in servers.items():
        command = server_config.get('command', '')
        args = server_config.get('args', [])
        
        status = "❌"
        note = ""
        
        if command == 'node':
            if check_command_exists('node'):
                if args and os.path.exists(args[0]):
                    status = "✅"
                    note = f"Node.js script: {args[0]}"
                else:
                    status = "⚠️ "
                    note = f"Node.js OK, but script not found: {args[0] if args else 'No script'}"
            else:
                note = "Node.js not installed"
        
        elif command == 'npx':
            if check_command_exists('npx'):
                status = "✅"
                package = args[1] if len(args) > 1 else args[0] if args else 'unknown'
                note = f"NPX package: {package}"
            else:
                note = "NPX not available"
        
        elif command == 'cmd':
            # Windows cmd 명령은 WSL에서 실행 불가
            status = "❌"
            note = "Windows CMD (WSL에서 실행 불가)"
        
        else:
            if check_command_exists(command):
                status = "✅"
                note = f"Command available: {command}"
            else:
                note = f"Command not found: {command}"
        
        print(f"{status} {name:<25} - {note}")

except Exception as e:
    print(f"설정 파일 읽기 오류: {e}")
EOF
else
    echo "설정 파일이 없어 서버 상태를 확인할 수 없습니다."
fi
echo ""

# 3. 필수 도구 설치 상태 확인
echo "=== 3. 필수 도구 설치 상태 확인 ==="
tools=("node" "npm" "npx" "python3" "git" "curl")

for tool in "${tools[@]}"; do
    if command -v "$tool" &> /dev/null; then
        version=$(eval "$tool --version 2>/dev/null | head -1" 2>/dev/null || echo "설치됨")
        echo "✅ $tool: $version"
    else
        echo "❌ $tool: 설치되지 않음"
    fi
done
echo ""

# 4. 글로벌 NPM 패키지 확인
echo "=== 4. 글로벌 NPM 패키지 확인 ==="
if command -v npm &> /dev/null; then
    echo "설치된 글로벌 NPM 패키지:"
    npm list -g --depth=0 2>/dev/null | grep -E "(claude-code|mcp-server|@anthropic)" | head -10 || echo "MCP 관련 패키지 없음"
else
    echo "NPM이 설치되지 않았습니다."
fi
echo ""

# 5. MCP 서버 실제 실행 테스트 (간단한 테스트)
echo "=== 5. MCP 서버 실행 테스트 ==="
echo "주요 MCP 서버들의 실행 가능 여부를 테스트합니다..."

# NPX 기반 서버들 테스트
mcp_packages=(
    "@modelcontextprotocol/server-filesystem"
    "@anthropic-ai/mcp-server-git"
    "@anthropic-ai/mcp-server-toolbox"
)

for package in "${mcp_packages[@]}"; do
    echo -n "테스트 중: $package ... "
    if timeout 5 npx -y "$package" --help >/dev/null 2>&1; then
        echo "✅ 실행 가능"
    elif timeout 10 npm view "$package" version >/dev/null 2>&1; then
        echo "⚠️  패키지 존재하나 실행 실패"
    else
        echo "❌ 패키지 없음"
    fi
done
echo ""

# 6. Claude Code 설치 확인
echo "=== 6. Claude Code 설치 확인 ==="
if command -v claude-code &> /dev/null; then
    echo "✅ Claude Code 설치됨: $(which claude-code)"
    
    # Claude Code 버전 확인
    version=$(claude-code --version 2>/dev/null || echo "버전 정보 없음")
    echo "   버전: $version"
    
    # Claude Code 설정 확인
    if [ -f "$HOME/.claude-code/config.json" ]; then
        echo "✅ Claude Code 설정 파일 존재"
    else
        echo "⚠️  Claude Code 설정 파일 없음 (첫 실행 시 생성됨)"
    fi
else
    echo "❌ Claude Code 설치되지 않음"
    echo "   설치 명령: npm install -g @anthropic-ai/claude-code"
fi
echo ""

# 7. 네트워크 연결 확인
echo "=== 7. 네트워크 연결 확인 ==="
echo -n "NPM 레지스트리 연결: "
if npm ping >/dev/null 2>&1; then
    echo "✅ 정상"
else
    echo "❌ 연결 실패"
fi

echo -n "GitHub 연결: "
if ping -c 1 -W 3 github.com >/dev/null 2>&1; then
    echo "✅ 정상"
else
    echo "❌ 연결 실패"
fi
echo ""

# 8. 환경 변수 확인
echo "=== 8. 환경 변수 확인 ==="
if [ -f ".env" ]; then
    echo "✅ .env 파일 존재"
    source .env 2>/dev/null
    
    # API 키 확인 (값은 표시하지 않음)
    api_keys=("OPENAI_API_KEY" "ANTHROPIC_API_KEY" "GITHUB_TOKEN" "NOTION_API_KEY")
    for key in "${api_keys[@]}"; do
        if [[ -n "${!key}" ]]; then
            echo "✅ $key: 설정됨"
        else
            echo "⚠️  $key: 설정되지 않음"
        fi
    done
else
    echo "❌ .env 파일 없음"
fi
echo ""

# 9. 요약 및 권장사항
echo "======================================"
echo "  📊 MCP 설치 상태 요약"
echo "======================================"

# 간단한 상태 점검
has_config=$([ -f "$claude_config" ] && echo "1" || echo "0")
has_node=$(command -v node &> /dev/null && echo "1" || echo "0")
has_npm=$(command -v npm &> /dev/null && echo "1" || echo "0")
has_claude_code=$(command -v claude-code &> /dev/null && echo "1" || echo "0")

total_score=$((has_config + has_node + has_npm + has_claude_code))

echo "설치 상태: $total_score/4"
echo ""

if [ $total_score -eq 4 ]; then
    echo "🎉 완벽! MCP 서버 환경이 완전히 설정되었습니다."
    echo ""
    echo "다음 단계:"
    echo "1. Claude Code 실행: ./start_claude_code_wsl.sh"
    echo "2. MCP 서버 연결 테스트"
elif [ $total_score -ge 2 ]; then
    echo "⚠️  부분적으로 설정됨. 일부 구성 요소가 누락되었습니다."
    echo ""
    echo "해결 방법:"
    [ $has_config -eq 0 ] && echo "- Claude Desktop 설정: cp claude_desktop_config_wsl.json ~/.config/claude-desktop/claude_desktop_config.json"
    [ $has_node -eq 0 ] && echo "- Node.js 설치: curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash - && sudo apt install -y nodejs"
    [ $has_npm -eq 0 ] && echo "- NPM은 Node.js와 함께 설치됩니다"
    [ $has_claude_code -eq 0 ] && echo "- Claude Code 설치: npm install -g @anthropic-ai/claude-code"
else
    echo "❌ 설정이 불완전합니다. 전체 설정을 다시 실행하세요."
    echo ""
    echo "해결 방법:"
    echo "./complete_wsl_setup.sh 실행"
fi

echo ""
echo "🔧 추가 확인 방법:"
echo "- MCP 서버 개별 테스트: npx -y @modelcontextprotocol/server-filesystem --help"
echo "- Claude Code 실행 테스트: claude-code --help"
echo "- 전체 검증: ./verify_wsl_setup.sh"
