# 📁 C:\xampp\htdocs\BPM\scripts\install-git-mcp.md
# Create at 2508021950 Ver1.00

# Claude Code에 Git MCP 설치 가이드

## 🔧 Git MCP 설치 명령어

### **방법 1: Claude Code 터미널에서 직접 설치**
```bash
# Claude Code 터미널에서 실행
claude code mcp install @anthropic-ai/mcp-server-git --repository "C:\xampp\htdocs\BPM"
```

### **방법 2: npm을 통한 설치**
```bash
# 전역 설치
npm install -g @anthropic-ai/mcp-server-git

# Claude Code MCP 서버로 등록
claude code mcp add git --command "npx" --args "@anthropic-ai/mcp-server-git" --args "--repository" --args "C:\xampp\htdocs\BPM"
```

### **방법 3: 설정 파일 직접 수정 (고급)**
Claude Code 설정 파일 위치: `~/.claude-code/config.json`

```json
{
  "mcpServers": {
    "git": {
      "command": "npx",
      "args": [
        "-y",
        "@anthropic-ai/mcp-server-git",
        "--repository",
        "C:\\xampp\\htdocs\\BPM"
      ]
    }
  }
}
```

## 📋 설치 후 확인

### **1. MCP 서버 목록 확인**
```bash
claude code mcp list
```

**예상 결과**: git MCP가 목록에 표시되어야 함

### **2. Git 상태 확인**
```bash
# Claude Code에서 git 도구 사용 테스트
git status
git log --oneline -5
```

### **3. Git 기본 설정 (필요시)**
```bash
# 사용자 정보 설정
git config user.name "BPM Developer"
git config user.email "dev@bmp-system.com"

# 기본 브랜치 설정
git config init.defaultBranch main
```

## 🚀 사용 예시

### **기본 Git 명령어**
```bash
# 상태 확인
git status

# 변경사항 추가
git add .

# 커밋
git commit -m "feat: 새 기능 추가"

# 푸시
git push origin main

# 로그 확인
git log --oneline -10
```

### **브랜치 관리**
```bash
# 새 브랜치 생성
git checkout -b feature/organization-module

# 브랜치 목록
git branch -a

# 브랜치 전환
git checkout main

# 브랜치 병합
git merge feature/organization-module
```

## ⚠️ 주의사항

1. **경로 설정**: `C:\xampp\htdocs\BPM`이 정확한 경로인지 확인
2. **권한 문제**: 관리자 권한으로 실행 필요할 수 있음
3. **네트워크**: 인터넷 연결 필요 (npm 패키지 다운로드)
4. **Git 설치**: 시스템에 Git이 먼저 설치되어 있어야 함

## 🔍 문제 해결

### **Git이 설치되지 않은 경우**
```bash
# Git 설치 확인
git --version

# Git 설치 (Windows)
winget install Git.Git
```

### **npm 오류가 발생하는 경우**
```bash
# npm 캐시 정리
npm cache clean --force

# 다시 설치 시도
npm install -g @anthropic-ai/mcp-server-git
```

### **MCP 서버가 연결되지 않는 경우**
```bash
# Claude Code 재시작
# 설정 파일 확인
# 경로 권한 확인
```

## 📞 추가 도움말

- **Claude Code 문서**: https://docs.anthropic.com/claude-code
- **Git MCP 문서**: @anthropic-ai/mcp-server-git
- **Git 공식 문서**: https://git-scm.com/doc