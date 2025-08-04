# AppMart 배포 가이드

## 개발 및 배포 프로세스

### 1. 개발 환경
- **개발 위치**: `C:\xampp\htdocs\AppMart`
- **테스트 URL**: http://localhost:8080
- **데이터베이스**: 로컬 XAMPP MySQL (appmart_db)

### 2. 배포 프로세스 (순서대로 진행)

#### 단계 1: 개발 및 테스트 완료
1. 로컬 환경에서 개발 완료
2. `http://localhost:8080`에서 기능 테스트 완료
3. 데이터베이스 연결 및 기능 동작 확인

#### 단계 2: 프로덕션 배포
```bash
# 1. X:\html 디렉토리로 파일 복사 (자동으로 appmart.dothome.co.kr에 업로드됨)
xcopy "C:\xampp\htdocs\AppMart\*" "X:\html\" /E /Y /I

# 2. 백업용 OneDrive 위치로 복사
xcopy "C:\xampp\htdocs\AppMart\*" "C:\Users\man4k\OneDrive\문서\APP\AppMart\" /E /Y /I

# 3. Git 저장소에 푸시
cd "C:\xampp\htdocs\AppMart"
"C:\Program Files\Git\bin\git.exe" add .
"C:\Program Files\Git\bin\git.exe" commit -m "feat: 프로덕션 배포 준비 완료"
"C:\Program Files\Git\bin\git.exe" push origin main
```

### 3. 배포 후 확인사항

#### 프로덕션 환경 확인
- **사이트 URL**: http://appmart.dothome.co.kr
- **데이터베이스**: appmart (웹호스팅 MySQL)
- **FTP 정보**: 
  - 호스트: 112.175.185.148
  - 사용자: appmart
  - 비밀번호: dmlwjdqn!Wkd24

#### 배포 후 체크리스트
- [ ] 사이트 접속 확인 (http://appmart.dothome.co.kr)
- [ ] 데이터베이스 연결 확인
- [ ] 주요 기능 동작 테스트
- [ ] 보안 설정 적용 확인
- [ ] 에러 로그 확인

### 4. 환경별 설정 차이점

#### 개발 환경 (.env)
```env
APP_ENV=development
APP_DEBUG=true
DB_HOST=localhost
DB_NAME=appmart_db
DB_USER=root
DB_PASS=
```

#### 프로덕션 환경 (.env)
```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_NAME=appmart
DB_USER=appmart
DB_PASS=dmlwjdqn!Wkd24
```

### 5. 자동화 스크립트

#### Windows 배치 파일 (deploy.bat)
```batch
@echo off
echo AppMart 배포 시작...

echo 1단계: X:\html로 파일 복사 (프로덕션 배포)
xcopy "C:\xampp\htdocs\AppMart\*" "X:\html\" /E /Y /I
if %errorlevel% neq 0 (
    echo 오류: X:\html 복사 실패
    pause
    exit /b 1
)

echo 2단계: OneDrive 백업 복사
xcopy "C:\xampp\htdocs\AppMart\*" "C:\Users\man4k\OneDrive\문서\APP\AppMart\" /E /Y /I
if %errorlevel% neq 0 (
    echo 경고: OneDrive 복사 실패
)

echo 3단계: Git 커밋 및 푸시
cd "C:\xampp\htdocs\AppMart"
"C:\Program Files\Git\bin\git.exe" add .
"C:\Program Files\Git\bin\git.exe" commit -m "feat: 배포 버전 %date% %time%"
"C:\Program Files\Git\bin\git.exe" push origin main

echo 배포 완료!
echo 프로덕션 사이트: http://appmart.dothome.co.kr
pause
```

### 6. 주의사항

#### 보안 주의사항
- `.env` 파일의 민감한 정보 (비밀번호, API 키) 노출 방지
- 프로덕션에서는 반드시 `APP_DEBUG=false` 설정
- 정기적인 비밀번호 변경 권장

#### 백업 정책
- 배포 전 항상 현재 버전 백업
- 데이터베이스 백업 정기 실행
- Git을 통한 소스코드 버전 관리

#### 롤백 절차
문제 발생 시 이전 버전으로 복구:
1. Git에서 이전 커밋으로 되돌리기
2. X:\html에 이전 버전 재배포
3. 필요시 데이터베이스 백업 복구

### 7. 연락처 및 지원

- **개발자**: man4k
- **Git 저장소**: GitHub (main 브랜치)
- **호스팅**: dothome.co.kr
- **도메인**: appmart.dothome.co.kr