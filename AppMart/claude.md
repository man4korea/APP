# Claude Project Context - AppMart

## 핵심 참조 파일 (필요시 읽기)
- `SHRIMP_Tasks.md` - 전체 작업 계획 및 MVP 구현 가이드
- `app_mart_prd.md` - 제품 요구사항 명세서 (PRD)
- `README.md` - 배포 프로세스 및 환경 설정

## 프로젝트 기본 정보

**AppMart** - AI 기반 웹앱 마켓플레이스

### 🎯 현재 상태
- **개발 환경**: `C:\xampp\htdocs\AppMart` (XAMPP + PHP 8.x + MySQL)
- **테스트 URL**: http://localhost:8080
- **프로덕션 URL**: http://appmart.dothome.co.kr
- **개발 단계**: MVP 기반 구축 완료, 기능 확장 진행 중

### 🛠 기술 스택
- **Backend**: PHP 8.4 + MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Server**: Apache 2.4
- **Development**: XAMPP (Windows 로컬 개발)
- **Production**: Dothome 웹호스팅

### 🔐 환경 설정
- `.env` 파일로 환경별 설정 관리
- 개발: `APP_ENV=development`, `APP_DEBUG=true`
- 프로덕션: `APP_ENV=production`, `APP_DEBUG=false`

### 📋 현재 워크플로 (실사용자 없음)
1. 로컬 개발 → 배포 (`X:\html`) → 프로덕션 테스트 → Git 커밋

### 🎨 코딩 표준
```php
// C:\xampp\htdocs\AppMart\[하위경로]\[파일명]
// Create at YYMMDDhhmm Ver1.00
```

---
*Claude Context Document v1.0*