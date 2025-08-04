# AppMart PRD (Product Requirements Document)

## 프로젝트명: AppMart

---

## 📌 1. 개요 (Overview)

**AppMart**는 사용자가 개발한 AI·웹 연동 웹앱을 등록·공유·판매 할 수 있고, 개발 의뢰도 할 수 있는 **AI 웹앱 마켓 플랫폼**입니다.  
Firebase, MySQL, Supabase, Excel, Google Sheets 등 다양한 기술 기반의 앱을 수용하며,  
앱 제작자와 사용자 간 연결을 통해 비즈니스 가치 창출을 도와드립니다.

---

## 🌟 2. 목표 (Goals)

- **개발자**: 다양한 기술 스택 기반 앱을 등록, 수익화
- **사용자**: 원하는 기능의 웹앱을 탐색, 다운로드 또는 요청 가능
- **uad00ub9acuc790**: 등록된 앱의 품질 및 안정성 관리, 중계 수수료 운영

---

## 👥 3. 사용자 유형 (User Types)

| 유형 | 설명 |
|------|------|
| **일반 사용자 (User)** | 앱 탐색 및 구매, 요청 기능 사용 가능 |
| **개발자 (Developer)** | 앱 등록, 가격 책정, 수익 확인 가능 |
| **관리자 (Admin)** | 전체 관리 기능 (검토, 승인, 차단, 요청 매칭 등) |

---

## 🧩 4. 주요 기능 (Core Features)

### 🔹 사용자 기능
- 이메일 기반 회원가입/로그인
- 웹앱 목록 보기 / 검색 / 필터링
- 웹앱 상세 정보 보기
- 유료 앱 결제 및 다운로드
- 개발 요청 게시판 작성 및 댓글 가능

### 🔹 개발자 기능
- 앱 등록 (이름, 설명, 기술스택, DB, 파일/링크, 썸네일, 가격 등)
- 등록 앱 관리 (수정/삭제/통계)
- 요청 게시판에서 프로젝트 수주  \uubc0f 제안 가능

### 🔹 관리자 기능
- 앱 등록 승인/반려
- 사용자 관리 (활동 모니터링, 신고 처리)
- 결제 중계 수수료 관리 및 정산
- 요청 게시판 매칭 조정
- 태그 및 카테고리 관리

---

## 📂 5. 데이터베이스 스키마 (MySQL)

### 📌 `users`
| 필드 | 타입 | 설명 |
|----------|-------|--------|
| id | INT | PK, AUTO_INCREMENT |
| email | VARCHAR(255) | 고유 이메일 |
| password_hash | TEXT | 암호화된 비밀번호 |
| nickname | VARCHAR(100) | 별칭 |
| role | ENUM('user', 'developer', 'admin') | 역할 |
| created_at | DATETIME | 가입일 |

### 📌 `apps`
| 필드 | 타입 | 설명 |
|--------|------|--------|
| id | INT | PK |
| title | VARCHAR(255) | 앱 이름 |
| description | TEXT | 설명 |
| tech_stack | TEXT | 기술스택 (json 또는 csv) |
| db_type | VARCHAR(100) | 사용 DB |
| file_url | TEXT | 파일 또는 링크 |
| thumbnail | TEXT | 썸네일 |
| price | INT | 가격 |
| status | ENUM('pending','approved','rejected') | 상태 |
| owner_id | INT | FK: users.id |
| created_at | DATETIME | 등록일 |

### 📌 `purchases`
| 필드 | 타입 | 설명 |
|----------|------|--------|
| id | INT | PK |
| user_id | INT | 구매자 FK |
| app_id | INT | 앱 FK |
| price | INT | 결제 금액 |
| purchased_at | DATETIME | 구매 시간 |

### 📌 `requests`
| 필드 | 타입 | 설명 |
|-----------|------|--------|
| id | INT | PK |
| user_id | INT | 요청자 FK |
| title | VARCHAR(255) | 요청 제목 |
| description | TEXT | 상세 요청 |
| matched_developer_id | INT | 수주한 개발자 FK |
| status | ENUM('open','matched','completed') | 진행상태 |
| created_at | DATETIME | 작성일 |

---

## 💻 6. 시스템 아키텍처 (1차 MVP)

- **호스팅**: [DotHome 500MB 웹호스팅]
- **DB**: MySQL (UTF8MB4)
- **프론트엔드**: PHP + HTML + CSS (Bootstrap 또는 Tailwind)
- **파일 저장소**: DotHome 또는 Google Drive 링크
- **보안**: PHP session 기반 인증, admin 페이지 분류

---

## ⚖️ 7. MVP 우선순위 개발 항목

- 회원가입 / 로그인
- 앱 등록 / 조회 / 상세보기
- 검색 / 필터링 (기술스택 / DB 기준)
- 무료 앱 다운로드 기능
- 관리자 검토 시스템 (승인/반려)

---

## 💰 8. 수익 목록

- 유료 앱 판매 시 중계 수수료 (예: 20%)
- 프리미엄 노출 (유료 광고 슬롯)
- 개발 요청 중계 수수료 (수주한 개발자에게서)

---

## 📆 9. 프로젝트 일정 (예시)

| 단계 | 내용 | 기간 |
|------|------|------|
| 기획/설계 | 명칭 선정, PRD 작성 | D+0 ~ D+3 |
| MVP 개발 | 회원가입, 앱 등록/조회 | D+4 ~ D+14 |
| 테스트 배포 | DotHome에서 개발 검수 | D+15 ~ D+17 |
| 피드백 | 초기 사용자 테스트 | D+18 ~ D+20 |
| 정식 공개 | 1차 버전 로칫 | D+21 |

---

## 🚀 AppMart 슬로간 / 태그라인 제안

- **"Build, Share, and Sell Your AI-Powered Tools"**
- **"앱을 만들고, 나누고, 수익화하세요."**
- **"당신의 웹앱, 세상과 연결되는 시작점"**
- **"AppMart – Where smart apps meet the right people"**

---

🤔 당신은 이제 어떻게 진행하고 싶으시겠어요?
- DB SQL 생성으로 가일까요?
- MVP 개발 초기 파일 구조에서 처음으로 가는 건가요?

아래 행동을 결정해 주시거나, 추가적인 사항이 있으면 말씀해주세요. 😊

