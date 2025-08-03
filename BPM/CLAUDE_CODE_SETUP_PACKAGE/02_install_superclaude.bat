@echo off
REM 📁 C:\xampp\htdocs\BPM\CLAUDE_CODE_SETUP_PACKAGE\02_install_superclaude.bat
REM Create at 2508031204 Ver1.00

echo ========================================
echo  SuperClaude AI 도구 설치 스크립트
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

REM Node.js 및 npm 확인
node --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ Node.js 버전: 
    node --version
) else (
    echo ❌ Node.js가 설치되지 않았습니다.
    pause
    exit /b 1
)

npm --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ npm 버전:
    npm --version
) else (
    echo ❌ npm이 설치되지 않았습니다.
    pause
    exit /b 1
)

echo.
echo 🤖 SuperClaude 설치 중...

REM 설치 로그 파일
set LOGFILE=%~dp0\superclaude_install.log
echo SuperClaude 설치 로그 - %date% %time% > "%LOGFILE%"

REM 기존 SuperClaude 제거 (오류 무시)
echo 🗑️ 기존 SuperClaude 제거 중...
call npm uninstall -g superclaude >> "%LOGFILE%" 2>&1

REM npm 캐시 클리어
echo 🧹 npm 캐시 클리어 중...
call npm cache clean --force >> "%LOGFILE%" 2>&1

REM SuperClaude 설치
echo 📦 SuperClaude 글로벌 설치 중...
echo 이 과정은 몇 분이 걸릴 수 있습니다...
echo.

call npm install -g superclaude >> "%LOGFILE%" 2>&1
if %errorLevel% == 0 (
    echo ✅ SuperClaude 설치 완료!
) else (
    echo ❌ SuperClaude 설치 실패
    echo.
    echo 🔄 대체 방법으로 재시도 중...
    
    REM 대체 방법 1: --force 옵션 사용
    call npm install -g superclaude --force >> "%LOGFILE%" 2>&1
    if %errorLevel% == 0 (
        echo ✅ SuperClaude 설치 완료! (--force 옵션 사용)
    ) else (
        echo ❌ 대체 방법도 실패
        echo.
        echo 📄 설치 로그를 확인하세요: %LOGFILE%
        echo.
        echo 🛠️ 수동 설치 방법:
        echo 1. PowerShell 관리자 권한으로 실행
        echo 2. npm install -g superclaude
        echo 3. 만약 계속 실패하면: npm install -g superclaude --force
        echo.
        pause
        exit /b 1
    )
)

echo.
echo 🧪 SuperClaude 설치 검증 중...

REM SuperClaude 버전 확인
superclaude --version >nul 2>&1
if %errorLevel% == 0 (
    echo ✅ SuperClaude 정상 설치됨:
    superclaude --version
    echo.
    
    echo 📋 SuperClaude 주요 명령어:
    echo - superclaude commit -i     : AI 커밋 메시지 생성
    echo - superclaude review        : 코드 리뷰
    echo - superclaude readme        : README 생성
    echo - superclaude docs          : 문서화
    echo - superclaude changelog     : 변경 이력 생성
    echo - superclaude annotate      : 코드 주석 추가
    echo.
) else (
    echo ❌ SuperClaude 설치 검증 실패
    echo PATH 환경 변수에 npm 글로벌 경로가 포함되어 있는지 확인하세요.
    echo.
    echo 🔧 문제 해결 방법:
    echo 1. PowerShell 재시작
    echo 2. 시스템 환경 변수 확인
    echo 3. npm root -g 경로가 PATH에 포함되어 있는지 확인
    echo.
    pause
    exit /b 1
)

echo.
echo 🎯 BPM 프로젝트 연동 설정 중...

REM BPM 프로젝트 폴더로 이동
cd /d "C:\xampp\htdocs\BPM"
if %errorLevel% neq 0 (
    echo ⚠️ BPM 프로젝트 폴더를 찾을 수 없습니다.
    echo C:\xampp\htdocs\BPM 경로를 확인하세요.
) else (
    echo ✅ BPM 프로젝트 폴더 확인됨
    
    REM .gitignore 확인 및 업데이트
    if exist ".gitignore" (
        findstr /c:"superclaude.log" .gitignore >nul
        if %errorLevel% neq 0 (
            echo. >> .gitignore
            echo # SuperClaude 로그 파일 >> .gitignore
            echo superclaude.log >> .gitignore
            echo *.superclaude >> .gitignore
            echo ✅ .gitignore 업데이트 완료
        )
    )
    
    REM SuperClaude 설정 파일 생성 (있는 경우 스킵)
    if not exist ".superclaude.json" (
        echo 📝 SuperClaude 설정 파일 생성 중...
        (
            echo {
            echo   "project": {
            echo     "name": "BPM Total Business Process Management",
            echo     "description": "Business Process Management SaaS with 10 modules",
            echo     "type": "web-application",
            echo     "framework": "PHP"
            echo   },
            echo   "ai": {
            echo     "provider": "claude",
            echo     "model": "claude-3-sonnet",
            echo     "commit_style": "conventional",
            echo     "review_depth": "thorough"
            echo   },
            echo   "files": {
            echo     "include": ["*.php", "*.js", "*.css", "*.html", "*.md"],
            echo     "exclude": ["vendor/", "node_modules/", "tests/", "*.log"]
            echo   }
            echo }
        ) > .superclaude.json
        echo ✅ SuperClaude 설정 파일 생성 완료
    )
)

echo.
echo 🧪 SuperClaude 기능 테스트...

REM 간단한 테스트 실행
echo 📝 SuperClaude 도움말 테스트:
superclaude --help
if %errorLevel% == 0 (
    echo ✅ SuperClaude 도움말 정상 작동
) else (
    echo ⚠️ SuperClaude 도움말 실행 실패
)

echo.
echo ========================================
echo  SuperClaude 설치 완료!
echo ========================================
echo.
echo 📋 다음 단계:
echo 1. 03_setup_agents.bat 실행 (5개 전문 에이전트 설치)
echo 2. 04_verify_installation.bat 실행 (전체 검증)
echo.
echo 🎯 SuperClaude 사용법:
echo - Git 커밋 전: superclaude commit -i
echo - 코드 리뷰: superclaude review
echo - 문서 생성: superclaude readme
echo - 변경이력: superclaude changelog
echo.
echo 📄 설치 로그: %LOGFILE%
echo.
echo ⚠️ 주의사항:
echo - BPM 프로젝트에서 SuperClaude 사용시 더 정확한 결과를 얻을 수 있습니다
echo - API 키가 필요한 일부 기능은 별도 설정이 필요할 수 있습니다
echo.

pause
echo 🎉 SuperClaude 설치가 완료되었습니다!
echo 이제 03_setup_agents.bat를 실행하세요.
echo.
pause