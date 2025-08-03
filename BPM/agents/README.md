# BPM 에이전트 시스템

## 개요
BPM 프로젝트의 AI 에이전트 관리 시스템입니다. 다양한 전문 에이전트들을 통해 개발 효율성을 향상시킵니다.

## 에이전트 목록

### 1. Code Simplifier
- **파일**: `code-simplifier-system-prompt.md`, `code-simplifier-config.json`
- **목적**: 복잡한 코드를 초보자도 이해할 수 있게 단순화
- **기능**: 
  - 복잡한 로직 단계별 분해
  - 직관적인 변수명/함수명 제안
  - 상세한 주석 추가
  - 초보자를 위한 학습 포인트 제시

### 2. Security Reviewer
- **파일**: `security-reviewer-system-prompt.md`, `security-reviewer-config.json`
- **목적**: 프로젝트의 보안 취약점 분석 및 데이터 보호 강화
- **기능**:
  - 인증/인가 시스템 보안 검토
  - 데이터 보호 및 암호화 검증
  - 환경 설정 보안 점검
  - 코드 보안 취약점 분석 (SQL 인젝션, XSS, CSRF 등)
  - 위험도별 분류 및 개선 방안 제시

### 3. Code Reviewer
- **파일**: `code-reviewer-system-prompt.md`, `code-reviewer-config.json`
- **목적**: 코드 품질, 성능, 유지보수성을 종합적으로 검토
- **기능**:
  - 100점 만점 점수 시스템으로 코드 품질 평가
  - 가독성, 성능, 유지보수성, 표준 준수 4개 카테고리 분석
  - 우선순위별 개선 방안 제시 (High/Medium/Low)
  - 리팩토링된 코드 예시 제공
  - 다양한 프로그래밍 언어 지원

### 4. Tech Lead
- **파일**: `tech-lead-system-prompt.md`, `tech-lead-config.json`
- **목적**: 전체 시스템 설계와 기술적 의사결정을 담당하는 기술 리더
- **기능**:
  - 프로젝트 규모별 아키텍처 패턴 추천 (소/중/대규모)
  - 기술 스택 선택 및 근거 제시
  - 단계별 구현 로드맵 수립 (Foundation → Core → Optimization)
  - 위험 요소 분석 및 대응 방안 제시
  - 확장성, 성능, 비용 효율성 종합 고려

### 5. UX Reviewer
- **파일**: `ux-reviewer-system-prompt.md`, `ux-reviewer-config.json`
- **목적**: 사용자 경험(UX/UI) 전문가로 사용자 중심의 인터페이스 개선
- **기능**:
  - Nielsen 휴리스틱 기반 사용성 분석 (10가지 원칙)
  - WCAG 2.1 접근성 준수 검사 및 개선 방안
  - 반응형 디자인 및 다중 디바이스 최적화 검토
  - 사용자 여정 매핑 및 마찰 지점 식별
  - A/B 테스트 제안 및 사용자 테스트 시나리오 설계

## 에이전트 사용법

### Claude 웹 인터페이스에서 에이전트 생성
1. Claude 웹사이트 접속
2. "Create new agent" 클릭
3. 해당 에이전트의 `system-prompt.md` 내용을 복사하여 붙여넣기
4. 필요한 도구들 활성화 (config.json 참조)
5. 테스트 후 저장

### 로컬에서 에이전트 테스트
```bash
# 에이전트 디렉토리로 이동
cd C:\xampp\htdocs\BPM\agents

# 설정 파일 확인
cat code-simplifier-config.json

# 시스템 프롬프트 확인
cat code-simplifier-system-prompt.md
```

## 에이전트 개발 가이드

### 새로운 에이전트 추가시
1. `[agent-name]-system-prompt.md` 파일 생성
2. `[agent-name]-config.json` 설정 파일 생성
3. 이 README.md에 에이전트 정보 추가
4. 테스트 후 Git 커밋

### 파일 명명 규칙
- 시스템 프롬프트: `[agent-name]-system-prompt.md`
- 설정 파일: `[agent-name]-config.json`
- 테스트 파일: `[agent-name]-test.js` (선택사항)

## 향후 계획
- [x] ~~Security Analyzer 에이전트~~ ✅ **Security Reviewer로 완성**
- [x] ~~Code Reviewer 에이전트~~ ✅ **Code Reviewer로 완성**
- [x] ~~Tech Lead 에이전트~~ ✅ **Tech Lead로 완성**
- [x] ~~UX Reviewer 에이전트~~ ✅ **UX Reviewer로 완성**
- [ ] Documentation Generator 에이전트  
- [ ] Performance Optimizer 에이전트
- [ ] API Documentation Generator 에이전트
- [ ] Database Schema Reviewer 에이전트
- [ ] Test Case Generator 에이전트
- [ ] Deployment Checker 에이전트