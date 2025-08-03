<!-- 📁 C:\xampp\htdocs\BPM\SuperClaude Manual.md -->
<!-- Create at 2508021233 Ver1.00 -->

# 🔮 SuperClaude 완전 사용법 매뉴얼

## 📋 개요

SuperClaude는 Claude AI를 활용한 강력한 개발 도구로, Git 워크플로우를 자동화하고 코드 품질을 향상시킵니다.

### 🎯 주요 기능
- 🤖 AI 기반 커밋 메시지 생성
- 📅 지능형 변경로그 작성
- 📖 자동 README 문서 생성
- 🔍 심층 코드 리뷰 및 분석
- 💡 개선 아이디어 브레인스토밍
- 📚 기술 문서 자동 생성

---

## 🚀 설치 정보

### 현재 설치된 버전
```bash
SuperClaude Version: v1.0.3
시스템 요구사항: ✅ 모두 충족
  ├─ Claude Code: 1.0.67
  ├─ Git: 2.50.1.windows.1
  ├─ Node.js: v22.18.0
  └─ GitHub CLI: 2.74.1
```

### 설치된 패키지
- `superclaude@1.0.3` - 메인 도구
- `superclaude-gemini-integration-mcp@1.0.1` - MCP 통합

---

## 📖 명령어 상세 가이드

### 1. 🤖 commit - AI 커밋 메시지 생성

**기본 사용법:**
```bash
superclaude commit
```

**옵션과 함께 사용:**
```bash
# 컨텍스트 제공
superclaude commit "fixing auth bug"
superclaude commit "add user dashboard"

# 인터랙티브 모드 (확인 후 커밋)
superclaude commit -i
superclaude commit "fixing auth bug" -i

# 상세 진행상황 표시
superclaude commit -v
superclaude commit "add feature" -iv
```

**동작 과정:**
1. Git 변경사항 분석
2. 코드 컨텍스트 파악
3. Conventional Commit 형식으로 메시지 생성
4. 자동으로 stage, commit, push 수행

### 2. 📅 changelog - 변경로그 생성

**기본 사용법:**
```bash
superclaude changelog
```

**상세 모드:**
```bash
superclaude changelog -v
```

**생성되는 내용:**
- 일간/주간/월간 요약
- 사용자 영향도 중심의 변경사항
- 기술적 세부사항 필터링
- 의미있는 변경사항만 포함

### 3. 📖 readme - README 문서 생성

**기본 사용법:**
```bash
superclaude readme
```

**생성되는 섹션:**
- 프로젝트 개요 및 목적
- 설치 및 설정 가이드
- 사용법 및 예제
- API 문서 (해당시)
- 기여 가이드라인
- 라이선스 정보

### 4. 🔍 review - 코드 리뷰 및 분석

**기본 사용법:**
```bash
superclaude review
```

**분석 항목:**
- 🛡️ 보안 취약점 검사
- 📊 코드 품질 평가
- 🏗️ 아키텍처 개선점
- ⚡ 성능 최적화 제안
- 🧹 코드 정리 권장사항

### 5. 💡 brainstorm - 아이디어 브레인스토밍

**기본 사용법:**
```bash
superclaude brainstorm
```

**제안 내용:**
- 새로운 기능 아이디어
- 성능 개선 방안
- 사용자 경험 향상
- 기술 스택 업그레이드
- 확장성 개선

### 6. 📚 docs - 기술 문서 생성

**기본 사용법:**
```bash
superclaude docs
```

**생성되는 문서:**
- 아키텍처 가이드
- 컴포넌트 문서
- 배포 가이드
- 트러블슈팅 매뉴얼
- API 레퍼런스

### 7. 📝 annotate - 커밋 주석 생성

**기본 사용법:**
```bash
superclaude annotate
```

**기능:**
- 커밋 히스토리 분석
- 의미있는 주석 추가
- 코드 변경 이유 설명

---

## 🔧 고급 사용법

### 플래그 조합
```bash
# 모든 옵션 활성화
superclaude commit "major refactor" -iv

# Yarn 스크립트로 사용
yarn superclaude:commit
yarn superclaude:commit:verbose
yarn superclaude:commit:interactive
yarn superclaude:commit:full  # interactive + verbose
```

### 직접 스크립트 실행
```bash
./scripts/superclaude.sh commit -v
./scripts/superclaude.sh readme -v
```

---

## ⚡ 빠른 참조

### 자주 사용하는 명령어
```bash
# 일반적인 워크플로우
superclaude commit -i          # 커밋 (확인 후)
superclaude review             # 코드 리뷰
superclaude changelog          # 변경로그 업데이트

# 문서화 작업
superclaude readme             # README 생성
superclaude docs               # 기술 문서 생성

# 아이디어 및 개선
superclaude brainstorm         # 개선 아이디어
```

### 유용한 플래그
- `-i, --interactive` : 확인 후 실행
- `-v, --verbose` : 상세 진행상황 표시
- `--verify` : 의존성 강제 확인
- `--version` : 버전 정보 확인

---

## 🎨 BPM 프로젝트 통합 사용법

### 1. BPM 작업 시 권장 워크플로우

```bash
# 1. 작업 시작 전 현재 상태 확인
superclaude review

# 2. 개발 진행
# ... 코딩 작업 ...

# 3. 커밋 전 최종 검토
superclaude review

# 4. AI 커밋 (BPM 표준 준수)
superclaude commit "feat: [모듈명] 새 기능 구현" -i

# 5. 주요 마일스톤마다 문서 업데이트
superclaude changelog
superclaude readme
```

### 2. BPM 표준 커밋 메시지와 연동

SuperClaude가 생성하는 커밋 메시지는 자동으로 다음 형식을 따릅니다:
```
feat: [모듈명] 기능 설명
fix: [모듈명] 버그 수정
docs: [모듈명] 문서 업데이트
style: [모듈명] 스타일 변경
refactor: [모듈명] 리팩토링
test: [모듈명] 테스트 추가
```

### 3. 모듈별 색상 테마와 연동

SuperClaude는 BPM의 모듈별 색상 체계를 인식하여 문서에 적절한 색상을 적용합니다:
- 🔴 조직관리
- 🟠 구성원관리  
- 🟡 Task관리
- 🟢 문서관리
- 🔵 Process Map
- 🟣 업무Flow
- 🟤 직무분석

---

## 🛠️ 문제 해결

### 일반적인 오류

**1. Git 저장소가 아닌 경우**
```bash
# 해결법
git init
git remote add origin [저장소URL]
```

**2. 변경사항이 없는 경우**
```bash
# 해결법
git add .
superclaude commit
```

**3. Claude API 연결 문제**
```bash
# 해결법
superclaude --verify  # 의존성 재확인
```

### 디버깅 모드

상세한 로그가 필요한 경우:
```bash
superclaude [명령어] --verbose
```

---

## 🎓 활용 팁

### 1. 효율적인 사용 패턴

**일일 워크플로우:**
```bash
# 아침: 어제 작업 리뷰
superclaude changelog

# 개발 중: 주기적 커밋
superclaude commit -i

# 저녁: 최종 리뷰 및 문서화
superclaude review
superclaude docs
```

**주간 워크플로우:**
```bash
# 주 시작: 계획 수립
superclaude brainstorm

# 주 중간: 진행상황 정리
superclaude changelog

# 주 마감: 문서 완성
superclaude readme
superclaude docs
```

### 2. 팀 협업 활용

**코드 리뷰 전:**
```bash
superclaude review        # 자체 검토
superclaude commit -i     # 정리된 커밋
```

**릴리즈 준비:**
```bash
superclaude changelog     # 변경사항 정리
superclaude readme        # 문서 업데이트
superclaude docs          # 기술 문서 완성
```

### 3. 품질 관리

**정기 점검:**
```bash
# 주간 코드 품질 체크
superclaude review

# 월간 아키텍처 리뷰
superclaude brainstorm

# 분기별 문서 갱신
superclaude docs
superclaude readme
```

---

## 📞 지원 및 참고자료

### 공식 리소스
- **GitHub**: https://github.com/gwendall/superclaude
- **npm 패키지**: https://npm.im/superclaude
- **MCP 통합**: https://npm.im/superclaude-gemini-integration-mcp

### 관련 도구
- **Claude Code**: AI 기반 개발 환경
- **GitHub CLI**: Git 저장소 관리
- **MCP**: Model Context Protocol

### 버전 정보 확인
```bash
superclaude --version
```

---

## 📝 변경 이력

### v1.0.3 (현재)
- ✅ 안정적인 커밋 메시지 생성
- ✅ 향상된 변경로그 품질
- ✅ 포괄적인 코드 리뷰 기능
- ✅ MCP 통합 지원

### 향후 계획
- 🔮 더 정확한 AI 분석
- 🔮 다국어 지원 확대
- 🔮 커스터마이징 옵션 추가
- 🔮 팀 협업 기능 강화

---

*마지막 업데이트: 2025-08-02 12:33 JST*  
*SuperClaude v1.0.3 기준으로 작성됨*

---

## 🎯 BPM 프로젝트 전용 가이드

### SuperClaude + BPM 통합 워크플로우

1. **SHRIMP 작업과 연동**
   ```bash
   # SHRIMP 작업 시작 전
   superclaude review
   
   # 작업 완료 후
   superclaude commit "[작업ID] 작업 완료" -i
   ```

2. **모듈별 개발**
   ```bash
   # 조직관리 모듈 작업시
   superclaude commit "feat: 조직관리 새 기능 추가" -i
   
   # Process Map 작업시  
   superclaude commit "feat: Process Map 흐름도 개선" -i
   ```

3. **문서 자동화**
   ```bash
   # BPM 매뉴얼 업데이트
   superclaude docs
   
   # 사용자 가이드 생성
   superclaude readme
   ```

   
  📅 SuperClaude 명령어별 최적 사용 시점

  🔄 지속적으로 사용하는 명령어

  superclaude commit -i        # 매번 커밋할 때
  superclaude review          # 개발 중 주기적으로

  📊 주기적으로 사용하는 명령어

  superclaude changelog       # 주간/월간 정리시
  superclaude brainstorm      # 새 기능 계획시

  📖 프로젝트 완료 후 사용하는 명령어

  superclaude readme          # ✅ 프로젝트 완료 후
  superclaude docs           # ✅ 최종 문서화시
  superclaude annotate       # ✅ 전체 히스토리 정리시

  🎯 BPM 프로젝트 기준 권장 스케줄

  현재 단계 (개발 중):
  - superclaude commit -i - 매일 사용
  - superclaude review - 주 2-3회

  중간 마일스톤:
  - superclaude changelog - 단계 완료시
  - superclaude brainstorm - 다음 단계 계획시

  프로젝트 완료 후:
  - superclaude readme - 최종 README 생성
  - superclaude docs - 완전한 기술 문서
  - superclaude annotate - 개발 히스토리 정리

이 매뉴얼을 통해 SuperClaude의 모든 기능을 효과적으로 활용하세요! 🚀