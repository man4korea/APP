작업계획을 ## Gemini CLI를 위한 사용자 정의 도구 및 환경 설정 가이드

### 공통 주의사항
1.  **현재 환경 파악**: OS(윈도우, 리눅스, 맥) 및 셸(WSL, PowerShell, 명령 프롬프트 등)에 맞는 명령어를 사용해야 합니다. 모를 경우 사용자에게 질문하세요.
2.  **외부 도구 연동**: `run_shell_command`를 사용해 필요한 외부 스크립트나 CLI 도구를 실행합니다.
3.  **사전 테스트 필수**: `run_shell_command`로 명령어를 실행하기 전에, 터미널에서 해당 명령어가 독립적으로 정상 작동하는지 반드시 먼저 확인해야 합니다.
4.  **API 키 관리**: API 키와 같은 민감 정보는 셸의 환경 변수(`export API_KEY=...` 또는 `$env:API_KEY=...`)로 설정하는 것을 권장합니다. 또는 `run_shell_command` 내에서 임시로 변수를 설정하고, 실행 후 사용자에게 실제 유효한 키로 교체해야 함을 알려주세요.
5.  **선행 조건 확인**: MySQL 서버 접속과 같이 특정 서비스가 실행 중이어야 하는 도구가 실패할 경우, 재설치나 재시도 대신 사용자에게 필요한 선행 조건을 안내해야 합니다.
6.  **실행 환경 점검**: Node.js, Python 등 스크립트 실행에 필요한 런타임이 `PATH`에 올바르게 등록되어 있는지, 권장 버전(예: Node.js v18+)을 만족하는지 확인하세요.

---

### 사용자 정의 도구 연동 순서

Gemini는 `run_shell_command`를 통해 모든 종류의 CLI 프로그램이나 스크립트를 도구처럼 활용할 수 있습니다.

1.  **도구(스크립트) 준비 또는 확인**
    원하는 기능을 수행하는 셸 스크립트, Python/Node.js 스크립트, 또는 `yt-dlp`, `curl`과 같은 CLI 프로그램을 준비합니다.

2.  **터미널에서 단독 실행 테스트**
    Gemini를 통하지 않고, 터미널에서 직접 명령어를 실행하여 원하는 대로 작동하는지 확인합니다.
    *   `python tools/my_script.py --user "John"`
    *   `node utils/parser.js ./data.json`

3.  **Gemini에서 `run_shell_command`로 실행**
    터미널에서 성공한 명령어를 `run_shell_command`의 `command` 인자로 전달하여 실행합니다.
    ```python
    # 예시: Python 스크립트 실행
    print(default_api.run_shell_command(
        command="python tools/analyzer.py --file report.csv",
        description="report.csv 파일을 분석합니다."
    ))
    ```

4.  **실행 결과 확인**
    `run_shell_command`의 반환 값(`stdout`, `stderr`, `exit_code`)을 보고 성공 여부를 판단하고 다음 작업을 진행합니다. `exit_code`가 0이 아니거나 `stderr`에 내용이 있다면 문제가 발생한 것입니다.

---

### 웹앱 개발 환경 설정

#### 개발 디렉토리 구조
```
C:\Users\man4k\OneDrive\문서\APP\
├── 프로젝트1\
│   ├── package.json
│   └── src/
├── 프로젝트2\
│   ├── pyproject.toml
│   └── main.py
└── gemini.md
```

#### 웹앱 개발에 유용한 내장 도구 및 `run_shell_command` 활용 패턴

1.  **파일 시스템 관리**
    *   **내장 도구 우선 사용**: `read_file`, `write_file`, `list_directory`, `replace` 등 Gemini 내장 도구를 사용하는 것이 가장 안정적이고 효율적입니다.
    *   **복잡한 작업**: 여러 파일을 옮기거나 특정 패턴의 파일만 삭제하는 등 복잡한 작업은 `run_shell_command`와 `cp`, `mv`, `rm`, `find` 같은 셸 명령어를 조합하여 사용하세요.

2.  **텍스트 및 코드 편집**
    *   **부분 수정**: `replace` 도구를 사용하여 파일의 특정 부분을 정확하게 수정합니다.
    *   **전체 수정/생성**: `write_file` 도구를 사용하여 파일 전체 내용을 새로 쓰거나 생성합니다.

3.  **외부 데이터 연동 (예: YouTube)**
    *   `yt-dlp`와 같은 검증된 CLI 도구나, `requests`(Python), `axios`(Node.js)를 사용한 간단한 스크립트를 작성하여 `run_shell_command`로 실행합니다. API 키가 필요하다면 환경 변수를 활용하세요.

4.  **애플리케이션 실행 및 테스트**
    *   `run_shell_command`는 개발 서버를 켜고, 테스트를 실행하며, 빌드하는 모든 과정에 활용할 수 있는 핵심 도구입니다.
    *   `npm run dev`
    *   `pytest`
    *   `go run .`

### 현재 환경 정보
- **운영체제**: Windows 11 (64-bit)
- **개발 디렉토리**: C:\Users\man4k\OneDrive\문서\APP
- **동기화 방식**: Git 및 GitHub 원격 저장소 (`https://github.com/man4korea/APP`)
### Shrimp 작업 관리자 설정
DATA_DIR=C:\Users\man4k\OneDrive\문서\APP\SHRIMP
TEMPLATES_USE=templates_en
ENABLE_GUI=true

---

### MCP (Meta-Tool Controller Protocol) 사용법

`.gemini/settings.json`에 등록된 MCP(Meta-Tool Controller Protocol) 서버는 `runner.js` 스크립트를 통해 사용할 수 있습니다. 이 스크립트는 각 MCP에 필요한 인증 헤더(API 키 등)를 자동으로 포함하여 요청을 보냅니다.

#### 기본 사용법
```bash
node runner.js <mcp-alias> <command> [options]
```
- `<mcp-alias>`: `settings.json`에 정의된 MCP의 별칭 (예: `github`, `shrimp-task-manager`)
- `<command>`: 해당 MCP가 지원하는 명령어
- `[options]`: 명령어에 필요한 추가 인자

#### 등록된 MCP 목록 및 예시

1.  **`github`**: GitHub 관련 작업을 수행합니다. (예: 이슈 조회, 레포지토리 정보 확인)
    ```bash
    # 예시: man4korea/APP 레포지터리의 1번 이슈 내용 조회
    node runner.js github get-issue --owner man4korea --repo APP --issue-number 1
    ```

2.  **`shrimp-task-manager`**: Shrimp Task Manager와 상호작용하여 작업을 관리합니다.

    **지원 명령어:**
    - `plan_task`: 복잡한 기능이나 작업에 대한 계획 수립이 필요할 때 사용합니다. 단계별 지침을 엄격히 따라야 합니다.
    - `analyze_task`: 작업 요구사항을 분석하고 코드베이스를 검토하여 기술적 타당성과 잠재적 위험을 평가합니다.
    - `reflect_task`: 분석 결과를 비판적으로 검토하고 솔루션의 완성도를 평가하며 최적화 기회를 식별합니다.
    - `split_tasks`: 복잡한 작업을 독립적인 하위 작업으로 분해하고 종속성과 우선순위를 설정합니다.
    - `list_tasks`: 완료 상태, 우선순위, 종속성을 포함한 구조화된 작업 목록을 생성합니다.

    **사용 예시:**
    ```bash
    # 예시: 모든 작업 목록 조회
    node runner.js shrimp-task-manager list_tasks

    # 예시: 새로운 작업 계획 수립
    node runner.js shrimp-task-manager plan_task --goal "사용자 인증 시스템 구현"
    ```

3.  **`upstash-context`**: Upstash 데이터베이스를 사용하여 대화의 컨텍스트를 관리합니다.
    ```bash
    # 예시: 'main-context' 키로 저장된 컨텍스트 조회
    node runner.js upstash-context get-context --key "main-context"
    ```

4.  **`mem0-memory`**: Mem0 AI를 사용하여 장기 기억을 관리합니다.
    ```bash
    # 예시: 특정 메모리 조회
    node runner.js mem0-memory get-memory --key "user-preferences"
    ```