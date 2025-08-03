@echo off
REM 📁 C:\xampp\htdocs\BPM\CLAUDE_CODE_SETUP_PACKAGE\01_auto_install_mcp.bat
REM Create at 2508031202 Ver1.00

echo ========================================
echo  Claude Code MCP 서버 일괄 설치 스크립트
echo ========================================
echo.

REM 관리자 권한 확인
net session >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ 관리자 권한으로 실행 중
) else (
    echo ❌ 관리자 권한이 필요합니다.
    echo PowerShell을 관리자 권한으로 실행 후 다시 시도하세요.
    pause
    exit /b 1
)

echo.
echo 🔍 시스템 환경 확인 중...

REM Node.js 버전 확인
node --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Node.js 설치됨: 
    node --version
) else (
    echo ❌ Node.js가 설치되지 않았습니다.
    echo https://nodejs.org에서 Node.js 18+ 설치 후 다시 시도하세요.
    pause
    exit /b 1
)

REM npm 버전 확인
npm --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ npm 설치됨:
    npm --version
) else (
    echo ❌ npm이 설치되지 않았습니다.
    pause
    exit /b 1
)

echo.
echo 🚀 MCP 서버 설치 시작...
echo.

REM 설치 로그 파일 초기화
set LOGFILE=%~dp0\mcp_install.log
echo MCP 설치 로그 - %date% %time% > "%LOGFILE%"

REM MCP CLI 도구 먼저 설치
echo 📦 MCP CLI 도구 설치 중...
call npm install -g @modelcontextprotocol/cli
if %errorLevel% neq 0 (
    echo ❌ MCP CLI 설치 실패
    echo 상세 오류는 %LOGFILE% 파일을 확인하세요.
    pause
    exit /b 1
)
echo ✅ MCP CLI 설치 완료

echo.
echo 📡 MCP 서버들 설치 중...

REM 1. SHRIMP Task Manager (가장 중요)
echo [1/10] SHRIMP Task Manager 설치 중...
call npx @modelcontextprotocol/cli install @shrimpai/shrimp-task-manager >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ SHRIMP Task Manager 설치 완료
) else (
    echo ⚠️ SHRIMP Task Manager 설치 실패 (계속 진행)
)

REM 2. Filesystem
echo [2/10] Filesystem 서버 설치 중...
call npx @modelcontextprotocol/cli install @modelcontextprotocol/server-filesystem >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ Filesystem 서버 설치 완료
) else (
    echo ⚠️ Filesystem 서버 설치 실패 (계속 진행)
)

REM 3. Text Editor
echo [3/10] Text Editor 서버 설치 중...
call npx @modelcontextprotocol/cli install @modelcontextprotocol/server-text-editor >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ Text Editor 서버 설치 완료
) else (
    echo ⚠️ Text Editor 서버 설치 실패 (계속 진행)
)

REM 4. Memory
echo [4/10] Memory 서버 설치 중...
call npx @modelcontextprotocol/cli install @modelcontextprotocol/server-memory >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ Memory 서버 설치 완료
) else (
    echo ⚠️ Memory 서버 설치 실패 (계속 진행)
)

REM 5. GitHub
echo [5/10] GitHub 서버 설치 중...
call npx @modelcontextprotocol/cli install @modelcontextprotocol/server-github >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ GitHub 서버 설치 완료
) else (
    echo ⚠️ GitHub 서버 설치 실패 (계속 진행)
)

REM 6. Sequential Thinking
echo [6/10] Sequential Thinking 서버 설치 중...
call npx @modelcontextprotocol/cli install @modelcontextprotocol/server-sequential-thinking >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ Sequential Thinking 서버 설치 완료
) else (
    echo ⚠️ Sequential Thinking 서버 설치 실패 (계속 진행)
)

REM 7. Terminal (WeidWonder)
echo [7/10] Terminal 서버 설치 중...
call npx @modelcontextprotocol/cli install @weidwonder/terminal-mcp-server >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ Terminal 서버 설치 완료
) else (
    echo ⚠️ Terminal 서버 설치 실패 (계속 진행)
)

REM 8. Playwright
echo [8/10] Playwright 서버 설치 중...
call npx @modelcontextprotocol/cli install @agentic/mcp-playwright >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ Playwright 서버 설치 완료
) else (
    echo ⚠️ Playwright 서버 설치 실패 (계속 진행)
)

REM 9. Puppeteer
echo [9/10] Puppeteer 서버 설치 중...
call npx @modelcontextprotocol/cli install @modelcontextprotocol/server-puppeteer >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ Puppeteer 서버 설치 완료
) else (
    echo ⚠️ Puppeteer 서버 설치 실패 (계속 진행)
)

REM 10. IDE
echo [10/10] IDE 서버 설치 중...
call npx @modelcontextprotocol/cli install @modelcontextprotocol/server-ide >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ IDE 서버 설치 완료
) else (
    echo ⚠️ IDE 서버 설치 실패 (계속 진행)
)

echo.
echo 🔧 Claude Desktop 설정 업데이트 중...

REM Claude Desktop 설정 파일 위치 확인 및 백업
set CLAUDE_CONFIG="%APPDATA%\Claude\settings.json"
set CLAUDE_CONFIG_ALT="%APPDATA%\Claude\claude_desktop_config.json"
set CLAUDE_MCP="%APPDATA%\Claude\.mcp.json"

REM 설정 백업
if exist %CLAUDE_CONFIG% (
    copy %CLAUDE_CONFIG% "%CLAUDE_CONFIG%.backup.%date:~0,4%%date:~5,2%%date:~8,2%"
    echo ✅ Claude Desktop 설정 백업 완료
)

if exist %CLAUDE_MCP% (
    copy %CLAUDE_MCP% "%CLAUDE_MCP%.backup.%date:~0,4%%date:~5,2%%date:~8,2%"
    echo ✅ MCP 설정 백업 완료
)

REM 기본 MCP 설정 파일 생성 (존재하지 않는 경우)
if not exist %CLAUDE_MCP% (
    echo 📝 기본 MCP 설정 파일 생성 중...
    (
        echo {
        echo   "mcpServers": {}
        echo }
    ) > %CLAUDE_MCP%
    echo ✅ 기본 MCP 설정 파일 생성 완료
)

echo.
echo 🧪 설치 검증 중...

REM 설치된 MCP 서버 확인
echo 📊 설치된 MCP 서버 목록:
call npx @modelcontextprotocol/cli list 2>nul
if %errorLevel% neq 0 (
    echo ⚠️ MCP 서버 목록 조회 실패
    echo 수동으로 Claude Desktop에서 확인하세요.
)

echo.
echo ========================================
echo  MCP 서버 설치 완료!
echo ========================================
echo.
echo 📋 다음 단계:
echo 1. Claude Desktop 완전 재시작
echo 2. 02_install_superclaude.bat 실행
echo 3. 03_setup_agents.bat 실행
echo.
echo 📄 설치 로그: %LOGFILE%
echo.
echo ⚠️ 주의사항:
echo - Claude Desktop을 완전히 종료 후 다시 시작하세요
echo - 일부 서버가 설치되지 않았을 수 있습니다 (정상)
echo - Claude Code에서 MCP 도구들을 테스트해보세요
echo.

pause
echo 🔄 Claude Desktop 자동 재시작 시도 중...

REM Claude Desktop 프로세스 종료
taskkill /f /im "Claude.exe" >nul 2>&1
taskkill /f /im "Claude Desktop.exe" >nul 2>&1

REM 잠시 대기
timeout /t 3 /nobreak >nul

REM Claude Desktop 시작 시도 (설치된 경로에서)
start "" "%LOCALAPPDATA%\Programs\Claude\Claude.exe" >nul 2>&1
if %errorLevel% neq 0 (
    start "" "%PROGRAMFILES%\Claude\Claude.exe" >nul 2>&1
    if %errorLevel% neq 0 (
        echo ⚠️ Claude Desktop 자동 시작 실패
        echo 수동으로 Claude Desktop을 시작하세요.
    ) else (
        echo ✅ Claude Desktop 재시작 완료
    )
) else (
    echo ✅ Claude Desktop 재시작 완료
)

echo.
echo 🎉 MCP 서버 설치가 완료되었습니다!
echo 이제 02_install_superclaude.bat를 실행하세요.
echo.
pause