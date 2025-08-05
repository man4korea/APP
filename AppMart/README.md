# AppMart - AI 웹앱 마켓플레이스

## 개발 및 배포 프로세스

### 1. 개발 환경
- **개발 위치**: `C:\xampp\htdocs\AppMart`
- **테스트 URL**: http://localhost:8080
- **데이터베이스**: 로컬 XAMPP MySQL (appmart_db)

### 2. 배포 프로세스 (순서대로 진행)

#### 단계 1: 개발 
1. 로컬 환경에서 개발 완료

#### 단계 2: 프로덕션 배포
```bash
1. X:\html 디렉토리로 파일 복사 (자동으로 appmart.dothome.co.kr에 업로드됨)
xcopy "C:\xampp\htdocs\AppMart\*" "X:\html\" /E /Y /I

2. 데이터베이스 연결 및 기능 동작 확인

3. Git 저장소에 푸시
"C:\Program Files\Git\bin\git.exe" add .
"C:\Program Files\Git\bin\git.exe" commit -m "feat: 프로덕션 배포 준비 완료"
"C:\Program Files\Git\bin\git.exe" push origin main
```

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
  
*Last updated: 2025-08-04
