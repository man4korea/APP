@echo off
REM 📁 C:\xampp\htdocs\BPM\CLAUDE_CODE_SETUP_PACKAGE\05_rollback_installation.bat
REM Create at 2508031214 Ver1.00

echo ========================================
echo  Claude Code 환경 롤백 스크립트
echo ========================================
echo.

echo ⚠️ 이 스크립트는 설치된 MCP 서버, SuperClaude, 에이전트를 제거합니다.
echo.

set /p CONFIRM="정말로 설치를 롤백하시겠습니까? (y/N): "
if /i not "%CONFIRM%"=="y" (
    echo 롤백이 취소되었습니다.
    pause
    exit /b 0
)

echo.
echo 🗑️ 롤백 시작...

REM 롤백 로그 파일
set LOGFILE=%~dp0\rollback.log
echo 롤백 시작 - %date% %time% > "%LOGFILE%"

REM 1. SuperClaude 제거
echo [1/5] SuperClaude 제거 중...
call npm uninstall -g superclaude >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ SuperClaude 제거 완료
) else (
    echo ⚠️ SuperClaude 제거 실패 (이미 제거되었을 수 있음)
)

REM 2. MCP CLI 제거  
echo [2/5] MCP CLI 제거 중...
call npm uninstall -g @modelcontextprotocol/cli >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ MCP CLI 제거 완료
) else (
    echo ⚠️ MCP CLI 제거 실패 (이미 제거되었을 수 있음)
)

REM 3. 에이전트 파일 제거
echo [3/5] 에이전트 파일 제거 중...
set AGENTS_PATH=C:\xampp\htdocs\BPM\agents

if exist "%AGENTS_PATH%" (
    del /q "%AGENTS_PATH%\*-config.json" 2>nul
    del /q "%AGENTS_PATH%\*-system-prompt.md" 2>nul  
    del /q "%AGENTS_PATH%\*-test.js" 2>nul
    del /q "%AGENTS_PATH%\registration-status.json" 2>nul
    del /q "%AGENTS_PATH%\README.md" 2>nul
    echo ✅ 에이전트 파일 제거 완료
) else (
    echo ⚠️ 에이전트 폴더가 존재하지 않습니다
)

REM 4. Claude Desktop 설정 백업에서 복원
echo [4/5] Claude Desktop 설정 복원 중...

set CLAUDE_CONFIG="%APPDATA%\Claude\settings.json"
set CLAUDE_MCP="%APPDATA%\Claude\.mcp.json"

REM 백업 파일이 있으면 복원
for /f "delims=" %%f in ('dir /b /o-d "%APPDATA%\Claude\settings.json.backup.*" 2^>nul') do (
    copy "%APPDATA%\Claude\%%f" %CLAUDE_CONFIG% >nul 2>&1
    echo ✅ Claude Desktop 설정 복원: %%f
    goto :config_restored
)
echo ⚠️ Claude Desktop 설정 백업을 찾을 수 없습니다

:config_restored

REM MCP 설정 백업 복원
for /f "delims=" %%f in ('dir /b /o-d "%APPDATA%\Claude\.mcp.json.backup.*" 2^>nul') do (
    copy "%APPDATA%\Claude\%%f" %CLAUDE_MCP% >nul 2>&1
    echo ✅ MCP 설정 복원: %%f
    goto :mcp_restored
)
echo ⚠️ MCP 설정 백업을 찾을 수 없습니다

:mcp_restored

REM 5. npm 캐시 정리
echo [5/5] npm 캐시 정리 중...
call npm cache clean --force >> "%LOGFILE%" 2>&1
echo ✅ npm 캐시 정리 완료

echo.
echo 🔄 Claude Desktop 재시작 중...

REM Claude Desktop 프로세스 종료
taskkill /f /im "Claude.exe" >nul 2>&1
taskkill /f /im "Claude Desktop.exe" >nul 2>&1

timeout /t 3 /nobreak >nul

REM Claude Desktop 시작 시도
start "" "%LOCALAPPDATA%\Programs\Claude\Claude.exe" >nul 2>&1
if %errorLevel% neq 0 (
    start "" "%PROGRAMFILES%\Claude\Claude.exe" >nul 2>&1
    if %errorLevel% neq 0 (
        echo ⚠️ Claude Desktop 자동 시작 실패 - 수동으로 시작하세요
    ) else (
        echo ✅ Claude Desktop 재시작 완료
    )
) else (
    echo ✅ Claude Desktop 재시작 완료
)

echo.
echo ========================================
echo  롤백 완료
echo ========================================
echo.
echo 📋 제거된 항목:
echo - SuperClaude CLI 도구
echo - MCP CLI 및 서버들
echo - 5개 전문 에이전트 파일
echo - 수정된 Claude Desktop 설정 (백업에서 복원)
echo.
echo 📋 보존된 항목:
echo - BPM 프로젝트 소스 코드
echo - 데이터베이스 데이터
echo - 사용자 설정 백업 파일들
echo.
echo 📄 롤백 로그: %LOGFILE%
echo.
echo ⚠️ 참고사항:
echo - Node.js, Python, Git 등 기본 도구는 제거되지 않았습니다
echo - 필요시 다시 01_auto_install_mcp.bat부터 실행하여 재설치 가능합니다
echo - 백업 파일들은 수동으로 정리하셔도 됩니다
echo.

pause
echo 🎯 롤백이 완료되었습니다.
echo 문제가 해결되면 다시 설치 스크립트를 실행하세요.
echo.
pause