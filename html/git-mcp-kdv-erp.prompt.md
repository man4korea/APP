
## GIT MCP 사용법 - 마스터용

- 프로젝트 경로: `C:/app/html`
- 대상 GitHub 저장소: `man4korea/kdv-erp`

### 1. 초기화 및 첫 커밋 (.gitignore 포함)
```json
{
  "tool": "git",
  "parameters": {
    "subtool": "RunCommand",
    "path": "C:/app/html",
    "command": "cmd",
    "args": [
      "/c",
      "git init && " +
      "echo node_modules/ > .gitignore && " +
      "echo *.log >> .gitignore && " +
      "echo .DS_Store >> .gitignore && " +
      "git remote add origin https://github.com/man4korea/kdv-erp.git && " +
      "git add . && " +
      "git commit -m \"chore: initial commit from MCP\""
    ]
  }
}
```

### 2. 특정 파일 커밋 예시
```json
{
  "tool": "git",
  "parameters": {
    "subtool": "RunCommand",
    "path": "C:/app/html",
    "command": "cmd",
    "args": [
      "/c",
      "git add index.html && " +
      "git commit -m \"feat: update landing page\""
    ]
  }
}
```

### 3. 전체 코드 push (초기 원격 저장소 연결 후 1회)
```json
{
  "tool": "git",
  "parameters": {
    "subtool": "RunCommand",
    "path": "C:/app/html",
    "command": "cmd",
    "args": [
      "/c",
      "git push -u origin main"
    ]
  }
}
```

> ❗️주의: `main` 브랜치가 없을 경우, 먼저 `git branch -M main` 명령어를 추가해야 함.

### 4. 테스트 실행 후 자동 커밋 (예: npm 프로젝트)
```json
{
  "tool": "git",
  "parameters": {
    "subtool": "RunCommand",
    "path": "C:/app/html",
    "command": "cmd",
    "args": [
      "/c",
      "npm test && " +
      "git add . && " +
      "git commit -m \"test: commit after successful test\""
    ]
  }
}
```

### 5. 파일 생성 및 커밋 예시 (.env 등)
```json
{
  "tool": "git",
  "parameters": {
    "subtool": "RunCommand",
    "path": "C:/app/html",
    "command": "cmd",
    "args": [
      "/c",
      "echo API_KEY=xxx > .env.example && " +
      "git add .env.example && " +
      "git commit -m \"chore: add env example file\""
    ]
  }
}
```

### 6. 파일 삭제 및 커밋 예시
```json
{
  "tool": "git",
  "parameters": {
    "subtool": "RunCommand",
    "path": "C:/app/html",
    "command": "cmd",
    "args": [
      "/c",
      "git rm debug.log && " +
      "git commit -m \"build: remove debug log file\""
    ]
  }
}
```

### 7. Git 내 특정 파일 내용 읽기
```json
{
  "tool": "git",
  "parameters": {
    "subtool": "RunCommand",
    "path": "C:/app/html",
    "command": "cmd",
    "args": [
      "/c",
      "git show HEAD:index.html"
    ]
  }
}
```
