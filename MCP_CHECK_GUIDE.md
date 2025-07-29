# WSL에서 MCP 서버 설치 확인 방법

WSL 환경에서 Claude Desktop MCP 서버가 제대로 설치되고 설정되었는지 확인하는 방법들입니다.

## 🔍 기본 확인 명령어

### 1. 설정 파일 확인
```bash
# Claude Desktop 설정 파일 존재 확인
ls -la ~/.config/claude-desktop/claude_desktop_config.json

# 설정 파일 내용 확인
cat ~/.config/claude-desktop/claude_desktop_config.json | python3 -m json.tool
```

### 2. 필수 도구 확인
```bash
# Node.js와 NPM 버전 확인
node --version
npm --version

# Claude Code 설치 확인
claude-code --version
which claude-code
```

### 3. MCP 서버 패키지 확인
```bash
# 글로벌 NPM 패키지 목록
npm list -g --depth=0

# 특정 MCP 패키지 확인
npm list -g @modelcontextprotocol/server-filesystem
npm list -g @anthropic-ai/mcp-server-git
```

## 🧪 자동 확인 스크립트 실행

### 1. 전체 MCP 상태 확인
```bash
cd /mnt/c/Users/man4k/OneDrive/문서/APP
./check_mcp_wsl.sh
```

### 2. 개별 서버 테스트
```bash
./test_mcp_servers.sh
```

### 3. 전체 설정 검증
```bash
./verify_wsl_setup.sh
```

## 🔧 수동 테스트 명령어

### 1. 개별 MCP 서버 실행 테스트
```bash
# 파일시스템 서버 (기본)
npx -y @modelcontextprotocol/server-filesystem --help

# Git 서버
npx -y @anthropic-ai/mcp-server-git --help

# 도구 서버
npx -y @anthropic-ai/mcp-server-toolbox --help

# 터미널 서버
npx -y @dillip285/mcp-terminal --help
```

### 2. API 키 필요 서버 테스트
```bash
# 환경 변수 로드 후
source load_env_wsl.sh

# OpenAI 서버 (API 키 필요)
OPENAI_API_KEY=$OPENAI_API_KEY npx -y @anthropic-ai/mcp-server-openai --help

# Notion 서버 (API 키 필요)
NOTION_API_KEY=$NOTION_API_KEY npx -y @anthropic-ai/mcp-server-notion --help
```

### 3. 로컬 서버 파일 확인
```bash
# Shrimp 작업 관리자 서버
ls -la /mnt/c/Users/man4k/OneDrive/문서/APP/SHRIMP/index.js
node /mnt/c/Users/man4k/OneDrive/문서/APP/SHRIMP/index.js --help

# Edit File Lines 서버
ls -la node_modules/@joshuavial/edit-file-lines-mcp-server/dist/index.js
```

## 📊 설치 상태 체크리스트

### ✅ 완전 설치 확인 항목
- [ ] `~/.config/claude-desktop/claude_desktop_config.json` 파일 존재
- [ ] Node.js (v18+) 및 NPM 설치됨
- [ ] Claude Code 글로벌 설치됨
- [ ] 환경 변수 (.env) 로드 가능
- [ ] 기본 MCP 서버들 실행 가능
- [ ] API 키 설정된 서버들 실행 가능
- [ ] Git 리포지토리 설정됨

### 🚨 문제 해결

#### 설정 파일이 없는 경우:
```bash
mkdir -p ~/.config/claude-desktop
cp claude_desktop_config_wsl.json ~/.config/claude-desktop/claude_desktop_config.json
```

#### Node.js가 없는 경우:
```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
```

#### Claude Code가 없는 경우:
```bash
npm install -g @anthropic-ai/claude-code
```

#### MCP 패키지가 실행 안되는 경우:
```bash
# NPM 캐시 클리어
npm cache clean --force

# 패키지 재설치
npm install -g @anthropic-ai/claude-code
```

## 🚀 실제 작동 확인

### Claude Code에서 MCP 연결 확인
1. Claude Code 실행:
   ```bash
   ./start_claude_code_wsl.sh
   ```

2. Claude Code 인터페이스에서 다음 명령 테스트:
   - 파일 목록 보기
   - Git 상태 확인
   - 검색 기능 사용

### MCP 서버 로그 확인
Claude Code 실행 시 터미널에서 MCP 서버 연결 로그를 확인할 수 있습니다.

## 📞 추가 도움

문제가 지속되면:
1. `./check_mcp_wsl.sh` 결과 확인
2. `./verify_wsl_setup.sh` 점수 확인
3. 오류 메시지와 함께 문의

---

**참고**: WSL 환경에서는 Windows 전용 명령(`cmd`)이 포함된 MCP 서버는 실행되지 않습니다. 이는 정상적인 동작입니다.
