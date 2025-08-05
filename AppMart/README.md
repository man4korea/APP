# AppMart - AI 웹앱 마켓플레이스

## 개발 및 배포 프로세스

### 1. 개발 환경
- **개발 위치**: `C:\xampp\htdocs\AppMart`
- **테스트 URL**: http://localhost:8080
- **데이터베이스**: 로컬 XAMPP MySQL (appmart_db)

### 2. 배포 프로세스 (순서대로 진행)

#### 단계 1: 개발 
1. 로컬 환경에서 개발 완료

#### 단계 2: 프로덕션 배포 및 테스트 (완전 가이드)

##### 2.1 파일 배포
```bash
# RaiDrive를 통한 FTP 연결 확인
net use  # X: 드라이브 연결 상태 확인

# 프로덕션용 환경파일 준비
cp .env.production X:\html\.env

# 주요 파일들 배포
cp bootstrap.php X:\html\bootstrap.php
cp -r public X:\html\public
cp -r src X:\html\src
cp -r assets X:\html\assets
cp -r database X:\html\database
cp -r config X:\html\config

# 중복된 src 디렉토리 구조 수정 (필요시)
cp -r "X:\html\src\src\controllers" "X:\html\src\controllers"
cp -r "X:\html\src\src\views" "X:\html\src\views"
cp -r "X:\html\src\src\services" "X:\html\src\services"
```

##### 2.2 서버 테스트 절차
```bash
# 1. 기본 PHP 동작 확인
http://appmart.dothome.co.kr/test.php

# 2. 데이터베이스 연결 테스트
http://appmart.dothome.co.kr/?page=test-db

# 3. 환경 변수 확인
http://appmart.dothome.co.kr/?page=env

# 4. 데이터베이스 마이그레이션 실행
http://appmart.dothome.co.kr/migrate.php

# 5. 메인 애플리케이션 테스트 (index.php로 설정)
http://appmart.dothome.co.kr
# 또는 직접: http://appmart.dothome.co.kr/index.php
```

##### 2.3 배포 문제 해결
**일반적인 문제들:**
- **404 에러**: 파일 업로드 확인, 캐시 클리어
- **환경 파일 경로 오류**: .env 파일이 올바른 위치에 있는지 확인
- **데이터베이스 연결 실패**: 프로덕션 DB 설정 재확인
- **Session 경고**: HTML 출력 전에 세션 설정 필요

**디버깅 도구:**
```php
# 디버그 페이지 생성 (debug.php)
error_reporting(E_ALL);
ini_set('display_errors', 1);
// 테스트 코드 추가
```

##### 2.4 Git 커밋
```bash
"C:\Program Files\Git\bin\git.exe" add .
"C:\Program Files\Git\bin\git.exe" commit -m "feat: 프로덕션 환경 배포 완료 및 테스트 성공

✅ 웹서버 배포 완료 (http://appmart.dothome.co.kr)
✅ 데이터베이스 연결 테스트 성공
✅ PHP 8.4.10 정상 동작 확인
✅ Environment 설정 로드 성공
✅ 자동 배포 시스템 구축 완료

🚀 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>"
"C:\Program Files\Git\bin\git.exe" push origin main
```

##### 2.5 배포 검증 체크리스트
**✅ 성공 기준:**
- [ ] 웹서버 정상 응답 (200 OK)
- [ ] PHP 8.4.10 동작 확인
- [ ] 데이터베이스 연결 성공
- [ ] 환경 변수 로드 확인
- [ ] 12개 데이터베이스 테이블 생성
- [ ] Bootstrap 로드 성공
- [ ] HomeController 인스턴스화 성공
- [ ] 메인 페이지 렌더링 완료

**🔗 테스트 완료 URL:**
- **메인 앱**: http://appmart.dothome.co.kr (index.php)
- **시스템 상태**: http://appmart.dothome.co.kr/?page=test-db
- **환경 확인**: http://appmart.dothome.co.kr/?page=env

### 3. 환경별 설정 차이점

#### 개발 환경 (.env)
```env
APP_ENV=development
APP_DEBUG=true
DB_HOST=localhost
DB_NAME=appmart_db
DB_USER=root
DB_PASS=
```

#### 4. 프로덕션 환경 (.env)
```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_NAME=appmart
DB_USER=appmart
DB_PASS=dmlwjdqn!Wkd24
```
### 5. 주의사항

#### 🛡️ 보안 설정

모든 API 키와 중요 설정은 `.env` 파일에서 관리합니다.

#### 보안 주의사항
- `.env` 파일의 민감한 정보 (비밀번호, API 키) 노출 방지
- 프로덕션에서는 반드시 `APP_DEBUG=false` 설정

#### 롤백 절차
문제 발생 시 이전 버전으로 복구:
1. Git에서 이전 커밋으로 되돌리기
2. X:\html에 이전 버전 재배포
3. 필요시 데이터베이스 백업 복구

## 💻 시스템 아키텍처 (MVP)

- **Backend**: PHP 8.x + MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Server**: Apache 2.4 PHP 8.4 (http://appmart.dothome.co.kr)
  FTP 112.175.185.148  ID:appmart password:dmlwjdqn!Wkd24
  DB MySQL 8.0 DB명 appmart DB 아이디 appmart DB암호 dmlwjdqn!Wkd24

## 🗂️ AppMart MVP 프로젝트 구조

```
AppMart/
├── 📄 SHRIMP_Tasks.md                # ✅ 전체 작업 관리
├── 📄 app_mart_prd.md                # 📜 제품 요구사항 명세서
├── 📄 .env                           # ⚙️ 환경 설정
├── 📂 public/                         # 🌐 웹 루트 (index.php, css/, js/)
├── 📂 src/                            # 💡 소스 코드 (controllers/, models/, views/, core/)
├── 📂 uploads/                        # 📤 업로드 파일 저장
├── 📂 tests/                          # 🧪 테스트 코드
└── 📂 sql/                            # 🗄️ 데이터베이스 스키마
---


## ✍️ 파일 헤더 표준 양식 (json을 제외한 모든 파일)

```php
// C:\xampp\htdocs\AppMart\[하위경로]\[파일명]
// Create at YYMMDDhhmm Ver1.00
```

**시간 형식**: 한국시간(KST) 기준  
**예시**: `2508041030` = 2025년 8월 4일 10시 30분

---

## 🚀 미래 확장 계획

### 단기 목표 (1-3개월)
- 결제 시스템 연동 (PG사)
- 사용자 리뷰 및 평점 기능
- 개발자 수익 정산 시스템

### 중기 목표 (3-6개월)
- 앱 분석 및 통계 대시보드
- 다양한 인증 방식 지원 (소셜 로그인)
- API 기반 앱 등록 지원

### 장기 목표 (6-12개월)
- 구독 기반 유료 플랜 도입
- 글로벌 서비스 확장
- AI 기반 앱 추천 시스템 구축
  
*Last updated: 2025-08-05 (서버 배포 테스트 가이드 추가)
