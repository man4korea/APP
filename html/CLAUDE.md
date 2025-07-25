# CorpEasy ERP 프로젝트 설정

> 전역 지침은 `@../CLAUDE.md` 참조

## 목차
1. [프로젝트 정보](#프로젝트-정보)
2. [프로젝트 구조](#프로젝트-구조)
3. [CorpEasy 개발 지침](#corpeasy-개발-지침)
4. [워크플로우](#워크플로우)

---

## 프로젝트 정보

### CorpEasy ERP 시스템
- **프로젝트명**: CorpEasy ERP
- **개발 디렉토리**: `C:\Users\man4k\OneDrive\문서\APP\html`
- **SHRIMP 데이터 폴더**: `C:\Users\man4k\OneDrive\문서\APP\html\SHRIMP`
- **배포 URL**: http://kdverp.dothome.co.kr
- **로그인**: `man4korea@gmail.com` / `dmlwjdqn@Wkd24`

---

## 프로젝트 구조

### CorpEasy ERP 디렉토리 구조
```
C:\APP\html/                    # 🏠 Development directory (default path)
│
├── 📄 index.html               # Main homepage
├── 📄 dashboard.html           # Dashboard main page
├── 📄 login.html               # Login page
│
├── 🎨 css/                     # CSS files (modular structure)
│   ├── 📄 kdvstyles.css        # 🔧 Core design system
│   ├── 📂 components/          # 🧩 Component-specific CSS
│   │   ├── 📄 header.css       # Top header/topbar component
│   │   ├── 📄 sidebar.css      # Sidebar navigation component
│   │   ├── 📄 dropdown.css     # Dropdown menu component
│   │   ├── 📄 modal.css        # Modal dialog component
│   │   ├── 📄 cogy-chat.css    # COGY AI chat component
│   │   ├── 📄 buttons.css      # Button components
│   │   ├── 📄 forms.css        # Form components
│   │   ├── 📄 cards.css        # Card components
│   │   ├── 📄 tables.css       # Table components
│   │   ├── 📄 alerts.css       # Alerts and warnings
│   │   └── 📄 badges.css       # Badge components
│   │
│   └── 📂 pages/               # 📄 Page-specific CSS files
│       ├── 📄 dashboard.css    # Dashboard (stats, charts, etc.)
│       ├── 📄 login.css        # Login page
│       ├── 📄 employees.css    # Employee management page
│       ├── 📄 attendance.css   # Attendance management page
│       ├── 📄 payroll.css      # Payroll management page
│       ├── 📄 accounting.css   # Accounting management page
│       ├── 📄 reports.css      # Reports page
│       ├── 📄 settings.css     # Settings page
│       └── 📄 admin.css        # Admin page
│
├── 🔧 js/                      # JavaScript files
│   ├── 📄 app.js               # Main application logic
│   ├── 📄 sidebar.js           # Sidebar component logic
│   ├── 📄 env-config.js        # Environment configuration
│   │
│   ├── 📂 components/          # Reusable components
│   │   ├── 📄 modal.js         # Modal logic
│   │   ├── 📄 toast.js         # Toast notification logic
│   │   ├── 📄 cogy-chat.js     # COGY chat logic
│   │   └── 📄 header.js        # Header logic
│   │
│   └── 📂 modules/             # Feature-specific modules
│       ├── 📄 auth.js          # Authentication management
│       ├── 📄 api.js           # API communication
│       ├── 📄 utils.js         # Utility functions
│       └── 📄 firebase.js      # Firebase integration
│
├── 📂 pages/                   # HTML pages
│   ├── 📂 hr/                  # 👥 HR management pages
│   ├── 📂 finance/             # 💰 Financial management pages
│   ├── 📂 settings/            # ⚙️ Settings pages
│   └── 📂 admin/               # 🔐 Admin pages
│
├── 📂 assets/                  # Static assets
│   ├── 📂 images/              # Image files
│   ├── 📂 icons/               # Icon files
│   └── 📂 fonts/               # Font files
│
├── 📂 data/                    # Data files
│   ├── 📄 menu.json            # Menu structure data
│   └── 📄 config.json          # Configuration data
│
└── 📂 SHRIMP/                  # 🦐 Task management system
    ├── 📄 tasks.json           # Task list
    └── 📄 progress.json        # Progress tracking
```

---

## CorpEasy 개발 지침

### CorpEasy 핵심 원칙

#### 기술 스택
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: Firebase (Authentication, Firestore)
- **UI Framework**: 커스텀 CSS 컴포넌트 시스템
- **Icons**: Font Awesome, 커스텀 SVG

#### 아키텍처 원칙
- **모듈화**: CSS/JS 파일을 기능별로 분리
- **컴포넌트 기반**: 재사용 가능한 UI 컴포넌트
- **반응형 디자인**: 모바일 우선 설계
- **접근성**: WCAG 2.1 AA 준수

#### 파일 명명 규칙
- **HTML**: `kebab-case.html` (예: `employee-list.html`)
- **CSS**: `kebab-case.css` (예: `sidebar.css`)
- **JavaScript**: `camelCase.js` (예: `employeeManager.js`)
- **이미지**: `descriptive-name.ext` (예: `company-logo.png`)

### 개발 워크플로우

---

## 워크플로우

### 개발 → 배포 프로세스

1. **로컬 개발**: `C:\Users\man4k\OneDrive\문서\APP\html`에서 개발
2. **파일 동기화**: 
   ```bash
   robocopy "C:\APP\html" "Z:\html" /E /XO /XD ".cursor" ".git" "node_modules" "docs" "test" "SHRIMP" /R:3 /W:10
   ```
3. **원격 테스트**: `http://kdverp.dothome.co.kr`에서 확인
   - **로그인**: `man4korea@gmail.com` / `dmlwjdqn@Wkd24`
4. **Git 커밋**: `git-mcp-kdv-erp.prompt.md` 참조
5. **Task 업데이트**: `SHRIMP/tasks.json` 상태 갱신

### 환경 변수 설정

```powershell
# CorpEasy 프로젝트 작업 시
$env:DATA_DIR="C:\Users\man4k\OneDrive\문서\APP\html\SHRIMP"
```

### 데이터 플로우

```
Local Development → OneDrive Sync → Z Drive → Remote Server
C:\APP\html     →  OneDrive      →  Z:\html →  kdverp.dothome.co.kr
```