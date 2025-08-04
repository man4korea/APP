@echo off
chcp 65001 > nul
echo ==========================================
echo AppMart 자동 배포 스크립트
echo ==========================================
echo.

echo [1/4] 개발 환경 테스트 확인
echo 로컬 테스트 URL: http://localhost:8080
echo 배포 전 로컬에서 정상 동작을 확인하세요.
echo.
set /p confirm=계속 진행하시겠습니까? (y/N): 
if /i not "%confirm%"=="y" (
    echo 배포를 취소합니다.
    pause
    exit /b 0
)

echo.
echo [2/4] X:\html로 파일 복사 (프로덕션 자동 배포)
if not exist "X:\html" (
    echo 오류: X:\html 디렉토리가 존재하지 않습니다.
    echo X 드라이브가 마운트되었는지 확인하세요.
    pause
    exit /b 1
)

xcopy "C:\xampp\htdocs\AppMart\*" "X:\html\" /E /Y /I /Q
if %errorlevel% neq 0 (
    echo 오류: X:\html 복사 실패 (오류코드: %errorlevel%)
    pause
    exit /b 1
) else (
    echo ✓ 프로덕션 배포 완료 (X:\html)
)

echo.
echo [3/4] OneDrive 백업 복사
if not exist "C:\Users\man4k\OneDrive\문서\APP" (
    mkdir "C:\Users\man4k\OneDrive\문서\APP"
)

xcopy "C:\xampp\htdocs\AppMart\*" "C:\Users\man4k\OneDrive\문서\APP\AppMart\" /E /Y /I /Q
if %errorlevel% neq 0 (
    echo 경고: OneDrive 백업 복사 실패 (오류코드: %errorlevel%)
    echo 하지만 배포는 계속 진행합니다.
) else (
    echo ✓ OneDrive 백업 완료
)

echo.
echo [4/4] Git 커밋 및 푸시
cd "C:\xampp\htdocs\AppMart"

:: Git 상태 확인
"C:\Program Files\Git\bin\git.exe" status --porcelain > nul 2>&1
if %errorlevel% neq 0 (
    echo 경고: Git 저장소 상태 확인 실패
)

:: 변경사항 추가
"C:\Program Files\Git\bin\git.exe" add .
if %errorlevel% neq 0 (
    echo 경고: Git add 실패
)

:: 커밋 메시지 생성
for /f "tokens=1-3 delims=/ " %%a in ('date /t') do set mydate=%%a-%%b-%%c
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set mytime=%%a:%%b
set commit_msg=feat: 프로덕션 배포 %mydate% %mytime%

:: 커밋
"C:\Program Files\Git\bin\git.exe" commit -m "%commit_msg%"
if %errorlevel% neq 0 (
    echo 경고: Git commit 실패 (변경사항이 없을 수 있습니다)
)

:: 푸시
"C:\Program Files\Git\bin\git.exe" push origin main
if %errorlevel% neq 0 (
    echo 경고: Git push 실패
    echo 네트워크 연결 또는 인증 정보를 확인하세요.
) else (
    echo ✓ Git 푸시 완료
)

echo.
echo ==========================================
echo 🎉 배포 완료!
echo ==========================================
echo 프로덕션 사이트: http://appmart.dothome.co.kr
echo 개발 사이트: http://localhost:8080
echo.
echo 배포 후 확인사항:
echo 1. 프로덕션 사이트 접속 테스트
echo 2. 데이터베이스 연결 확인
echo 3. 주요 기능 동작 테스트
echo.
echo 문제 발생 시 DEPLOYMENT_GUIDE.md를 참고하세요.
echo ==========================================
pause