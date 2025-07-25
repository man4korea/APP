# Calculator 웹앱 프로젝트 설정

> 전역 지침은 `@../CLAUDE.md` 참조

## 목차
1. [프로젝트 정보](#프로젝트-정보)
2. [프로젝트 구조](#프로젝트-구조)
3. [Calculator 개발 지침](#calculator-개발-지침)
4. [워크플로우](#워크플로우)

---

## 프로젝트 정보

### Calculator 웹앱
- **프로젝트명**: Electronic Calculator Web App
- **개발 디렉토리**: `C:\Users\man4k\OneDrive\문서\APP\calculator`
- **SHRIMP 데이터 폴더**: `C:\Users\man4k\OneDrive\문서\APP\calculator\SHRIMP`
- **기술스택**: HTML5, CSS3, Vanilla JavaScript
- **타겟**: 데스크톱/모바일 브라우저 (반응형)

---

## 프로젝트 구조

### Calculator 디렉토리 구조
```
C:\APP\calculator/              # 🧮 Calculator 프로젝트 루트
│
├── 📄 index.html              # 메인 페이지
├── 📄 CLAUDE.md               # 프로젝트 지침 (이 파일)
│
├── 🎨 css/                    # 스타일시트
│   ├── 📄 calculator.css      # 메인 계산기 스타일
│   ├── 📄 responsive.css      # 반응형 디자인
│   └── 📄 theme.css          # 테마/색상 시스템
│
├── 🔧 js/                     # JavaScript 모듈
│   ├── 📄 calculator.js       # 메인 계산기 클래스
│   ├── 📄 display.js          # 디스플레이 관리
│   ├── 📄 operations.js       # 수학 연산 로직
│   ├── 📄 keyboard.js         # 키보드 이벤트 처리
│   └── 📄 utils.js           # 유틸리티 함수
│
└── 📂 SHRIMP/                 # 🦐 Task management system
    ├── 📄 tasks.json          # Task list
    └── 📄 progress.json       # Progress tracking
```

---

## Calculator 개발 지침

### 핵심 원칙

#### 기술 스택
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (ES6+)
- **Layout**: CSS Grid/Flexbox
- **No Dependencies**: 외부 라이브러리 사용 금지
- **Browser Support**: 모던 브라우저 (Chrome, Firefox, Safari, Edge)

#### 아키텍처 원칙
- **모듈화**: 기능별 JavaScript 파일 분리
- **컴포넌트 기반**: 재사용 가능한 UI 컴포넌트
- **반응형 디자인**: Mobile-first approach
- **접근성**: WCAG 2.1 AA 준수
- **성능**: 최적화된 코드, 빠른 응답성

#### 파일 명명 규칙
- **HTML**: `index.html`
- **CSS**: `kebab-case.css` (예: `calculator.css`)
- **JavaScript**: `camelCase.js` (예: `calculator.js`)
- **클래스명**: `PascalCase` (예: `Calculator`)
- **함수명**: `camelCase` (예: `calculateResult`)

### 기능 요구사항

#### 필수 기능
1. **기본 연산**: +, -, *, / (사칙연산)
2. **숫자 입력**: 0-9 숫자 버튼
3. **소수점**: 소수점 계산 지원
4. **등호**: = 버튼으로 계산 실행
5. **클리어**: C (Clear), AC (All Clear)
6. **백스페이스**: 마지막 입력 삭제
7. **키보드 지원**: 숫자키, 연산키, Enter, Escape 등

#### 고급 기능 (선택사항)
1. **메모리 기능**: M+, M-, MR, MC
2. **과학 계산**: √, x², %
3. **부호 변경**: +/- 토글
4. **계산 히스토리**: 이전 계산 결과 표시

#### UI/UX 요구사항
1. **직관적 레이아웃**: 일반 계산기와 유사한 배치
2. **시각적 피드백**: 버튼 hover/active 효과
3. **반응형 디자인**: 모바일/태블릿/데스크톱 최적화
4. **접근성**: 스크린 리더 지원, 키보드 네비게이션
5. **에러 처리**: 0으로 나누기, 오버플로우 등

---

## 워크플로우

### 개발 프로세스

1. **로컬 개발**: `C:\Users\man4k\OneDrive\문서\APP\calculator`에서 개발
2. **실시간 테스트**: Live Server 또는 로컬 파일로 테스트
3. **브라우저 호환성**: 주요 브라우저에서 테스트
4. **모바일 테스트**: 반응형 디자인 확인
5. **접근성 검증**: 키보드 네비게이션, 스크린 리더 테스트

### 환경 변수 설정

```powershell
# Calculator 프로젝트 작업 시
$env:DATA_DIR="C:\Users\man4k\OneDrive\문서\APP\calculator\SHRIMP"
```

### 개발 순서

1. **HTML 구조**: 기본 레이아웃과 버튼 배치
2. **CSS 스타일링**: 디자인 시스템 구축
3. **JavaScript 로직**: 계산 엔진 구현
4. **이벤트 처리**: 클릭/키보드 이벤트
5. **반응형 최적화**: 모바일 디자인 조정
6. **접근성 개선**: ARIA 라벨, 키보드 지원
7. **테스트 및 디버깅**: 엣지 케이스 처리

### 테스트 케이스

- 기본 사칙연산 정확성
- 소수점 계산 정밀도
- 연속 계산 로직
- 0으로 나누기 예외 처리
- 큰 수/작은 수 오버플로우
- 키보드 입력 호환성
- 모바일 터치 이벤트