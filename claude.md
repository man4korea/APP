# Claude Code 전역 설정 가이드

## 목차
1. [환경 정보](#환경-정보)
2. [MCP (Model Context Protocol) 설정](#mcp-model-context-protocol-설정)  
3. [개발 환경 설정](#개발-환경-설정)
4. [Shrimp Task Manager 설정](#shrimp-task-manager-설정)
5. [공통 개발 원칙](#공통-개발-원칙)
6. [프로젝트별 구조](#프로젝트별-구조)

---

## 환경 정보

### 현재 환경
- **운영체제**: Windows 11 64비트
- **전역 개발 디렉토리**: `C:\Users\man4k\OneDrive\문서\APP`
- **GitHub 저장소**: https://github.com/man4korea/APP
- **동기화**: OneDrive 자동 동기화 활용

### 전역 디렉토리 구조
```
C:\Users\man4k\OneDrive\문서\APP\
├── CLAUDE.md                    # 🌐 전역 설정 (이 파일)
├── .claude\                     # 전역 Claude 설정
│   └── settings.json
│
├── html\                        # 🏢 CorpEasy ERP 프로젝트
│   ├── CLAUDE.md               # 프로젝트별 지침
│   └── .claude\settings.json
│
├── webapp1\                     # 🚀 새로운 웹앱 1
│   ├── CLAUDE.md               # 프로젝트별 지침  
│   └── .claude\settings.json
│
└── webapp2\                     # 🎯 새로운 웹앱 2
    ├── CLAUDE.md               # 프로젝트별 지침
    └── .claude\settings.json
```

---

## MCP (Model Context Protocol) 설정

### 공통 주의사항

1. **환경 확인**: 현재 사용 환경(OS, 셸 환경) 확인
2. **OS별 대응**: Windows, Linux, macOS 및 환경(WSL, PowerShell, 명령프롬프트) 파악
3. **설치 도구**: mcp-installer 사용하여 user 스코프로 설치
4. **사전 검증**: WebSearch로 공식 사이트 확인 후 설치
5. **Context7 확인**: 공식 사이트 확인 후 Context7 MCP로 재확인
6. **작동 확인**: 설치 후 디버그 모드로 검증 필수
7. **API 키 처리**: 가상 API 키로 설치 후 사용자에게 올바른 키 입력 안내
8. **서버 의존성**: MySQL 등 특정 서버 필요한 경우 재설치보다 조건 안내
9. **선택적 설치**: 요청받은 MCP만 설치, 기존 에러 MCP 무시
10. **터미널 검증**: 터미널에서 작동 성공 시 해당 설정으로 JSON 파일 구성

### Windows 환경 주의사항

- **경로 구분자**: JSON에서 백슬래시 이스케이프 처리 (`\\\\`)
- **Node.js**: PATH 등록 및 v18 이상 버전 확인
- **npx 최적화**: `-y` 옵션으로 버전 호환성 문제 해결

### 전역 MCP 설정 위치

- **User 설정**: `C:\Users\{사용자명}\.claude.json`
- **Project 설정**: `각 프로젝트 루트\.claude\settings.json`

### 환경변수 활용 MCP 설정

**환경변수 파일**: `C:\Users\man4k\OneDrive\문서\APP\.env`

**MCP 서버 설정 예시**:
```json
{
  "mcpServers": {
    "example-mcp": {
      "command": "npx",
      "args": ["-y", "example-mcp-server"],
      "env": {
        "API_KEY": "${API_KEY}"
      }
    }
  }
}
```

**주의사항**:
- `${변수명}` 형식으로 환경변수 참조
- .env 파일에서 실제 API 키 관리
- 보안을 위해 .env 파일은 .gitignore에 포함

---

## 개발 환경 설정

### 다중 프로젝트 환경 관리

#### 프로젝트별 Shrimp Task 관리

각 프로젝트별로 독립적인 Shrimp tasks 관리:

**프로젝트별 SHRIMP 디렉토리**
- `C:\Users\man4k\OneDrive\문서\APP\html\SHRIMP`
- `C:\Users\man4k\OneDrive\문서\APP\calculator\SHRIMP` 
- `C:\Users\man4k\OneDrive\문서\APP\webapp1\SHRIMP`

#### 프로젝트별 데이터베이스 환경 변수 관리

**환경 변수 파일 구조**
```
APP/
├── .env                     # 전역 공통 API 키 (YouTube, OpenAI 등)
├── .env.html               # CorpEasy ERP 프로젝트 DB 설정
├── .env.calculator         # 전자계산기 프로젝트 DB 설정
├── .env.ecommerce         # 이커머스 프로젝트 DB 설정
├── html/
│   └── .mcp.json          # HTML 프로젝트 MCP 설정
└── calculator/
    └── .mcp.json          # Calculator 프로젝트 MCP 설정
```

**프로젝트별 데이터베이스 설정 예시**
```env
# .env.html (CorpEasy ERP 프로젝트)
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=root
MYSQL_PASSWORD=your_html_project_password
MYSQL_DATABASE=corpeasy_erp

# .env.calculator (전자계산기 프로젝트)  
MYSQL_HOST=localhost
MYSQL_PORT=3306
MYSQL_USER=root
MYSQL_PASSWORD=your_calculator_password
MYSQL_DATABASE=calculator_app
```

**사용 방법**
- 각 프로젝트 폴더에서 작업 시 해당 `.mcp.json`이 자동으로 로드됨
- 프로젝트별 환경 변수가 독립적으로 관리됨
- 데이터베이스 설정 충돌 방지

**설정 방법**
```powershell
# 각 프로젝트 작업 시 환경변수 설정
$env:DATA_DIR="C:\Users\man4k\OneDrive\문서\APP\[프로젝트명]\SHRIMP"
```

---

## Shrimp Task Manager 설정

### 13 Core Functions

1. **init_project_rules**: Set coding rules for the project
2. **plan_task**: Convert user requirements into a task plan
3. **analyze_task**: Analyze technical feasibility
4. **process_thought**: Step-by-step problem-solving for complex issues
5. **reflect_task**: Reflect on analysis results
6. **split_tasks**: Divide tasks (clearAllTasks/append/overwrite/selective)
7. **list_tasks**: List all tasks
8. **query_task**: Search for a task
9. **get_task_detail**: View task details
10. **delete_task**: Delete incomplete tasks ⚠️**Requires consent**
11. **execute_task**: Execute the task
12. **verify_task**: Verify task completion
13. **clear_all_tasks**: Delete all tasks ⚠️**Requires consent**

---

## 공통 개발 원칙

### SOLID Principles

- **SRP (Single Responsibility Principle)**: Each component should have only one responsibility
- **OCP (Open/Closed Principle)**: Extend functionalities without modifying existing code
- **LSP (Liskov Substitution Principle)**: Subtypes must be substitutable for their base types
- **ISP (Interface Segregation Principle)**: Do not depend on methods you do not use
- **DIP (Dependency Inversion Principle)**: High-level modules should depend on abstractions

### Code Quality Principles

- **Simplicity First**: Prioritize the simplest solution over complex ones
- **DRY (Don't Repeat Yourself)**: Eliminate code duplication and promote reusable structures
- **Clean Architecture**: Separate the Presentation Layer (UI, etc.) from the Service Layer
- **GUARDRAIL**: Do not use mock data outside of testing environments
- **Naming Convention**: Maintain consistent naming (camelCase / PascalCase, etc.)
- **Dependency Injection**: Ensure a testable and modular structure
- **Exception Handling**: Always handle errors explicitly and predictably; avoid generic catch-alls

### File Operation Guidelines

#### Directory Handling (공통 규칙)
```
// ✅ Correct file creation flow
1. list_directory("parentPath")           // Check if directory exists
2. create_directory("newPath")            // Create if necessary
3. write_file("newPath/file", content)    // Then create file

// ❌ Incorrect usage (causes error)
write_file("nonexistentPath/file", content)
→ Error: Parent directory does not exist
```

#### File Creation Policy (공통 규칙)
- **✅ Prioritize Practicality**: Only create files used in real operations
- **✅ Prefer Improving Existing Files**: Create new ones only if unavoidable
- **❌ Do Not Create Example Files**: `-example.html`, `-demo.js`, `-test.html`, etc.
- **❌ Avoid Duplicate Functionality**: Do not create redundant files

---

## 프로젝트별 구조

### html/ (CorpEasy ERP)
- **설명**: 기업용 ERP 시스템
- **기술스택**: HTML, CSS, JavaScript, Firebase
- **특화 지침**: 상세한 컴포넌트 구조와 모듈화

### webapp1/ (새 프로젝트)
- **설명**: [프로젝트 설명 추가 필요]
- **기술스택**: [기술스택 정의 필요]
- **특화 지침**: [프로젝트별 CLAUDE.md에서 정의]

### webapp2/ (새 프로젝트)
- **설명**: [프로젝트 설명 추가 필요]
- **기술스택**: [기술스택 정의 필요]
- **특화 지침**: [프로젝트별 CLAUDE.md에서 정의]

---

## 중요 지침

### MCP 설치 후 검증 절차
1. `claude mcp list`로 설치 목록 확인
2. `claude --debug`로 디버그 모드 실행 (2분간 관찰)
3. 디버그 메시지에서 에러 내용 확인
4. `/mcp` 명령어로 실제 작동 여부 확인

### 개발 워크플로우
1. 환경 변수 설정 (프로젝트별 DATA_DIR)
2. MCP 서버 상태 확인
3. Task Manager로 작업 계획 수립
4. 개발 진행 및 진척 상황 업데이트
5. 완료 후 상태 업데이트

### 파일 관리 원칙
- 필요한 경우에만 파일 생성
- 기존 파일 편집 우선
- 문서 파일은 명시적 요청 시에만 생성
- 요청 사항 이상 작업 금지

### 프로젝트 전환 시
1. `cd [PROJECT_DIR]`로 프로젝트 디렉토리 이동
2. 해당 프로젝트의 CLAUDE.md 확인
3. 프로젝트별 환경변수 설정 (DATA_DIR 등)
4. 프로젝트별 MCP 설정 적용

---

## 계층적 설정 우선순위

1. **프로젝트별 CLAUDE.md** (최우선)
2. **전역 CLAUDE.md** (공통 지침)
3. **Claude Code 기본 설정** (기본값)

프로젝트 작업 시 프로젝트별 CLAUDE.md가 전역 설정을 오버라이드합니다.