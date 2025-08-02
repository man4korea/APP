<!-- 📁 C:\xampp\htdocs\BPM\BPM_CLAUDE_DESKTOP_GUIDE.md -->
<!-- Create at 2508022005 Ver1.00 -->

# 🖥️ Claude Desktop 전용 지침서

## 📋 지침서 참조 방법

### 지침서 구조
```
📚 공통 지침서: BPM_PROJECT_GUIDE.md (기본 참조)
🛠️ Claude Code: BPM_CLAUDE_CODE_GUIDE.md (개발 실행)
🖥️ Claude Desktop: BPM_CLAUDE_DESKTOP_GUIDE.md (본 파일)
```

### 역할 분담
- **Claude Desktop**: 기획, 설계, 리뷰, 문서화, 복잡한 논의, 사용자 요청시 Cluade Code 역할 수행
- **Claude Code**: 실제 개발, 테스트, 배포, Git 관리

---

## 🎯 Claude Desktop 주요 역할

### 1. 프로젝트 기획 및 설계
- 비즈니스 요구사항 분석
- 시스템 아키텍처 설계
- 데이터베이스 스키마 설계
- API 설계 및 문서화

### 2. 코드 리뷰 및 품질 관리
- Claude Code 개발 결과 검토
- 코드 품질 및 표준 준수 확인
- 보안 취약점 점검
- 성능 최적화 제안

### 3. 문서화 및 가이드 작성
- 사용자 매뉴얼 작성
- API 문서 작성
- 개발 가이드 업데이트
- 프로젝트 진행 보고서

### 4. 복잡한 문제 해결
- 아키텍처 레벨 이슈 분석
- 성능 병목점 식별
- 확장성 고려사항 검토
- 보안 정책 수립

### 5. 사용자 요청시 Clode Code 역할 수행
---

## 📊 작업 관리 연동

### SHRIMP Tasks 모니터링
Claude Desktop에서는 직접 SHRIMP를 조작하지 않고, 다음과 같이 연동합니다:

```markdown
## 현재 작업 현황 확인 요청
"Claude Code에서 현재 SHRIMP 작업 현황을 확인해주세요"

## 새로운 작업 추가 요청  
"다음 작업을 SHRIMP에 추가해주세요:
- 작업명: [작업명]
- 설명: [상세설명] 
- 의존성: [선행작업]"

## 작업 우선순위 조정 요청
"작업 우선순위를 다음과 같이 조정해주세요: [우선순위 목록]"
```

---

## 🏗️ 아키텍처 설계 가이드

### 시스템 아키텍처
```
Frontend (Web UI)
├── 🌈 모듈별 무지개 테마
├── 반응형 디자인
└── AJAX 통신

Backend (PHP/Node.js)
├── RESTful API
├── 인증/권한 시스템
├── 비즈니스 로직
└── 데이터 접근 계층

Database (MySQL)
├── 멀티테넌트 설계
├── 권한 관리 테이블
├── 감사 로그
└── 성능 최적화

Infrastructure
├── XAMPP (개발)
├── OneDrive (백업)
├── 웹서버 (운영)
└── Git (버전관리)
---

*Last updated: 2025-08-02 20:05 JST*  
*Version: 1.00 - Claude Desktop 전용*