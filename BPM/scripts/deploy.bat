REM 📁 C:\xampp\htdocs\BPM\scripts\deploy.bat
REM Create at 2508021941 Ver1.01

@echo off
REM BPM Total Business Process Management - 배포 자동화 스크립트
REM 
REM 용도: 테스트 통과시 OneDrive 및 웹서버 자동 배포
REM 실행: scripts\deploy.bat

echo.
echo ================================
echo  BPM 자동 배포 시스템
echo ================================
echo.

REM 현재 시간 설정
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"
set "timestamp=%YYYY%-%MM%-%DD% %HH%:%Min%:%Sec%"

echo 📅 배포 시작 시간: %timestamp%
echo.

REM 1단계: XAMPP 서비스 확인
echo 🔍 1단계: XAMPP 서비스 확인...
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo ✅ Apache 서버 실행중
) else (
    echo ❌ Apache 서버가 실행되지 않음
    echo    XAMPP Control Panel에서 Apache를 시작하세요.
    pause
    exit /b 1
)

tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo ✅ MySQL 서버 실행중
) else (
    echo ❌ MySQL 서버가 실행되지 않음
    echo    XAMPP Control Panel에서 MySQL을 시작하세요.
    pause
    exit /b 1
)

echo.

REM 2단계: Playwright 테스트 실행
echo 🧪 2단계: Playwright 테스트 실행...
cd /d "C:\xampp\htdocs\BPM"

REM Node.js 및 npm 확인
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo ⚠️ Node.js가 설치되지 않음 - 테스트 건너뜀
    goto :skip_test
)

REM package.json 확인
if not exist "package.json" (
    echo 📦 package.json 생성 중...
    echo { > package.json
    echo   "name": "bpm-tests", >> package.json
    echo   "version": "1.0.0", >> package.json
    echo   "dependencies": { >> package.json
    echo     "playwright": "^1.40.0" >> package.json
    echo   } >> package.json
    echo } >> package.json
    
    echo 📥 Playwright 설치 중...
    call npm install
)

echo 🚀 테스트 실행 중...
node tests\bpm-module-test.js

if %errorlevel% neq 0 (
    echo.
    echo ❌ 테스트 실패 - 배포를 중단합니다.
    echo    tests\test-report.json 및 tests\screenshots\ 확인
    pause
    exit /b 1
)

echo ✅ 모든 테스트 통과!

:skip_test
echo.

REM 3단계: OneDrive 백업
echo 📁 3단계: OneDrive 백업...
set "onedrive_path=C:\Users\man4k\OneDrive\문서\APP\bpm"

if not exist "%onedrive_path%" (
    echo 📁 OneDrive 디렉토리 생성: %onedrive_path%
    mkdir "%onedrive_path%"
)

echo 📋 파일 복사 중...
xcopy "C:\xampp\htdocs\BPM\*" "%onedrive_path%\" /E /Y /I /Q

if %errorlevel% equ 0 (
    echo ✅ OneDrive 백업 완료
) else (
    echo ❌ OneDrive 백업 실패
    pause
    exit /b 1
)

echo.

REM 4단계: 웹서버 배포
echo 🌐 4단계: 웹서버 배포...
set "webserver_path=Z:\html\bpm"

REM Z 드라이브 확인
if not exist "Z:\" (
    echo ⚠️ Z 드라이브가 연결되지 않음
    echo    네트워크 드라이브를 연결하거나 수동으로 배포하세요.
    goto :skip_webserver
)

if not exist "%webserver_path%" (
    echo 📁 웹서버 디렉토리 생성: %webserver_path%
    mkdir "%webserver_path%"
)

echo 🚀 웹서버 배포 중...
xcopy "C:\xampp\htdocs\BPM\*" "%webserver_path%\" /E /Y /I /Q

if %errorlevel% equ 0 (
    echo ✅ 웹서버 배포 완료
) else (
    echo ❌ 웹서버 배포 실패
    goto :skip_webserver
)

goto :deploy_success

:skip_webserver
echo ⚠️ 웹서버 배포 건너뜀

:deploy_success
echo.
echo ================================
echo  🎉 배포 완료!
echo ================================
echo.
echo 📊 배포 요약:
echo   - 로컬 개발: C:\xampp\htdocs\BPM\
echo   - OneDrive 백업: %onedrive_path%
if exist "%webserver_path%" (
    echo   - 웹서버: %webserver_path%
)
echo   - 배포 시간: %timestamp%
echo.

REM 배포 로그 저장
echo %timestamp% - 배포 완료 >> logs\deploy.log
echo 배포 성공: 로컬 -^> OneDrive -^> 웹서버 >> logs\deploy.log

echo 🔗 접속 URL:
echo   - 로컬: http://localhost/BPM/
if exist "%webserver_path%" (
    echo   - 웹서버: [웹서버 URL]/bpm/
)
echo.

pause