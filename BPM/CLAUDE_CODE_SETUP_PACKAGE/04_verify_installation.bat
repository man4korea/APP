@echo off
REM 📁 C:\xampp\htdocs\BPM\CLAUDE_CODE_SETUP_PACKAGE\04_verify_installation.bat
REM Create at 2508031208 Ver1.00

echo ========================================
echo  Claude Code 환경 설치 완료 검증
echo ========================================
echo.

echo 🔍 전체 설치 환경을 검증합니다...
echo.

REM 검증 결과 파일
set LOGFILE=%~dp0\verification_results.log
set BPM_PATH=C:\xampp\htdocs\BPM
set AGENTS_PATH=%BPM_PATH%\agents

echo 검증 시작 - %date% %time% > "%LOGFILE%"
echo ================================== >> "%LOGFILE%"

REM 검증 점수 카운터
set TOTAL_CHECKS=0
set PASSED_CHECKS=0

echo 📋 1. 기본 시스템 요구사항 확인
echo ================================

REM Node.js 확인
set /a TOTAL_CHECKS+=1
node --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Node.js 설치됨: 
    node --version
    echo [PASS] Node.js 설치됨 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ❌ Node.js 미설치
    echo [FAIL] Node.js 미설치 >> "%LOGFILE%"
)

REM npm 확인
set /a TOTAL_CHECKS+=1
npm --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ npm 설치됨:
    npm --version
    echo [PASS] npm 설치됨 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ❌ npm 미설치
    echo [FAIL] npm 미설치 >> "%LOGFILE%"
)

REM Git 확인
set /a TOTAL_CHECKS+=1
git --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Git 설치됨:
    git --version
    echo [PASS] Git 설치됨 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ❌ Git 미설치
    echo [FAIL] Git 미설치 >> "%LOGFILE%"
)

REM Python 확인
set /a TOTAL_CHECKS+=1
python --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Python 설치됨:
    python --version
    echo [PASS] Python 설치됨 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ⚠️ Python 미설치 (일부 MCP 서버에 필요할 수 있음)
    echo [WARN] Python 미설치 >> "%LOGFILE%"
)

echo.
echo 📦 2. MCP 서버 설치 확인
echo ========================

REM MCP CLI 확인
set /a TOTAL_CHECKS+=1
npx @modelcontextprotocol/cli --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ MCP CLI 설치됨
    echo [PASS] MCP CLI 설치됨 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ❌ MCP CLI 미설치
    echo [FAIL] MCP CLI 미설치 >> "%LOGFILE%"
)

REM MCP 서버 목록 확인
echo 📡 설치된 MCP 서버 목록:
npx @modelcontextprotocol/cli list 2>nul
if %errorLevel% == 0 (
    echo ✅ MCP 서버 목록 조회 성공
    echo [PASS] MCP 서버 목록 조회 성공 >> "%LOGFILE%"
) else (
    echo ⚠️ MCP 서버 목록 조회 실패 (일부 서버가 설치되지 않았을 수 있음)
    echo [WARN] MCP 서버 목록 조회 실패 >> "%LOGFILE%"
)

echo.
echo 🤖 3. SuperClaude 설치 확인
echo ==========================

set /a TOTAL_CHECKS+=1
superclaude --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ SuperClaude 설치됨:
    superclaude --version
    echo [PASS] SuperClaude 설치됨 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ❌ SuperClaude 미설치
    echo [FAIL] SuperClaude 미설치 >> "%LOGFILE%"
)

REM SuperClaude 기능 테스트
set /a TOTAL_CHECKS+=1
superclaude --help >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ SuperClaude 도움말 실행 가능
    echo [PASS] SuperClaude 도움말 실행 가능 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ❌ SuperClaude 도움말 실행 실패
    echo [FAIL] SuperClaude 도움말 실행 실패 >> "%LOGFILE%"
)

echo.
echo 👥 4. 에이전트 설치 확인
echo =====================

REM BPM 프로젝트 폴더 확인
set /a TOTAL_CHECKS+=1
if exist "%BPM_PATH%" (
    echo ✅ BPM 프로젝트 폴더 존재: %BPM_PATH%
    echo [PASS] BPM 프로젝트 폴더 존재 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ❌ BPM 프로젝트 폴더 미존재: %BPM_PATH%
    echo [FAIL] BPM 프로젝트 폴더 미존재 >> "%LOGFILE%"
)

REM agents 폴더 확인
set /a TOTAL_CHECKS+=1
if exist "%AGENTS_PATH%" (
    echo ✅ agents 폴더 존재: %AGENTS_PATH%
    echo [PASS] agents 폴더 존재 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ❌ agents 폴더 미존재: %AGENTS_PATH%
    echo [FAIL] agents 폴더 미존재 >> "%LOGFILE%"
    goto :skip_agent_check
)

REM 각 에이전트 파일 확인
echo 📋 에이전트별 상세 확인:

for %%a in (code-reviewer security-reviewer tech-lead ux-reviewer code-simplifier) do (
    echo.
    echo [%%a 에이전트]
    
    REM config.json 확인
    set /a TOTAL_CHECKS+=1
    if exist "%AGENTS_PATH%\%%a-config.json" (
        echo   ✅ config.json 존재
        echo [PASS] %%a config.json 존재 >> "%LOGFILE%"
        set /a PASSED_CHECKS+=1
    ) else (
        echo   ❌ config.json 누락
        echo [FAIL] %%a config.json 누락 >> "%LOGFILE%"
    )
    
    REM system-prompt.md 확인
    set /a TOTAL_CHECKS+=1
    if exist "%AGENTS_PATH%\%%a-system-prompt.md" (
        echo   ✅ system-prompt.md 존재
        echo [PASS] %%a system-prompt.md 존재 >> "%LOGFILE%"
        set /a PASSED_CHECKS+=1
    ) else (
        echo   ❌ system-prompt.md 누락
        echo [FAIL] %%a system-prompt.md 누락 >> "%LOGFILE%"
    )
    
    REM test.js 확인
    set /a TOTAL_CHECKS+=1
    if exist "%AGENTS_PATH%\%%a-test.js" (
        echo   ✅ test.js 존재
        echo [PASS] %%a test.js 존재 >> "%LOGFILE%"
        set /a PASSED_CHECKS+=1
    ) else (
        echo   ❌ test.js 누락
        echo [FAIL] %%a test.js 누락 >> "%LOGFILE%"
    )
)

:skip_agent_check

echo.
echo 📁 5. 프로젝트 파일 구조 확인
echo ============================

REM 중요 프로젝트 파일들 확인
set PROJECT_FILES=sql\schema.sql includes\config.php core\Security.php core\Router.php core\Cache.php composer.json

for %%f in (%PROJECT_FILES%) do (
    set /a TOTAL_CHECKS+=1
    if exist "%BPM_PATH%\%%f" (
        echo ✅ %%f 존재
        echo [PASS] %%f 존재 >> "%LOGFILE%"
        set /a PASSED_CHECKS+=1
    ) else (
        echo ⚠️ %%f 누락
        echo [WARN] %%f 누락 >> "%LOGFILE%"
    )
)

echo.
echo 🔧 6. Claude Desktop 설정 확인
echo =============================

REM Claude Desktop 설정 파일 확인
set CLAUDE_CONFIG="%APPDATA%\Claude\settings.json"
set CLAUDE_MCP="%APPDATA%\Claude\.mcp.json"

set /a TOTAL_CHECKS+=1
if exist %CLAUDE_CONFIG% (
    echo ✅ Claude Desktop 설정 파일 존재
    echo [PASS] Claude Desktop 설정 파일 존재 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ⚠️ Claude Desktop 설정 파일 미존재
    echo [WARN] Claude Desktop 설정 파일 미존재 >> "%LOGFILE%"
)

set /a TOTAL_CHECKS+=1
if exist %CLAUDE_MCP% (
    echo ✅ MCP 설정 파일 존재
    echo [PASS] MCP 설정 파일 존재 >> "%LOGFILE%"
    set /a PASSED_CHECKS+=1
) else (
    echo ⚠️ MCP 설정 파일 미존재
    echo [WARN] MCP 설정 파일 미존재 >> "%LOGFILE%"
)

echo.
echo 🧪 7. 기능 테스트
echo ================

REM 각 에이전트 테스트 스크립트 실행
if exist "%AGENTS_PATH%" (
    echo 📋 에이전트 테스트 실행:
    
    cd /d "%AGENTS_PATH%"
    
    for %%a in (code-reviewer security-reviewer tech-lead ux-reviewer code-simplifier) do (
        if exist "%%a-test.js" (
            echo [%%a 테스트]
            node "%%a-test.js"
            if %errorLevel% == 0 (
                echo ✅ %%a 테스트 통과
                echo [PASS] %%a 테스트 통과 >> "%LOGFILE%"
            ) else (
                echo ❌ %%a 테스트 실패
                echo [FAIL] %%a 테스트 실패 >> "%LOGFILE%"
            )
        )
    )
)

echo.
echo ========================================
echo  검증 결과 요약
echo ========================================

REM 성공률 계산
set /a SUCCESS_RATE=(%PASSED_CHECKS% * 100) / %TOTAL_CHECKS%

echo.
echo 📊 검증 통계:
echo - 총 검사 항목: %TOTAL_CHECKS%개
echo - 통과 항목: %PASSED_CHECKS%개
echo - 성공률: %SUCCESS_RATE%%%
echo.

REM 성공률에 따른 평가
if %SUCCESS_RATE% GEQ 90 (
    echo 🎉 우수! 설치가 성공적으로 완료되었습니다.
    echo [RESULT] 설치 성공 - %SUCCESS_RATE%%% >> "%LOGFILE%"
) else if %SUCCESS_RATE% GEQ 70 (
    echo ✅ 양호! 대부분의 구성요소가 정상 설치되었습니다.
    echo [RESULT] 설치 양호 - %SUCCESS_RATE%%% >> "%LOGFILE%"
) else if %SUCCESS_RATE% GEQ 50 (
    echo ⚠️ 주의! 일부 구성요소에 문제가 있습니다.
    echo [RESULT] 설치 주의 - %SUCCESS_RATE%%% >> "%LOGFILE%"
) else (
    echo ❌ 실패! 많은 구성요소가 정상 설치되지 않았습니다.
    echo [RESULT] 설치 실패 - %SUCCESS_RATE%%% >> "%LOGFILE%"
)

echo.
echo 📋 다음 단계 가이드:

if %SUCCESS_RATE% GEQ 90 (
    echo 🚀 Claude Code를 시작하여 다음 명령어들을 테스트해보세요:
    echo.
    echo # MCP 서버 테스트
    echo - shrimp-task-manager:list_tasks all
    echo - mcp__filesystem__list_allowed_directories  
    echo - mcp__github__search_repositories query:"test"
    echo.
    echo # SuperClaude 테스트
    echo - superclaude --version
    echo - superclaude commit -i
    echo.
    echo # 에이전트 테스트  
    echo - claude agents list
    echo - claude agents tech-lead
    echo.
) else (
    echo 🔧 문제 해결이 필요합니다:
    echo.
    if %SUCCESS_RATE% LSS 50 (
        echo 1. 01_auto_install_mcp.bat 재실행
        echo 2. 02_install_superclaude.bat 재실행
        echo 3. 03_setup_agents.bat 재실행
        echo.
    )
    echo 상세한 오류 내용은 다음 파일을 확인하세요:
    echo - %LOGFILE%
    echo - mcp_install.log
    echo - superclaude_install.log
    echo - agents_install.log
)

echo.
echo 📄 검증 보고서: %LOGFILE%
echo 📁 프로젝트 경로: %BPM_PATH%
echo 📁 에이전트 경로: %AGENTS_PATH%
echo.

echo ========================================
echo  환경 복제 설치 검증 완료
echo ========================================
echo.

REM 최종 안내
if %SUCCESS_RATE% GEQ 80 (
    echo 🎉 축하합니다! 
    echo 집 노트북과 동일한 Claude Code 환경이 회사 노트북에 성공적으로 구축되었습니다.
    echo.
    echo BPM 프로젝트 개발을 계속 진행할 수 있습니다.
    echo.
    echo Claude Code에서 다음 명령으로 SHRIMP 작업을 확인하세요:
    echo cd C:\xampp\htdocs\BPM
    echo shrimp-task-manager:list_tasks all
) else (
    echo 🔧 일부 구성요소에 문제가 있습니다.
    echo 위의 가이드를 따라 문제를 해결한 후 다시 검증하세요.
)

echo.
pause