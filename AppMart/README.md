# AppMart - AI 웹앱 마켓플레이스

**AI 웹앱을 등록 관리하고, 공유하고, 수익을 창출하는 가장 쉬운 방법입니다.**

## 🚀 프로젝트 개요

AppMart는 개발자들이 자신의 AI 기반 웹 애플리케이션을 쉽게 등록하고 판매할 수 있으며, 일반 사용자들은 필요한 앱을 찾거나 개발을 의뢰할 수 있는 개방형 마켓플레이스 플랫폼입니다. Firebase, Supabase, MySQL 등 다양한 기술 스택으로 만들어진 앱들을 모두 환영합니다.

1. 프로젝트 루트 : C:\xampp\htdocs\AppMart
2. 개발 Test는 xampp의 아파치 서버에서 도구에 추가된 playwright나 puppeteer를 활용해서 해줘.
3. 개발 Test가 완료된 파일은 X:\html에 복사해주고 C:\Users\man4k\OneDrive\문서\APP\AppMart에 Copy해줘.
4. Git은 "C:\Program Files\Git" 폴더에 설치해두었으니 이걸 활용해서 push해줘.
5. `SHRIMP_Tasks.md`를 모든 작업의 **마스터 기준**으로 삼습니다.

## ✨ 주요 특징

- **앱 등록 및 판매**: 개발한 웹앱을 간단하게 등록하고 자신만의 가격 정책으로 판매하여 수익을 창출하세요.
- **앱 스토어**: 다양한 카테고리의 AI 웹앱을 탐색하고, 검색하며, 필터링하여 원하는 앱을 찾아보세요.
- **안전한 결제**: 안전하고 간편한 결제 시스템을 통해 유료 앱을 구매하고 즉시 다운로드할 수 있습니다.
- **개발 요청**: 원하는 기능의 앱이 없다면, 아이디어를 게시하여 다른 개발자에게 개발을 의뢰할 수 있습니다.
- **커뮤니뮤니티**: 개발자와 사용자 간의 소통을 통해 앱을 개선하고 새로운 비즈니스 기회를 만들어갑니다.
- `app_mart_prd.md`: 프로젝트의 상세한  목표와 요구사항을 확인
- 
## 💻 시스템 아키텍처 (MVP)

- **Backend**: PHP 8.x + MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Server**: Apache 2.4 PHP 8.4 (http://appmart.dothome.co.kr)
  FTP 112.175.185.148  ID:appmart password:dmlwjdqn!Wkd24
  DB MySQL 8.0 DB명 appmart DB 아이디 appmart DB암호 dmlwjdqn!Wkd24
- **보안**: CSRF 토큰, 세션 관리, 비밀번호 해싱

## 🗂️ 프로젝트 구조

```
AppMart/
├── 📄 README.md                      # 📜 프로젝트 소개 (본 파일)
├── 📄 APP_MART_PROJECT_GUIDE.md      # 📜 공통 개발 지침서
├── 📄 app_mart_prd.md                # 📜 제품 요구사항 명세서
├── 📄 .env                           # ⚙️ 환경 설정
├── 📂 public/                         # 🌐 웹 루트 (index.php, css/, js/)
├── 📂 src/                            # 💡 소스 코드 (controllers/, models/, views/)
├── 📂 uploads/                        # 📤 업로드 파일 저장
└── 📂 tests/                          # 🧪 테스트 코드
```

## 🛠️ 설치 및 실행

### 요구사항

- **PHP 8.4** 이상
- **MySQL 8.0** 이상
- **Apache 2.4** 웹 서버
- **XAMPP** (로컬 개발환경)
- **Git** (C:\Program Files\Git)

### 설치 과정

1. **프로젝트 클론**
   ```bash
   cd C:\xampp\htdocs
   git clone https://github.com/your-username/AppMart.git
   cd AppMart
   ```

2. **데이터베이스 설정**
   ```bash
   # MySQL 데이터베이스 생성
   "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS appmart_db;"
   
   # 테이블 생성 (SQL 스크립트 실행)
   "C:\xampp\mysql\bin\mysql.exe" -u root -e "source C:/xampp/htdocs/AppMart/sql/appmart_db.sql"
   ```

3. **환경 변수 설정**
   - `.env` 파일에 자신의 데이터베이스 정보 및 기타 설정을 추가합니다.
   - 개발환경은 기본적으로 XAMPP 설정으로 구성되어 있습니다.
   
   **개발환경 (.env 파일):**
   ```env
   # 개발환경 설정 (로컬 XAMPP)
   DB_HOST=localhost
   DB_NAME=appmart_db
   DB_USER=root
   DB_PASS=
   SITE_URL=http://localhost/AppMart
   
   # 프로덕션 설정 (웹호스팅 환경용 - 주석처리)
   # DB_HOST=localhost
   # DB_NAME=appmart
   # DB_USER=appmart
   # DB_PASS=dmlwjdqn!Wkd24
   # SITE_URL=http://appmart.dothome.co.kr
   
   # FTP 설정 (배포용)
   FTP_HOST=112.175.185.148
   FTP_USER=appmart
   FTP_PASS=dmlwjdqn!Wkd24
   ```

4. **서버 실행**
   ```bash
   # XAMPP 컨트롤 패널에서 Apache와 MySQL 시작
   # 또는 PHP 내장 서버 사용
   "C:\xampp\php\php.exe" -S localhost:8080 -t public/
   ```
   
   브라우저에서 `http://localhost/AppMart` 또는 `http://localhost:8080` 접속

## 🤝 Git 사용 방법

### 기본 워크플로우
```bash
# Git 경로 설정 (시스템에 설치된 Git 사용)
set PATH="C:\Program Files\Git\bin";%PATH%

# 변경사항 확인
git status

# 파일 추가 및 커밋
git add .
git commit -m "feat: 새로운 기능 추가"

# 원격 저장소에 푸시
git push origin main
```

### 브랜치 관리
```bash
# 새 브랜치 생성 및 전환
git checkout -b feature/new-feature

# 브랜치 병합
git checkout main
git merge feature/new-feature
```



**AppMart와 함께 당신의 아이디어를 현실로 만들고, 세상과 공유하세요!**

*Last updated: 2025-08-04
