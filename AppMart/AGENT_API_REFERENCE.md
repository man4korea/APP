# 📚 Agent API 참조 문서

## 🎯 개요

이 문서는 AppMart의 5개 전문 Agent들이 제공하는 모든 API 함수들의 상세한 참조 가이드입니다.

---

## 🔍 Code Reviewer Agent

### `review_code`
코드 품질을 종합적으로 검토합니다.

**매개변수:**
- `code` (string, 필수): 분석할 코드
- `language` (string, 선택): 프로그래밍 언어 (기본값: 자동 감지)
- `focus` (string, 선택): 검토 포커스 (quality, performance, maintainability)

**반환값:**
```json
{
  "issues": ["발견된 문제점들"],
  "improvements": ["개선 방안들"],  
  "score": "품질 점수 (1-100)",
  "priority": ["우선순위별 수정 사항"]
}
```

**사용 예시:**
```javascript
review_code(
  code: "function test() { var x = 1; }",
  language: "javascript",
  focus: "quality"
)
```

### `analyze_structure`
코드의 구조적 문제를 분석합니다.

**매개변수:**
- `code` (string, 필수): 분석할 코드
- `type` (string, 선택): 분석 타입 (architecture, patterns, complexity)

**반환값:**
```json
{
  "complexity": "복잡도 레벨",
  "patterns": ["사용된 패턴들"],
  "suggestions": ["구조 개선 제안"]
}
```

### `suggest_improvements`
구체적인 개선 방안을 제시합니다.

**매개변수:**
- `code` (string, 필수): 개선할 코드
- `priority` (string, 선택): 우선순위 (high, medium, low)

**반환값:**
```json
{
  "refactoredCode": "개선된 코드",
  "benefits": ["개선 효과들"],
  "effort": "구현 난이도"
}
```

---

## 🔐 Security Reviewer Agent

### `security_audit`
전반적인 보안 감사를 수행합니다.

**매개변수:**
- `code` (string, 필수): 분석할 코드
- `type` (string, 필수): 코드 타입 (php, javascript, python, etc.)
- `focus` (string, 선택): 검토 포커스 (authentication, database, api, general)

**반환값:**
```json
{
  "vulnerabilities": ["발견된 취약점들"],
  "riskLevel": "위험도 (Low/Medium/High/Critical)",
  "recommendations": ["보안 강화 방안"],  
  "compliance": "보안 표준 준수 여부"
}
```

### `vulnerability_scan`
특정 취약점을 정밀 스캔합니다.

**매개변수:**
- `code` (string, 필수): 스캔할 코드
- `vulnerability_type` (string, 필수): 취약점 타입
  - `sql_injection`: SQL 인젝션
  - `xss`: Cross-Site Scripting
  - `csrf`: Cross-Site Request Forgery
  - `auth_bypass`: 인증 우회

**반환값:**
```json
{
  "found": "취약점 발견 여부 (boolean)",
  "severity": "심각도 (1-10)",
  "exploitation": "악용 가능성",
  "mitigation": "완화 방법"
}
```

### `env_security_check`
환경 변수 파일의 보안성을 검사합니다.

**매개변수:**
- `env_content` (string, 필수): .env 파일 내용

**반환값:**
```json
{
  "securityScore": "보안 점수 (1-100)",
  "issues": ["보안 문제들"],
  "recommendations": ["개선 권장사항"],
  "compliantExample": "보안 강화된 예시"
}
```

### `generate_security_checklist`
프로젝트별 보안 체크리스트를 생성합니다.

**매개변수:**
- `project_type` (string, 선택): 프로젝트 타입 (web_app, api, mobile_backend)
- `tech_stack` (string, 필수): 기술 스택

**반환값:**
```json
{
  "checklist": ["체크리스트 항목들"],
  "tools": ["권장 보안 도구들"],
  "resources": ["참고 자료들"]
}
```

---

## 🏗️ Tech Lead Agent

### `architecture_design`
시스템 아키텍처를 설계합니다.

**매개변수:**
- `project_type` (string, 필수): 프로젝트 타입
- `expected_users` (string, 필수): 예상 사용자 수
- `team_size` (string, 필수): 팀 크기
- `timeline` (string, 필수): 개발 기간
- `budget` (string, 선택): 예산 수준

**반환값:**
```json
{
  "architecture": "추천 아키텍처",
  "techStack": "기술 스택 추천",
  "roadmap": "구현 로드맵",
  "risks": "위험 요소들",
  "scalability": "확장성 고려사항"
}
```

### `tech_stack_recommendation`
최적의 기술 스택을 추천합니다.

**매개변수:**
- `project_requirements` (string, 필수): 프로젝트 요구사항
- `team_expertise` (string, 필수): 팀 기술 역량
- `performance_priority` (string, 선택): 성능 우선순위

**반환값:**
```json
{
  "frontend": "프론트엔드 기술",
  "backend": "백엔드 기술", 
  "database": "데이터베이스",
  "infrastructure": "인프라",
  "rationale": "선택 이유",
  "alternatives": "대안 기술들"
}
```

### `scalability_analysis`
시스템 확장성을 분석합니다.

**매개변수:**
- `current_architecture` (string, 필수): 현재 아키텍처
- `bottlenecks` (string, 선택): 병목 지점
- `growth_projection` (string, 필수): 성장 예측

**반환값:**
```json
{
  "currentCapacity": "현재 처리 능력",
  "bottlenecks": ["병목 지점들"],
  "scalingSolutions": ["확장 방안들"],
  "costEstimate": "비용 추정",
  "timeline": "구현 일정"
}
```

### `implementation_roadmap`
구현 로드맵을 생성합니다.

**매개변수:**
- `project_scope` (string, 필수): 프로젝트 범위
- `priority_features` (string, 필수): 우선순위 기능들
- `available_time` (string, 필수): 사용 가능한 시간

**반환값:**
```json
{
  "phases": ["개발 단계들"],
  "milestones": ["주요 마일스톤"],
  "resources": "필요 리소스",
  "dependencies": ["의존성들"],
  "risks": "위험 관리 방안"
}
```

### `code_architecture_review`
코드 아키텍처를 검토합니다.

**매개변수:**
- `code_structure` (string, 필수): 코드 구조
- `pain_points` (string, 선택): 현재 문제점들

**반환값:**
```json
{
  "assessment": "현재 상태 평가",
  "issues": ["구조적 문제들"],
  "refactoringPlan": "리팩토링 계획",
  "benefits": "개선 효과",
  "effort": "필요 노력"
}
```

---

## 🎨 UX Reviewer Agent

### `analyze_user_experience`
사용자 경험을 종합 분석합니다.

**매개변수:**
- `website_url` (string, 필수): 분석할 웹사이트 URL
- `page_type` (string, 필수): 페이지 타입 (homepage, product, checkout, login, dashboard)
- `target_users` (string, 선택): 대상 사용자 그룹
- `device_type` (string, 선택): 디바이스 타입

**반환값:**
```json
{
  "usabilityScore": "사용성 점수",
  "userJourney": "사용자 여정 분석",
  "painPoints": ["마찰 요소들"],
  "improvements": ["개선 방안들"],
  "prioritizedTasks": ["우선순위별 작업"]
}
```

### `accessibility_audit`
웹 접근성을 감사합니다.

**매개변수:**
- `html_content` (string, 필수): HTML 코드
- `wcag_level` (string, 선택): WCAG 준수 레벨 (A, AA, AAA)

**반환값:**
```json
{
  "wcagCompliance": "WCAG 준수 여부",
  "accessibilityScore": "접근성 점수",
  "violations": ["위반 사항들"],
  "fixes": ["수정 방법들"],
  "testingTools": ["권장 테스트 도구"]
}
```

### `user_journey_analysis`
사용자 여정을 분석합니다.

**매개변수:**
- `user_flow` (string, 필수): 사용자 플로우
- `pain_points` (string, 선택): 알려진 문제점들
- `business_goals` (string, 선택): 비즈니스 목표

**반환값:**
```json
{
  "journeyMap": "여정 맵",
  "frictionPoints": ["마찰 지점들"],
  "opportunities": ["개선 기회들"],
  "recommendations": ["권장사항들"],
  "metrics": "측정 지표"
}
```

### `ui_design_review`
UI 디자인을 검토합니다.

**매개변수:**
- `design_description` (string, 필수): 디자인 설명
- `design_system` (string, 선택): 디자인 시스템
- `brand_guidelines` (string, 선택): 브랜드 가이드라인

**반환값:**
```json
{
  "designScore": "디자인 점수",
  "consistency": "일관성 평가",
  "usability": "사용성 평가",
  "improvements": ["개선 제안들"],
  "designSystem": "디자인 시스템 권장사항"
}
```

### `mobile_ux_optimization`
모바일 UX를 최적화합니다.

**매개변수:**
- `current_mobile_experience` (string, 필수): 현재 모바일 경험
- `key_interactions` (string, 선택): 주요 상호작용 요소

**반환값:**
```json
{
  "mobileScore": "모바일 최적화 점수",
  "touchTargets": "터치 대상 분석",
  "performance": "모바일 성능",
  "optimizations": ["최적화 방안들"],
  "testingPlan": "테스트 계획"
}
```

### `generate_ux_test_plan`
UX 테스트 계획을 생성합니다.

**매개변수:**
- `test_objectives` (string, 필수): 테스트 목표
- `user_segments` (string, 선택): 사용자 그룹
- `key_features` (string, 필수): 테스트할 주요 기능들

**반환값:**
```json
{
  "testPlan": "테스트 계획",
  "participants": "참가자 모집 방안",
  "scenarios": ["테스트 시나리오들"],
  "metrics": ["측정 지표들"],
  "tools": ["권장 도구들"]
}
```

---

## ⚡ Performance Optimizer Agent

### `analyze_web_performance`
웹 성능을 분석합니다.

**매개변수:**
- `website_url` (string, 필수): 분석할 웹사이트
- `performance_type` (string, 선택): 성능 분석 타입 (frontend, backend, mobile, full)
- `target_metrics` (string, 선택): 목표 지표 (lighthouse, core_web_vitals, custom)

**반환값:**
```json
{
  "coreWebVitals": {
    "LCP": "Largest Contentful Paint",
    "FID": "First Input Delay", 
    "CLS": "Cumulative Layout Shift"
  },
  "performanceScore": "성능 점수",
  "bottlenecks": ["병목 지점들"],
  "optimizations": ["최적화 방안들"],
  "expectedImprovements": "예상 개선 효과"
}
```

### `optimize_frontend_code`
프론트엔드 코드를 최적화합니다.

**매개변수:**
- `code` (string, 필수): 최적화할 코드
- `framework` (string, 필수): 프레임워크 (react, vue, angular, vanilla)
- `performance_issues` (string, 선택): 알려진 성능 문제

**반환값:**
```json
{
  "optimizedCode": "최적화된 코드",
  "improvements": ["적용된 최적화들"],
  "performanceGains": "성능 향상 효과",
  "bestPractices": ["모범 사례들"],
  "bundleImpact": "번들 크기 영향"
}
```

### `optimize_database_queries`
데이터베이스 쿼리를 최적화합니다.

**매개변수:**
- `query` (string, 필수): 최적화할 쿼리
- `database_type` (string, 필수): DB 타입 (mysql, postgresql, mongodb, sqlite)
- `execution_time` (string, 선택): 현재 실행 시간
- `table_schema` (string, 선택): 테이블 스키마

**반환값:**
```json
{
  "optimizedQuery": "최적화된 쿼리",
  "indexRecommendations": ["인덱스 권장사항"],
  "performanceGain": "성능 향상 예상치",
  "explanation": "최적화 설명",
  "monitoring": "모니터링 방법"
}
```

### `analyze_bundle_size`
JavaScript 번들을 분석합니다.

**매개변수:**
- `bundle_info` (string, 필수): 번들 정보
- `target_size` (string, 선택): 목표 크기
- `build_tool` (string, 선택): 빌드 도구

**반환값:**
```json
{
  "currentSize": "현재 번들 크기",
  "analysis": "번들 분석 결과",
  "optimizations": ["최적화 방법들"],
  "expectedReduction": "예상 크기 감소",
  "buildConfig": "빌드 설정 최적화"
}
```

### `create_caching_strategy`
캐싱 전략을 수립합니다.

**매개변수:**
- `application_type` (string, 필수): 애플리케이션 타입
- `traffic_patterns` (string, 선택): 트래픽 패턴
- `current_infrastructure` (string, 선택): 현재 인프라

**반환값:**
```json
{
  "cachingStrategy": "캐싱 전략",
  "implementation": "구현 방법",
  "tools": ["권장 도구들"],
  "performance": "성능 향상 예상치",
  "monitoring": "모니터링 방안"
}
```

### `generate_performance_monitoring_plan`
성능 모니터링 계획을 생성합니다.

**매개변수:**
- `monitoring_scope` (string, 필수): 모니터링 범위
- `critical_metrics` (string, 선택): 중요 지표들
- `alert_thresholds` (string, 선택): 알림 임계값

**반환값:**
```json
{
  "monitoringPlan": "모니터링 계획",
  "tools": ["권장 도구들"],
  "metrics": ["핵심 지표들"],
  "alerts": "알림 설정",
  "dashboard": "대시보드 구성"
}
```

---

## 🔧 공통 오류 처리

모든 Agent는 다음과 같은 표준 오류 응답을 제공합니다:

```json
{
  "error": true,
  "message": "오류 메시지",
  "code": "ERROR_CODE",
  "details": "상세 오류 정보",
  "suggestions": ["해결 방법들"]
}
```

### 일반적인 오류 코드

- `INVALID_INPUT`: 잘못된 입력 데이터
- `MISSING_PARAMETER`: 필수 매개변수 누락
- `ANALYSIS_FAILED`: 분석 실행 실패
- `TIMEOUT`: 실행 시간 초과
- `RESOURCE_LIMIT`: 리소스 한계 초과

---

## 📊 응답 데이터 형식

### 표준 성공 응답
```json
{
  "success": true,
  "data": {
    // Agent별 특화 데이터
  },
  "metadata": {
    "timestamp": "2025-08-04T12:00:00Z",
    "agent": "agent-name",
    "version": "1.0.0",
    "processingTime": "2.3s"
  }
}
```

### 점수 체계
모든 점수는 0-100 범위의 정수로 제공됩니다:
- **90-100**: 우수 (Excellent)
- **80-89**: 양호 (Good) 
- **70-79**: 보통 (Fair)
- **60-69**: 개선 필요 (Needs Improvement)
- **0-59**: 부족 (Poor)

### 우선순위 레벨
- **🔴 Critical**: 즉시 수정 필요
- **🟡 High**: 빠른 수정 권장  
- **🟢 Medium**: 점진적 개선
- **🔵 Low**: 향후 고려사항

---

*📝 이 API 참조는 Agent 버전 1.0.0 기준으로 작성되었습니다.*