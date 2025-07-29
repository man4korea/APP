# WSL에서 MCP 서버 인식 안됨 문제 해결 가이드

## 🚨 현재 상황
VS Code WSL 환경에서 Claude를 실행했는데:
- `claude mcp list` → "No MCP servers configured" 
- MCP 서버가 인식되지 않음

## 🔧 즉시 해결 방법

### 1단계: Claude Code 설정 확인
VS Code 터미널에서 다음 명령 실행:

```bash
# 현재 선택지에서 "1. Yes" 선택
1

# 그 다음 MCP 인식 문제 해결 스크립트 실행
./fix_mcp_recognition.sh
```

### 2단계: 문제 해결 스크립트 실행
```bash
cd /mnt/c/Users/man4k/OneDrive/문서/APP
chmod +x fix_mcp_recognition.sh
./fix_mcp_recognition.sh
```

### 3단계: 수정된 Claude Code 실행
```bash
./start_claude_code_wsl_fixed.sh
```

## 🎯 주요 문제점들

### 문제 1: Claude Desktop 설정 파일 없음
**해결**: WSL 환경에 맞는 `~/.config/claude-desktop/claude_desktop_config.json` 생성

### 문제 2: 경로 설정 불일치
**해결**: Windows 경로(`C:\`)를 WSL 경로(`/mnt/c/`)로 올바르게 변환

### 문제 3: 환경 변수 연동 실패
**해결**: Windows .env 파일을 WSL에서 직접 로드하도록 설정

### 문제 4: MCP 서버 패키지 미설치
**해결**: 핵심 MCP 서버들을 사전 설치

## 🚀 빠른 복구 명령어

현재 VS Code 터미널에서 바로 실행:

```bash
# 1. 선택지에서 "1" 입력 (Yes 선택)
1

# 2. 작업 디렉토리로 이동
cd /mnt/c/Users/man4k/OneDrive/문서/APP

# 3. 통합 문제 해결 실행
./setup_complete_integration.sh

# 4. MCP 인식 문제 수정
./fix_mcp_recognition.sh

# 5. 수정된 Claude Code 실행
./start_claude_code_wsl_fixed.sh
```

## 🔍 설정 확인 방법

### MCP 서버 목록 확인
```bash
claude mcp list
```

### 설정 파일 확인
```bash
cat ~/.config/claude-desktop/claude_desktop_config.json
```

### 환경 변수 확인
```bash
source load_env_wsl.sh
echo $OPENAI_API_KEY | cut -c1-4  # API 키 앞 4자리 확인
```

## 📋 예상 결과

수정 후 `claude mcp list` 실행 시 다음과 같이 표시되어야 합니다:

```
Available MCP servers:
- filesystem
- git  
- terminal
- toolbox
- playwright-stealth
- googleSearch
- openai (if API key configured)
- notion (if API key configured)
- youtube (if API key configured)
```

## 🆘 여전히 문제가 있다면

1. **Claude Code 완전 종료 후 재시작**
2. **새 WSL 터미널에서 다시 시도**
3. **네트워크 연결 확인** (MCP 서버 다운로드용)
4. **권한 문제 확인**: `ls -la ~/.config/claude-desktop/`

---

**💡 팁**: 첫 번째 MCP 서버 실행 시 패키지 다운로드로 인해 시간이 걸릴 수 있습니다. 네트워크가 안정적인지 확인하세요.
