# 🏢 BPR Hub - Business Process Reengineering Management System

## 📋 프로젝트 개요

**BPR Hub**는 기업의 비즈니스 프로세스 재설계(Business Process Reengineering)를 지원하는 멀티테넌트 웹 애플리케이션입니다. 
회사별 독립적인 데이터 관리와 본점-지점 구조를 지원하여, 조직의 업무 프로세스를 체계적으로 관리할 수 있습니다.

### 🌟 주요 특징

- **멀티테넌트 아키텍처**: 회사별 완전한 데이터 분리
- **본점-지점 구조**: 계층적 조직 관리 및 통합 승인 시스템
- **4단계 권한 관리**: 설립자 → 관리자 → 프로세스오너 → 구성원
- **Task 중심 관리**: 업무 검색, 담당자 파악, 프로세스 매핑
- **실시간 협업**: 권한 기반 프로세스 편집 및 승인 워크플로우

---

## 🚀 현재 개발 상황 (2024.07.26 기준)

### ✅ 완료된 기능 (Phase 0)

#### 1. **아키텍처 설계** (16시간)
- 멀티테넌트 데이터베이스 스키마 완성
- 회사별 데이터 분리 구조 설계
- 권한 관리 시스템 설계
- 본점-지점 관계 모델링

#### 2. **회사 등록 시스템** (20시간)
- 확장된 회사 정보 관리 (대표자, 관리자, 주소, 법인설립일 등)
- 본점-지점 구조 지원
- 설립자 계정 자동 생성
- 기본 부서 구조 옵션

#### 3. **지점 통합 승인 시스템** (24시간) 
- 본점의 지점 통합 요청 기능
- 이메일 기반 지점 관리자 승인/거부
- 7일 자동 만료 시스템
- 통합 이력 관리 및 데이터 마이그레이션

#### 4. **테스트 환경** (16시간)
- 회사 등록 웹 인터페이스
- 회사 대시보드 UI
- 지점 통합 승인 페이지
- 반응형 디자인 및 실시간 검증

**완료된 기능 총 소요시간**: 76시간

---

## 🎯 향후 개발 계획

### 📅 Phase 1: 핵심 백엔드 API 개발 (7일, 56시간) - HIGH 우선순위
- Node.js/Express + TypeScript 서버 구축
- MySQL 데이터베이스 연동 및 스키마 적용
- JWT 기반 인증 시스템 구현
- 멀티테넌트 미들웨어 개발
- 회사/사용자 관리 REST API
- Swagger 기반 API 문서화

### 📅 Phase 2: 구성원 관리 시스템 (5일, 40시간) - HIGH 우선순위
- 이메일 기반 구성원 초대 API
- 초대 링크 생성 및 검증 시스템
- 구성원 역할 할당 및 상태 관리
- 구성원 CRUD 및 검색 기능

### 📅 Phase 3: Task 관리 및 검색 (6일, 48시간) - HIGH 우선순위
- Task CRUD API 개발
- 키워드 기반 검색 엔진 구현
- 담당자 자동 파악 시스템
- Task 필터링 및 통계 리포트

### 📅 Phase 4: Process Owner 권한 시스템 (4일, 32시간) - MEDIUM 우선순위
- 프로세스별 권한 정의 및 검증
- 프로세스 승인 워크플로우
- 변경 이력 관리 시스템

### 📅 Phase 5: 조직도 관리 (5일, 40시간) - MEDIUM 우선순위
- 부서 CRUD API 및 계층 구조 관리
- 조직도 시각화 API
- 조직 변경 승인 시스템

### 📅 Phase 6: 프론트엔드 UI (8일, 64시간) - HIGH 우선순위
- React + TypeScript 환경 구축
- 인증 및 회사 관리 UI
- 구성원 관리 인터페이스
- Task 관리 및 검색 UI
- 조직도 시각화 컴포넌트
- 반응형 대시보드

### 📅 Phase 7: 배포 환경 (4일, 32시간) - MEDIUM 우선순위
- Docker 컨테이너화
- CI/CD 파이프라인 구축
- 프로덕션 데이터베이스 설정
- 모니터링 시스템

### 📅 Phase 8: 테스트 및 QA (5일, 40시간) - MEDIUM 우선순위
- Unit/Integration/E2E 테스트
- 성능 테스트 및 최적화
- 보안 테스트 및 취약점 점검
- 코드 품질 개선

**예상 총 개발 기간**: 약 50일 (352시간)

---

## 🏗️ 기술 스택

### 백엔드
- **Runtime**: Node.js 18+
- **Framework**: Express.js with TypeScript
- **Database**: MySQL 8.0+ 
- **Authentication**: JWT + bcrypt
- **Email**: NodeMailer
- **Validation**: Joi/Yup
- **Testing**: Jest + Supertest

### 프론트엔드
- **Framework**: React 18+ with TypeScript
- **Build Tool**: Vite
- **State Management**: Zustand/Redux Toolkit
- **UI Library**: Tailwind CSS + Headless UI
- **Charts**: D3.js + Chart.js
- **Testing**: React Testing Library + Jest

### DevOps
- **Containerization**: Docker + Docker Compose
- **CI/CD**: GitHub Actions
- **Monitoring**: Prometheus + Grafana
- **Logging**: Winston + ELK Stack

---

## 📁 프로젝트 구조

```
bpr/
├── README.md                      # 프로젝트 개요 (이 파일)
├── DEVELOPMENT_LOG.md             # 상세 개발 일지
├── CLAUDE.md                      # 프로젝트별 개발 지침
│
├── database/                      # 데이터베이스 관련
│   ├── multitenant_schema.sql     # 멀티테넌트 스키마
│   └── schema.sql                 # 기존 스키마 (참고용)
│
├── backend/                       # 백엔드 서비스
│   └── services/
│       ├── CompanyRegistrationService.js
│       └── BranchIntegrationService.js
│
├── src/                          # 메인 소스코드
│   └── frontend/
│       └── test/                 # 테스트용 웹 인터페이스
│           ├── index.html        # 메인 테스트 페이지
│           ├── company-registration.html
│           ├── company-dashboard.html
│           └── branch-integration.html
│
└── SHRIMP/                       # Shrimp Task Manager
    ├── tasks.json                # 업데이트된 작업 계획
    └── updated_tasks.json        # 상세 태스크 정의
```

---

## 🔧 로컬 개발 환경 설정

### 1. 사전 요구사항
- Node.js 18.0 이상
- MySQL 8.0 이상
- Git

### 2. 프로젝트 클론
```bash
git clone https://github.com/man4korea/APP.git
cd APP/bpr
```

### 3. 의존성 설치 (향후 예정)
```bash
npm install
```

### 4. 환경 변수 설정 (향후 예정)
```bash
cp .env.example .env
# .env 파일에서 데이터베이스 및 기타 설정 구성
```

### 5. 데이터베이스 설정
```bash
mysql -u root -p < database/multitenant_schema.sql
```

### 6. 서버 실행 (향후 예정)
```bash
npm run dev
```

---

## 🧪 테스트 방법

### 현재 테스트 가능한 기능
1. **테스트 웹 인터페이스 확인**
   ```bash
   # src/frontend/test/index.html을 웹브라우저에서 열기
   ```

2. **회사 등록 플로우 테스트**
   - company-registration.html에서 회사 정보 입력 테스트

3. **대시보드 UI 확인**
   - company-dashboard.html에서 UI 컴포넌트 확인

4. **지점 통합 승인 UI 테스트**
   - branch-integration.html에서 승인 프로세스 UI 확인

### 향후 테스트 계획
- API 엔드포인트 테스트 (Postman/Thunder Client)
- 프론트엔드-백엔드 통합 테스트
- 멀티테넌트 데이터 분리 검증
- 성능 및 보안 테스트

---

## 📖 문서

- [개발 일지](DEVELOPMENT_LOG.md) - 일자별 상세 개발 진행 상황
- [프로젝트 지침](CLAUDE.md) - 개발 원칙 및 코딩 규칙
- [작업 계획](SHRIMP/tasks.json) - Shrimp Task Manager 기반 상세 계획

---

## 🤝 기여 방법

### 브랜치 전략
- `main`: 프로덕션 준비 완료 코드
- `develop`: 개발 브랜치  
- `feature/*`: 기능 개발 브랜치
- `hotfix/*`: 긴급 수정 브랜치

### 코딩 컨벤션
- **변수명**: camelCase
- **컴포넌트명**: PascalCase  
- **상수**: UPPER_SNAKE_CASE
- **파일명**: kebab-case
- **함수**: 동사+명사 조합으로 명확한 의도 표현

### 품질 관리
- Code Review 필수
- 테스트 커버리지 80% 이상 유지
- ESLint, Prettier 규칙 준수
- TypeScript 엄격 모드 적용

---

## 📄 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다. 자세한 내용은 [LICENSE](LICENSE) 파일을 참조하세요.

---

## 👥 팀

- **개발자**: man4korea
- **프로젝트 시작**: 2024년 7월 26일
- **개발 환경**: Windows 11, OneDrive 동기화

---

## 📞 연락처

프로젝트 관련 문의사항이나 기여를 원하시는 경우:

- **GitHub**: [https://github.com/man4korea/APP](https://github.com/man4korea/APP)
- **Issues**: GitHub Issues를 통한 버그 리포트 및 기능 요청

---

**📍 마지막 업데이트**: 2024년 7월 26일  
**📍 다음 마일스톤**: Phase 1 백엔드 API 개발 시작