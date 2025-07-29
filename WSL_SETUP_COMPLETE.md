# WSL 환경 MCP 서버 설정 완료 가이드

## 🎉 설정 완료!

Claude Desktop 환경의 MCP 서버가 WSL 환경에 성공적으로 이식되었습니다.

## 📁 생성된 파일들

### 핵심 설정 파일
- `claude_desktop_config_wsl.json` - WSL용 Claude Desktop 설정
- `load_env_wsl.sh` - WSL 환경 변수 로드 스크립트
- `.env` - 환경 변수 파일 (기존)

### 실행 스크립트
- `complete_wsl_setup.sh` - 전체 WSL 환경 설정 스크립트
- `start_claude_code_wsl.sh` - Claude Code WSL 실행 스크립트
- `setup_git_collaboration.sh` - Git 협업 환경 설정 스크립트
- `verify_wsl_setup.sh` - 설정 검증 스크립트
- `setup_permissions.sh` - 실행 권한 설정 스크립트

### Windows 실행 파일
- `start_claude_code_wsl.bat` - Windows에서 WSL Claude Code 실행

## 🚀 사용 방법

### 1. 첫 설정 (한 번만 실행)

WSL Ubuntu 터미널에서:

```bash
# APP 디렉토리로 이동
cd /mnt/c/Users/man4k/OneDrive/문서/APP

# 실행 권한 설정
bash setup_permissions.sh

# 전체 환경 설정 실행
./complete_wsl_setup.sh

# 설정 검증
./verify_wsl_setup.sh
```

### 2. 일상적인 사용

#### WSL에서 Claude Code 실행
```bash
cd /mnt/c/Users/man4k/OneDrive/문서/APP
source load_env_wsl.sh
./start_claude_code_wsl.sh
```

#### Windows에서 바로 실행
`start_claude_code_wsl.bat` 파일을 더블클릭

### 3. Git 협업

```bash
# 새 기능 브랜치 생성
git checkout -b feature/새기능명

# 작업 후 커밋
git add .
git commit -m "[AI이름] 작업 내용 설명"

# 원격 저장소에 푸시
git push origin feature/새기능명
```

## 🤖 AI 협업 구조

```
├── chatgpt/          # ChatGPT 작업 영역
├── gemini/           # Gemini 작업 영역  
├── claude-code/      # Claude Code 작업 영역
├── claude-desktop/   # Claude Desktop 작업 영역
├── shared/           # 공통 리소스
└── .env             # 공유 환경 변수
```

## 🔧 MCP 서버 구성

WSL 환경에서 다음 MCP 서버들이 활성화됩니다:

### 개발 도구
- `playwright-stealth` - 웹 자동화
- `terminal` - 터미널 명령 실행
- `filesystem` - 파일 시스템 접근
- `git` - Git 버전 관리

### AI 서비스 연동
- `openai-gpt-image-mcp` - OpenAI API
- `context7-mcp` - 컨텍스트 검색
- `github` - GitHub 연동

### 유틸리티
- `googleSearch` - 구글 검색
- `toolbox` - 유틸리티 도구
- `text-editor` - 텍스트 편집
- `shrimp-task-manager` - 작업 관리

## 🔐 보안 설정

- API 키는 `.env` 파일에 안전하게 저장
- `.gitignore`로 민감한 파일 보호
- 개인 설정은 로컬에만 유지

## 🐛 문제 해결

### 환경 변수 로드 안됨
```bash
source load_env_wsl.sh
```

### Git 권한 문제
- GitHub token 확인
- Git 사용자 정보 재설정

### MCP 서버 연결 실패
- 설정 파일 경로 확인
- Node.js 버전 확인
- 네트워크 연결 확인

### Claude Code 실행 안됨
```bash
npm install -g @anthropic-ai/claude-code
```

## 📋 체크리스트

설정이 완료되면 다음 항목들이 모두 ✅ 상태여야 합니다:

- [ ] WSL Ubuntu 설치 및 실행
- [ ] Node.js & NPM 설치
- [ ] Claude Code 설치
- [ ] 환경 변수 로드 성공
- [ ] Git 사용자 설정 완료
- [ ] MCP 서버 설정 파일 생성
- [ ] AI 협업 디렉토리 구조 생성
- [ ] 네트워크 연결 정상

## 🎯 다음 단계

1. **Claude Code 실행**: `./start_claude_code_wsl.sh`
2. **첫 번째 AI 협업 프로젝트 시작**
3. **다른 AI 도구들과 Git을 통한 협업**

## 📞 지원

문제가 발생하면:
1. `./verify_wsl_setup.sh`로 상태 확인
2. 오류 메시지와 함께 문의
3. 설정 파일 백업 후 재설정 시도

---

✨ **AI 협업 환경이 완성되었습니다!**  
ChatGPT, Gemini, Claude Code, Claude Desktop이 모두 연결되어 함께 작업할 수 있습니다.
