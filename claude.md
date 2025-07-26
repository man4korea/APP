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

### HTML 렌더링 문제 해결 가이드 🔧

#### 문제 유형: HTML 태그가 텍스트로 표시되는 현상
HTML 태그(`<span>`, `<br>` 등)가 브라우저에서 파싱되지 않고 그대로 텍스트로 표시되는 문제

#### 주요 원인 3가지
1. **템플릿 엔진 HTML 이스케이프**: `<` → `&lt;` 변환으로 태그가 무력화
2. **JavaScript innerHTML vs innerText**: `innerText` 사용 시 HTML 태그가 문자열로 처리
3. **서버사이드 HTML 인코딩**: 서버에서 HTML 태그를 자동 이스케이프 처리

#### 필수 해결 방법 ✅

**1. JavaScript innerHTML 사용 강제**
```javascript
// ❌ 잘못된 방법 (태그가 텍스트로 표시)
element.innerText = '<span class="highlight">텍스트</span>';

// ✅ 올바른 방법 (HTML 태그 파싱됨)
element.innerHTML = '<span class="highlight">텍스트</span>';
```

**2. DOM 완전 로딩 후 실행**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const element = document.getElementById('target');
    if (element) {
        element.innerHTML = 'HTML 태그 포함 텍스트';
    }
});
```

**3. HTML 디코딩 함수 적용**
```javascript
function decodeHtmlEntities(str) {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = str;
    return textarea.value;
}

// 서버사이드 인코딩 대응
const decodedText = decodeHtmlEntities(htmlString);
element.innerHTML = decodedText;
```

**4. CSS 선택자 다중 적용**
```css
/* 강력한 명시성으로 CSS 적용 보장 */
.hero-title .highlight,
h1 .highlight,
span.highlight,
.highlight {
    color: #2563eb !important;
    font-weight: 900 !important;
    /* 다중 fallback 제공 */
}
```

**5. 스타일 강제 적용**
```javascript
// JavaScript로 스타일 강제 적용
highlight.style.setProperty('color', '#2563eb', 'important');
highlight.style.setProperty('font-weight', '900', 'important');
```

#### 검증 방법 🔍
```javascript
// 디버그 및 검증 코드
setTimeout(() => {
    const element = document.querySelector('.highlight');
    if (element) {
        console.log('✅ 요소 발견:', element.textContent);
        console.log('✅ 적용된 스타일:', window.getComputedStyle(element).color);
    } else {
        console.log('❌ 요소를 찾을 수 없음');
    }
}, 100);
```

#### 핵심 체크리스트 📋
- [ ] `innerHTML` 사용 (innerText 금지)
- [ ] DOM 로딩 완료 후 실행
- [ ] HTML 디코딩 함수 적용
- [ ] CSS 선택자 다중화
- [ ] JavaScript 스타일 강제 적용
- [ ] 브라우저 콘솔 검증

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

## 클로드 코드에서의 mcp-installer를 사용한 MCP (Model Context Protocol) 설치 및 설정 가이드 
공통 주의사항
1. 현재 사용 환경을 확인할 것. 모르면 사용자에게 물어볼 것. 
2. OS(윈도우,리눅스,맥) 및 환경들(WSL,파워셀,명령프롬프트등)을 파악해서 그에 맞게 세팅할 것. 모르면 사용자에게 물어볼 것.
3. mcp-installer을 이용해 필요한 MCP들을 설치할 것
   (user 스코프로 설치 및 적용할것)
4. 특정 MCP 설치시, 바로 설치하지 말고, WebSearch 도구로 해당 MCP의 공식 사이트 확인하고 현재 OS 및 환경 매치하여, 공식 설치법부터 확인할 것
5. 공식 사이트 확인 후에는 context7 MCP 존재하는 경우, context7으로 다시 한번 확인할 것
6. MCP 설치 후, task를 통해 디버그 모드로 서브 에이전트 구동한 후, /mcp 를 통해 실제 작동여부를 반드시 확인할 것 
7. 설정 시, API KEY 환경 변수 설정이 필요한 경우, 가상의 API 키로 디폴트로 설치 및 설정 후, 올바른 API 키 정보를 입력해야 함을 사용자에게 알릴 것
8. Mysql MCP와 같이 특정 서버가 구동중 상태여만 정상 작동한 것은 에러가 나도 재설치하지 말고, 정상 구동을 위한 조건을 사용자에게 알릴 것
9. 현재 클로드 코드가 실행되는 환경이야.
10. 설치 요청 받은 MCP만 설치하면 돼. 혹시 이미 설치된 다른 MCP 에러 있어도, 그냥 둘 것
11. 일단, 터미널에서 설치하려는 MCP 작동 성공한 경우, 성공 시의 인자 및 환경 변수 이름을 활용해, 올바른 위치의 json 파일에 MCP 설정을 직접할 것
12. WSL sudo 패스워드: qsc1445! (WSL 환경인 경우에만 해당)

*윈도우에서의 주의사항*
1. 설정 파일 직접 세팅시, Windows 경로 구분자는 백슬래시(\)이며, JSON 내에서는 반드시 이스케이프 처리(\\\\)해야 해.
** OS 공통 주의사항**
1. Node.js가 %PATH%에 등록되어 있는지, 버전이 최소 v18 이상인지 확인할 것
2. npx -y 옵션을 추가하면 버전 호환성 문제를 줄일 수 있음

### MCP 서버 설치 순서

1. 기본 설치
	mcp-installer를 사용해 설치할 것

2. 설치 후 정상 설치 여부 확인하기	
	claude mcp list 으로 설치 목록에 포함되는지 내용 확인한 후,
	task를 통해 디버그 모드로 서브 에이전트 구동한 후 (claude --debug), 최대 2분 동안 관찰한 후, 그 동안의 디버그 메시지(에러 시 관련 내용이 출력됨)를 확인하고 /mcp 를 통해(Bash(echo "/mcp" | claude --debug)) 실제 작동여부를 반드시 확인할 것

3. 문제 있을때 다음을 통해 직접 설치할 것

	*User 스코프로 claude mcp add 명령어를 통한 설정 파일 세팅 예시*
	예시1:
	claude mcp add --scope user youtube-mcp \
	  -e YOUTUBE_API_KEY=$YOUR_YT_API_KEY \

	  -e YOUTUBE_TRANSCRIPT_LANG=ko \
	  -- npx -y youtube-data-mcp-server


4. 정상 설치 여부 확인 하기
	claude mcp list 으로 설치 목록에 포함되는지 내용 확인한 후,
	task를 통해 디버그 모드로 서브 에이전트 구동한 후 (claude --debug), 최대 2분 동안 관찰한 후, 그 동안의 디버그 메시지(에러 시 관련 내용이 출력됨)를 확인하고, /mcp 를 통해(Bash(echo "/mcp" | claude --debug)) 실제 작동여부를 반드시 확인할 것


5. 문제 있을때 공식 사이트 다시 확인후 권장되는 방법으로 설치 및 설정할 것
	(npm/npx 패키지를 찾을 수 없는 경우) pm 전역 설치 경로 확인 : npm config get prefix
	권장되는 방법을 확인한 후, npm, pip, uvx, pip 등으로 직접 설치할 것

	#### uvx 명령어를 찾을 수 없는 경우
	# uv 설치 (Python 패키지 관리자)
	curl -LsSf https://astral.sh/uv/install.sh | sh

	#### npm/npx 패키지를 찾을 수 없는 경우
	# npm 전역 설치 경로 확인
	npm config get prefix


	#### uvx 명령어를 찾을 수 없는 경우
	# uv 설치 (Python 패키지 관리자)
	curl -LsSf https://astral.sh/uv/install.sh | sh


	## 설치 후 터미널 상에서 작동 여부 점검할 것 ##
	
	## 위 방법으로, 터미널에서 작동 성공한 경우, 성공 시의 인자 및 환경 변수 이름을 활용해서, 클로드 코드의 올바른 위치의 json 설정 파일에 MCP를 직접 설정할 것 ##


	설정 예시
		(설정 파일 위치)
		**리눅스, macOS 또는 윈도우 WSL 기반의 클로드 코드인 경우**
		- **User 설정**: `~/.claude/` 디렉토리
		- **Project 설정**: 프로젝트 루트/.claude

		**윈도우 네이티브 클로드 코드인 경우**
		- **User 설정**: `C:\Users\{사용자명}\.claude` 디렉토리
		- *User 설정파일*  C:\Users\{사용자명}\.claude.json
		- **Project 설정**: 프로젝트 루트\.claude

		1. npx 사용

		{
		  "youtube-mcp": {
		    "type": "stdio",
		    "command": "npx",
		    "args": ["-y", "youtube-data-mcp-server"],
		    "env": {
		      "YOUTUBE_API_KEY": "YOUR_API_KEY_HERE",
		      "YOUTUBE_TRANSCRIPT_LANG": "ko"
		    }
		  }
		}


		2. cmd.exe 래퍼 + 자동 동의)
		{
		  "mcpServers": {
		    "mcp-installer": {
		      "command": "cmd.exe",
		      "args": ["/c", "npx", "-y", "@anaisbetts/mcp-installer"],
		      "type": "stdio"
		    }
		  }
		}

		3. 파워셀예시
		{
		  "command": "powershell.exe",
		  "args": [
		    "-NoLogo", "-NoProfile",
		    "-Command", "npx -y @anaisbetts/mcp-installer"
		  ]
		}

		4. npx 대신 node 지정
		{
		  "command": "node",
		  "args": [
		    "%APPDATA%\\npm\\node_modules\\@anaisbetts\\mcp-installer\\dist\\index.js"
		  ]
		}

		5. args 배열 설계 시 체크리스트
		토큰 단위 분리: "args": ["/c","npx","-y","pkg"] 와
			"args": ["/c","npx -y pkg"] 는 동일해보여도 cmd.exe 내부에서 따옴표 처리 방식이 달라질 수 있음. 분리가 안전.
		경로 포함 시: JSON에서는 \\ 두 번. 예) "C:\\tools\\mcp\\server.js".
		환경변수 전달:
			"env": { "UV_DEPS_CACHE": "%TEMP%\\uvcache" }
		타임아웃 조정: 느린 PC라면 MCP_TIMEOUT 환경변수로 부팅 최대 시간을 늘릴 수 있음 (예: 10000 = 10 초) 

**중요사항**
	윈도우 네이티브 환경이고 MCP 설정에 어려움이 있는데 npx 환경이라면, cmd나 node 등으로 다음과 같이 대체해 볼것:
	{
	"mcpServers": {
	      "context7": {
		 "command": "cmd",
		 "args": ["/c", "npx", "-y", "@upstash/context7-mcp@latest"]
	      }
	   }
	}

	claude mcp add-json context7 -s user '{"type":"stdio","command":"cmd","args": ["/c", "npx", "-y", "@upstash/context7-mcp@latest"]}'

(설치 및 설정한 후는 항상 아래 내용으로 검증할 것)
	claude mcp list 으로 설치 목록에 포함되는지 내용 확인한 후,
	task를 통해 디버그 모드로 서브 에이전트 구동한 후 (claude --debug), 최대 2분 동안 관찰한 후, 그 동안의 디버그 메시지(에러 시 관련 내용이 출력됨)를 확인하고 /mcp 를 통해 실제 작동여부를 반드시 확인할 것

		
** MCP 서버 제거가 필요할 때 예시: **
claude mcp remove youtube-mcp


## 윈도우 네이티브 클로드 코드에서 클로드 데스크탑의 MCP 가져오는 방법 ###

"C:\Users\<사용자이름>\AppData\Roaming\Claude\claude_desktop_config.json" 이 파일이 존재한다면 클로드 데스크탑이 설치된 상태야.
이 파일의 mcpServers 내용을 클로드 코드 설정 파일(C:\Users\{사용자명}\.claude.json)의 user 스코프 위치(projects 항목에 속하지 않은 mcpServers가 user 스코프에 해당)로 그대로 가지고 오면 돼.
가지고 온 후, task를 통해 디버그 모드로 서브 에이전트 구동하여 (claude --debug) 클로드 코드에 문제가 없는지 확인할 것

또 C:\Users\<사용자이름>\AppData\Roaming\Claude\Claude Extensions\ -> 이 경로를 조회하면 mcp들이 있을 수 있어. 해당 mcp로 들어가면 manifest.json 파일이 존재해. 이걸 기반으로 네이티브 클로드로 가지고 올 수 있어.
(이렇게 가지고 오는 경우, 툴즈 정보를 보고, 해당 MCP 사용법을 CLAUDE.md 끝에 추가해줘)
