# 🔧 Agent 문제 해결 가이드

## 📋 목차
1. [일반적인 문제](#일반적인-문제)
2. [연결 문제](#연결-문제)
3. [성능 문제](#성능-문제)
4. [설정 문제](#설정-문제)
5. [Agent별 특수 문제](#agent별-특수-문제)
6. [고급 문제 해결](#고급-문제-해결)

---

## 일반적인 문제

### ❓ Agent가 응답하지 않음

**증상**: Agent 호출 시 무응답 또는 오류 메시지

**원인 및 해결방법**:

1. **MCP 연결 문제**
   ```bash
   # 연결 상태 확인
   node test-agent-connections.js
   
   # 결과 예시:
   # ✅ 정상: Code Reviewer Agent
   # ❌ 실패: Security Reviewer Agent
   ```

2. **Claude Code 재시작**
   - Claude Code 완전 종료 후 재실행
   - 설정 파일 다시 로드 확인

3. **Agent 프로세스 확인**
   ```bash
   # Windows
   tasklist | findstr node
   
   # Linux/Mac
   ps aux | grep node
   ```

### ❓ 분석 결과가 이상함

**증상**: 예상과 다른 분석 결과 또는 불완전한 응답

**해결방법**:

1. **입력 데이터 검증**
   - 코드/데이터의 완전성 확인
   - 특수 문자나 인코딩 문제 확인
   - 필수 매개변수 누락 여부 확인

2. **컨텍스트 정보 추가 제공**
   ```javascript
   // 좋은 예
   {
     code: "complete code here",
     framework: "react",
     version: "18.2.0",
     issues: "specific problems you're facing"
   }
   
   // 나쁜 예
   {
     code: "function test() { ... }",
     framework: ""
   }
   ```

---

## 연결 문제

### 🔌 MCP 서버 연결 실패

**증상**: "Agent not found" 또는 "Connection failed" 오류

**단계별 해결**:

1. **설정 파일 확인**
   ```json
   // settings.local.json 검증
   {
     "mcpServers": {
       "code-reviewer-agent": {
         "command": "node",
         "args": ["C:\\xampp\\htdocs\\AppMart\\code-reviewer-agent.js"],
         "cwd": "C:\\xampp\\htdocs\\AppMart"
       }
     }
   }
   ```

2. **파일 경로 확인**
   ```bash
   # 파일 존재 여부 확인
   ls -la *agent*.js
   
   # 실행 권한 확인 (Linux/Mac)
   chmod +x *agent*.js
   ```

3. **Node.js 및 의존성 확인**
   ```bash
   # Node.js 버전 확인 (14+ 필요)
   node -v
   
   # MCP SDK 설치 확인
   npm list @modelcontextprotocol/sdk
   
   # 필요 시 재설치
   npm install @modelcontextprotocol/sdk
   ```

### 🚫 포트 충돌 문제

**증상**: 여러 Agent가 동시에 실행되지 않음

**해결방법**:
1. Agent들은 독립적인 프로세스로 실행되므로 포트 충돌 없음
2. 메모리 부족 시 시스템 리소스 확인
3. 동시 실행 개수 제한 확인

---

## 성능 문제

### 🐌 Agent 응답 속도 느림

**원인별 해결방법**:

1. **대용량 코드 분석**
   ```javascript
   // 코드를 작은 단위로 분할
   const chunks = splitCodeIntoChunks(largeCode, 1000);
   for (const chunk of chunks) {
     await analyzeChunk(chunk);
   }
   ```

2. **메모리 사용량 확인**
   ```bash
   # 메모리 사용량 모니터링
   # Windows
   tasklist /fi "imagename eq node.exe"
   
   # Linux/Mac
   top -p $(pgrep node)
   ```

3. **동시 요청 제한**
   - 한 번에 하나의 Agent만 사용
   - 복잡한 분석은 단계적으로 진행

### 💾 메모리 부족 오류

**해결방법**:
```bash
# Node.js 메모리 한도 증가
node --max-old-space-size=4096 agent-file.js

# 시스템 메모리 확인
# Windows: Ctrl+Shift+Esc → 성능 탭
# Linux/Mac: free -h
```

---

## 설정 문제

### ⚙️ 설정 파일 오류

**일반적인 JSON 오류**:

1. **문법 오류**
   ```json
   // ❌ 잘못된 예
   {
     "mcpServers": {
       "code-reviewer-agent": {
         "command": "node",
         "args": ["path"],  // 마지막 쉼표 제거 필요
       }
     }
   }
   
   // ✅ 올바른 예
   {
     "mcpServers": {
       "code-reviewer-agent": {
         "command": "node",
         "args": ["path"]
       }
     }
   }
   ```

2. **경로 문제**
   ```json
   // Windows 경로 이스케이프
   "args": ["C:\\\\xampp\\\\htdocs\\\\AppMart\\\\agent.js"]
   
   // 또는 슬래시 사용
   "args": ["C:/xampp/htdocs/AppMart/agent.js"]
   ```

### 🔍 설정 파일 검증 도구

```bash
# JSON 문법 검사
node -e "console.log(JSON.parse(require('fs').readFileSync('settings.local.json')))"

# 또는 온라인 JSON 검증기 사용
# https://jsonlint.com/
```

---

## Agent별 특수 문제

### 🔍 Code Reviewer 문제

**문제**: 코드 분석이 부정확함
```javascript
// 해결방법: 충분한 컨텍스트 제공
{
  code: "complete function code",
  language: "javascript",
  framework: "react",
  focus: "performance" // 또는 "quality", "security"
}
```

### 🔐 Security Reviewer 문제  

**문제**: 취약점 탐지 누락
```javascript
// 해결방법: 코드 전체 제공
{
  code: "include imports and dependencies",
  type: "php", // 정확한 언어 지정
  focus: "authentication" // 구체적인 영역 지정
}
```

### 🏗️ Tech Lead 문제

**문제**: 부적절한 아키텍처 추천
```javascript
// 해결방법: 상세한 요구사항 제공
{
  project_type: "e_commerce",
  expected_users: "100K",
  team_size: "medium",
  timeline: "6개월",
  budget: "medium",
  constraints: "existing PHP codebase" // 제약사항 명시
}
```

### 🎨 UX Reviewer 문제

**문제**: UX 분석이 표면적임
```javascript
// 해결방법: 구체적인 사용자 정보 제공
{
  website_url: "actual URL",
  page_type: "product",
  target_users: "senior", // 구체적인 사용자 그룹
  device_type: "mobile", // 특정 디바이스
  current_issues: "specific problems users face"
}
```

### ⚡ Performance Optimizer 문제

**문제**: 성능 분석이 일반적임
```javascript
// 해결방법: 현재 성능 지표 제공
{
  website_url: "actual URL",
  performance_type: "frontend",
  current_metrics: {
    LCP: "4.2s",
    FID: "180ms",
    CLS: "0.25"
  },
  bottlenecks: "specific performance issues"
}
```

---

## 고급 문제 해결

### 🔧 디버깅 모드 활성화

Agent 파일에 디버깅 로그 추가:
```javascript
// agent 파일 상단에 추가
const DEBUG = process.env.DEBUG === 'true';

function debugLog(message) {
  if (DEBUG) {
    console.error(`[DEBUG] ${new Date().toISOString()}: ${message}`);
  }
}

// 사용법
debugLog('Agent started');
debugLog(`Received request: ${JSON.stringify(request)}`);
```

실행 시:
```bash
DEBUG=true node code-reviewer-agent.js
```

### 📊 로그 분석

1. **Agent 로그 확인**
   ```bash
   # Agent 실행 로그 확인
   node agent-file.js 2>&1 | tee agent.log
   
   # 로그 파일 분석
   grep -i error agent.log
   grep -i warning agent.log
   ```

2. **Claude Code 로그 확인**
   - Claude Code 설정에서 로그 레벨 조정
   - MCP 통신 로그 활성화

### 🚨 응급 복구 절차

**모든 Agent가 작동하지 않을 때**:

1. **백업 설정 복원**
   ```bash
   # 설정 파일 백업 복원
   cp settings.local.json.backup settings.local.json
   ```

2. **Agent 재설치**
   ```bash
   # 의존성 재설치
   rm -rf node_modules package-lock.json
   npm install
   
   # Agent 파일 권한 복원
   chmod +x *agent*.js
   ```

3. **단계적 복구**
   - 하나씩 Agent 활성화
   - 각 단계에서 연결 테스트
   - 문제가 있는 Agent 격리

---

## 📞 추가 지원

### 🆘 지원 요청 시 포함할 정보

1. **시스템 정보**
   ```bash
   # 시스템 정보 수집
   echo "OS: $(uname -a)"
   echo "Node.js: $(node -v)"
   echo "NPM: $(npm -v)"
   echo "Claude Code Version: [버전 정보]"
   ```

2. **오류 로그**
   - 전체 오류 메시지
   - Agent 실행 로그
   - Claude Code 오류 로그

3. **재현 단계**
   - 정확한 실행 순서
   - 사용한 입력 데이터
   - 예상 결과 vs 실제 결과

### 📝 버그 리포트 템플릿

```markdown
## 문제 설명
[간단한 문제 설명]

## 재현 단계
1. [단계 1]
2. [단계 2]
3. [단계 3]

## 예상 결과
[예상했던 결과]

## 실제 결과  
[실제 발생한 결과]

## 환경 정보
- OS: [운영체제]
- Node.js: [버전]
- Agent: [문제가 있는 Agent]

## 추가 정보
[오류 로그, 스크린샷 등]
```

---

*🔧 문제가 해결되지 않으면 팀 Slack 채널로 문의해주세요.*