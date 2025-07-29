@echo off
REM WSL 환경에서 Claude Code를 실행하는 Windows 배치 파일
REM 이 파일을 더블클릭하면 WSL에서 Claude Code가 실행됩니다.

echo ====================================
echo   WSL Claude Code 실행
echo ====================================
echo.

REM 현재 디렉토리 확인
echo 현재 디렉토리: %CD%
echo.

REM WSL이 설치되어 있는지 확인
wsl --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [오류] WSL이 설치되지 않았거나 실행할 수 없습니다.
    echo Microsoft Store에서 WSL을 설치하거나 Windows 기능을 활성화하세요.
    pause
    exit /b 1
)

echo WSL 환경이 확인되었습니다.
echo Claude Code를 실행합니다...
echo.

REM WSL에서 Claude Code 실행
cd /d "C:\Users\man4k\OneDrive\문서\APP"
wsl bash -c "cd /mnt/c/Users/man4k/OneDrive/문서/APP && source load_env_wsl.sh && ./start_claude_code_wsl.sh"

REM 실행 완료 메시지
echo.
echo Claude Code 실행이 완료되었습니다.
pause
