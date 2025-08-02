<!-- 📁 C:\xampp\htdocs\BPM\README.md -->
<!-- Create at 2508021941 Ver1.01 -->

# BPM - Total Business Process Management

🌈 **멀티테넌트 10개 모듈 통합 BPM SaaS 시스템**

## 📋 프로젝트 개요

BPR(Business Process Reengineering) 기반의 차세대 업무 프로세스 관리 시스템으로, 10개의 무지개 색상 테마 모듈을 통해 조직의 모든 업무를 통합 관리할 수 있는 SaaS 플랫폼입니다.

## 🌟 주요 특징

- **🔴 조직관리** - 회사/부서 계층구조, 본점-지점 통합
- **🟠 구성원관리** - 사용자 초대, 권한 관리, 인사이동
- **🟡 Task관리** - 업무 분류, 시간 추적, 효율성 분석
- **🟢 문서관리** - 파일 관리, 버전 관리, 템플릿 시스템
- **🔵 Process Map관리** - 시각적 프로세스 매핑
- **🟣 업무Flow관리** - BPR/BPM 워크플로우
- **🟤 직무분석** - 업무시간/효율성 분석
- **⚫ 업무혁신관리** - 혁신 프로젝트 관리
- **⚪ 인사관리** - 인사평가/급여 관리
- **🩷 목표성과관리** - KPI/OKR 관리

## 🏗️ 시스템 아키텍처

### 기술 스택
- **백엔드**: PHP 8.0+, MySQL 8.0+
- **프론트엔드**: HTML5, CSS3, JavaScript ES6+
- **테스트**: Playwright/Puppeteer
- **서버**: Apache (XAMPP)
- **배포**: 자동화 스크립트 (OneDrive + 웹서버)

### 폴더 구조
```
BPM/
├── core/                   # 통합 대시보드 및 메인 시스템
├── modules/                # 10개 모듈별 독립 실행 코드
│   ├── organization/       # 🔴 조직관리 (빨강 테마)
│   ├── members/            # 🟠 구성원관리 (주황 테마)
│   ├── tasks/              # 🟡 Task관리 (노랑 테마)
│   ├── documents/          # 🟢 문서관리 (초록 테마)
│   ├── processes/          # 🔵 Process Map관리 (파랑 테마)
│   ├── workflows/          # 🟣 업무Flow관리 (보라 테마)
│   └── analytics/          # 🟤 직무분석 (갈색 테마)
├── shared/                 # 공통 컴포넌트 및 유틸리티
│   ├── components/         # 재사용 가능한 UI 컴포넌트
│   └── utils/              # 공통 함수 및 헬퍼
├── assets/                 # CSS, JS, 이미지 등 정적 자원
│   ├── css/
│   │   └── themes/         # 모듈별 색상 테마
│   ├── js/
│   │   └── modules/        # 모듈별 JavaScript
│   └── images/             # 이미지 및 아이콘
├── sql/                    # 데이터베이스 스키마 및 마이그레이션
├── includes/               # PHP 설정 및 공통 함수
├── tests/                  # Playwright 자동화 테스트
│   └── screenshots/        # 테스트 스크린샷
├── scripts/                # 배포 및 유틸리티 스크립트
├── uploads/                # 사용자 업로드 파일
├── logs/                   # 시스템 로그
├── cache/                  # 캐시 저장소
├── config/                 # 설정 파일
├── docs/                   # 문서화
├── .env                    # 환경 변수 설정
├── .gitignore              # Git 무시 파일 목록
├── package.json            # Node.js 의존성 관리
├── README.md               # 프로젝트 가이드
└── SHRIMP_Tasks.md         # 작업 관리 시스템
```

## 🚀 설치 및 실행

### 요구사항
- **XAMPP**: Apache + MySQL + PHP
- **Node.js**: 16.0.0 이상 (테스트용)
- **Git**: 버전 관리 (선택사항)

### 설치 과정

1. **XAMPP 설치 및 서비스 시작**
   ```bash
   # XAMPP Control Panel에서 Apache, MySQL 시작
   ```

2. **프로젝트 클론**
   ```bash
   cd C:\xampp\htdocs\
   git clone [repository] BPM
   cd BPM
   ```

3. **의존성 설치**
   ```bash
   npm install
   npx playwright install
   ```

4. **데이터베이스 설정**
   ```bash
   # MySQL에 접속하여 스키마 실행
   mysql -u root -p < sql/schema.sql
   ```

5. **환경 설정**
   ```bash
   # .env 파일 생성 및 설정
   cp .env.example .env
   ```

## 🧪 테스트 실행

### 자동화 테스트
```bash
# 모든 모듈 테스트
npm test

# 테스트 감시 모드
npm run test:watch

# Playwright 브라우저 설치
npm run playwright:install
```

### 수동 테스트
```bash
# 로컬 서버 확인
npm run dev
# http://localhost/BPM/ 접속
```

## 🔄 배포 프로세스

### 자동 배포
```bash
# Node.js 배포 스크립트
npm run deploy

# Windows 배치 스크립트  
npm run deploy:bat
```

### 배포 단계
1. **🧪 테스트**: Playwright 자동화 테스트 실행
2. **📁 OneDrive**: `C:\Users\man4k\OneDrive\문서\APP\bpm` 백업
3. **🌐 웹서버**: `Z:\html\bpm` 배포
4. **📊 로그**: 배포 결과 기록

## 📊 작업 관리

### SHRIMP 작업 시스템
모든 개발 작업은 `SHRIMP_Tasks.md`에서 관리됩니다:

- **작업 계획** 수립 및 수정
- **진행상황** 실시간 추적 
- **Git 연동** 자동화
- **검증 기준** 체계적 관리

### 작업 상태
- ⏳ **대기중** - 의존성 작업 완료 대기
- 🟡 **진행중** - 현재 개발 진행중  
- ✅ **완료** - 검증 완료 및 배포 완료
- 🔴 **차단** - 문제 발생으로 진행 불가

## 🛠️ 개발 가이드

### 모듈 개발 원칙
1. **독립 실행**: 각 모듈은 독립적으로 실행 가능
2. **색상 테마**: 모듈별 고유 무지개 색상 적용
3. **멀티테넌트**: 회사별 완전한 데이터 분리
4. **반응형**: 모든 화면 크기 대응

### 코딩 컨벤션
- **파일명**: snake_case 사용
- **함수명**: camelCase 사용  
- **상수명**: UPPER_CASE 사용
- **주석**: 모든 함수에 한글 주석 필수

## 🔐 보안 가이드

### 데이터 보호
- **환경변수**: `.env` 파일로 중요 정보 분리
- **SQL 인젝션**: Prepared Statement 사용
- **XSS 방지**: 모든 출력 데이터 이스케이프
- **CSRF 보호**: 토큰 기반 요청 검증

### 권한 관리
- **Founder**: 시스템 전체 관리
- **Admin**: 회사 관리 권한
- **Process Owner**: 프로세스 관리 권한  
- **Member**: 기본 사용 권한

## 📈 성능 최적화

### 데이터베이스
- **인덱싱**: 자주 조회되는 필드 인덱스
- **파티셔닝**: 회사별 데이터 분할
- **캐싱**: 자주 사용되는 쿼리 결과 캐시

### 프론트엔드
- **이미지 최적화**: WebP 포맷 사용
- **CSS 압축**: 프로덕션 빌드시 압축
- **JavaScript 모듈화**: 필요한 모듈만 로드

## 🐛 문제 해결

### 자주 발생하는 문제

**Q: XAMPP 서비스가 시작되지 않음**
```bash
# 포트 충돌 확인
netstat -ano | findstr :80
netstat -ano | findstr :3306

# 방화벽 해제 또는 포트 변경
```

**Q: Playwright 테스트 실패**
```bash
# 브라우저 재설치
npx playwright install --force

# 권한 확인
npm run test -- --debug
```

**Q: 배포 스크립트 실행 오류**
```bash
# 실행 권한 확인
powershell Set-ExecutionPolicy RemoteSigned

# 경로 확인
echo %PATH%
```

## 📞 지원 및 문의

### 개발팀 연락처
- **이메일**: dev@bmp-system.com
- **이슈 트래킹**: `SHRIMP_Tasks.md` 참조
- **문서**: `CLAUDE.md` 상세 가이드

### 기여 방법
1. **Fork** 프로젝트
2. **Feature branch** 생성
3. **Commit** 변경사항
4. **Pull Request** 생성

## 📜 라이선스

MIT License - 자세한 내용은 `LICENSE` 파일 참조

---

**🎯 비전**: AI와 인간이 협력하는 차세대 BPR 플랫폼으로 모든 조직의 업무 프로세스를 혁신합니다.

*Last updated: 2025-08-02*