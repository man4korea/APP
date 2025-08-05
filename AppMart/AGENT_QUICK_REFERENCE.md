# ⚡ Agent 빠른 참조 가이드

## 🎯 한눈에 보는 Agent 기능

### 🔍 Code Reviewer
```
주요 기능: 코드 품질 검토 및 리팩토링
┣━ review_code(code, language, focus)
┣━ analyze_structure(code, type)  
┗━ suggest_improvements(code, priority)

💡 언제 사용?: 코드 작성 후, 리팩토링 전, 코드 리뷰 시
```

### 🔐 Security Reviewer  
```
주요 기능: 보안 취약점 분석 및 강화
┣━ security_audit(code, type, focus)
┣━ vulnerability_scan(code, vulnerability_type)
┣━ env_security_check(env_content)
┗━ generate_security_checklist(project_type, tech_stack)

💡 언제 사용?: 배포 전, 보안 감사, 취약점 진단 시
```

### 🏗️ Tech Lead
```
주요 기능: 시스템 아키텍처 설계 및 기술 선택
┣━ architecture_design(project_type, users, team_size, timeline)
┣━ tech_stack_recommendation(requirements, team_expertise)
┣━ scalability_analysis(architecture, bottlenecks, growth)
┣━ implementation_roadmap(scope, features, time)
┗━ code_architecture_review(structure, pain_points)

💡 언제 사용?: 프로젝트 시작, 기술 선택, 확장 계획 시
```

### 🎨 UX Reviewer
```
주요 기능: 사용자 경험 최적화 및 접근성
┣━ analyze_user_experience(url, page_type, users, device)
┣━ accessibility_audit(html_content, wcag_level)
┣━ user_journey_analysis(flow, pain_points, goals)
┣━ ui_design_review(design, system, guidelines)
┣━ mobile_ux_optimization(experience, interactions)
┗━ generate_ux_test_plan(objectives, users, features)

💡 언제 사용?: UI/UX 설계 시, 접근성 검사, 사용성 테스트 시
```

### ⚡ Performance Optimizer
```
주요 기능: 성능 분석 및 최적화
┣━ analyze_web_performance(url, type, metrics)
┣━ optimize_frontend_code(code, framework, issues)
┣━ optimize_database_queries(query, db_type, time)
┣━ analyze_bundle_size(bundle_info, target_size)
┣━ create_caching_strategy(app_type, traffic, infra)
┗━ generate_performance_monitoring_plan(scope, metrics)

💡 언제 사용?: 성능 문제 발생 시, 최적화 필요 시, 모니터링 구축 시
```

---

## 🚀 빠른 시작 체크리스트

### ✅ 설치 확인
- [ ] Claude Code 실행
- [ ] Agent 연결 상태 확인
- [ ] MCP 설정 파일 검증

### ✅ 첫 번째 사용
- [ ] 간단한 코드로 Code Reviewer 테스트
- [ ] 현재 프로젝트를 Tech Lead로 분석
- [ ] 기본 보안 검사 실행

---

## 🎯 상황별 Agent 선택 가이드

| 상황 | 1순위 | 2순위 | 3순위 |
|------|-------|-------|-------|
| 새 프로젝트 시작 | 🏗️ Tech Lead | 🔐 Security | 🎨 UX Reviewer |
| 코드 리뷰 | 🔍 Code Reviewer | 🔐 Security | ⚡ Performance |
| 성능 문제 | ⚡ Performance | 🔍 Code Reviewer | 🏗️ Tech Lead |
| 보안 감사 | 🔐 Security | 🔍 Code Reviewer | 🏗️ Tech Lead |
| UI/UX 개선 | 🎨 UX Reviewer | ⚡ Performance | 🏗️ Tech Lead |
| 배포 전 점검 | 🔐 Security | ⚡ Performance | 🎨 UX Reviewer |

---

## ⚠️ 주의사항

### 🔴 긴급 상황
- SQL 인젝션 → 즉시 Security Reviewer
- 사이트 다운 → Performance Optimizer 우선
- 데이터 유출 위험 → Security Reviewer 즉시

### 🟡 일반적 권장사항
- 복잡한 분석은 충분한 컨텍스트 제공
- Agent 결과는 참고용으로 활용
- 최종 결정은 팀 논의 후 진행

---

*💡 팁: 이 가이드를 북마크하여 빠른 참조용으로 활용하세요!*