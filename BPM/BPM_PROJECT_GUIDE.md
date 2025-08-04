<!-- 📁 C:\xampp\htdocs\BPM\BPM_PROJECT_GUIDE.md -->
<!-- Create at 2508022015 Ver3.10 -->

# 📋 BPM 프로젝트 공통 개발 지침서

## ⚡ 자동 초기화 수행 (Ver 3.10 신규)

### 🔄 BPM 폴더 접속 시 필수 체크리스트
```bash
✅ CLAUDDE.md 읽기 완료
✅ BPM_PROJECT_GUIDE.md 읽기 완료 (현재 파일)
✅ BPM_CLAUDE_CODE_GUIDE.md 읽기 예정
✅ SHRIMP 작업 현황 파악 예정
✅ Git 상태 확인 예정
```

**다음 단계**: `BPM_CLAUDE_CODE_GUIDE.md` 읽기 및 SHRIMP 상황 파악

---

## 🎯 핵심 정보

**프로젝트**: Total Business Process Management SaaS  
**개발경로**: `C:\xampp\htdocs\BPM\`  
**작업관리**: SHRIMP Task Manager + `SHRIMP_Tasks.md`  
**개발환경**: Claude Code (주) + Claude Desktop (보조)

---

## 📚 지침서 구조

### 공통 지침 (본 파일)
- 프로젝트 개요 및 목표
- 모듈별 색상 테마
- 파일 작성 표준
- 보안 설정
- 프로젝트 구조

### 환경별 전용 지침
- **Claude Code**: `BPM_CLAUDE_CODE_GUIDE.md`
- **Claude Desktop**: `BPM_CLAUDE_DESKTOP_GUIDE.md`

---

## 🌈 모듈별 무지개 색상 테마

| 모듈 | 색상 | 기본색상 | 배경색상 |
|------|------|----------|----------|
| 🔴 조직관리 | 빨강 | #ff6b6b | #fff5f5 |
| 🟠 구성원관리 | 주황 | #ff9f43 | #fff8f0 |
| 🟡 Task관리 | 노랑 | #feca57 | #fffcf0 |
| 🟢 문서관리 | 초록 | #55a3ff | #f0fff4 |
| 🔵 Process Map | 파랑 | #3742fa | #f0f8ff |
| 🟣 업무Flow | 보라 | #a55eea | #f8f0ff |
| 🟤 직무분석 | 갈색 | #8b4513 | #faf0e6 |

---

## 📁 필수 헤더 형식 (모든 파일)

```
// 📁 C:\xampp\htdocs\BPM\[전체경로]\[파일명]
// Create at YYMMDDhhmm Ver1.00
```

**시간 형식**: 동경시각(JST, UTC+9) 기준  
**예시**: `2508022000` = 2025년 8월 2일 20시 00분

---

## 🔄 공통 작업 진행 절차

### 1. 지침서 로딩
- Claude Code: 전용 지침서 자동 로딩
- Claude Desktop: 수동 참조

### 2. 작업 관리
```
SHRIMP_Tasks.md → 작업 선택 → 개발 진행 → 완료 검증
```

### 3. 개발 표준
- 표준 헤더 적용
- 모듈별 색상 테마 적용
- 상세 주석 작성

### 4. 품질 관리
- 테스트 실행 (Claude Code)
- 코드 리뷰 (Claude Desktop)
- 문서화 (공동 작업)

---

## 🚀 4단계 자동 배포

```
XAMPP → Git → OneDrive → 웹호스팅
```

**배포 경로**:
- 로컬: `C:\xampp\htdocs\BPM\`
- OneDrive: `C:\Users\man4k\OneDrive\문서\APP\bpm\`
- 웹호스팅: `http://bpmapp.dothome.co.kr`
- FTP 서버: `112.175.185.148`

---

## 🔒 보안 설정

모든 API 키와 중요 설정은 `.env` 파일에 저장:

```bash
# 데이터베이스 설정 (웹호스팅 환경용)
DB_HOST=localhost
DB_NAME=bpmapp
DB_USER=bpmapp
DB_PASS=dmlwjdqn!Wkd24
DB_CHARSET=utf8mb4

# FTP 설정 (관리용)
FTP_HOST=112.175.185.148
FTP_USER=bpmapp
FTP_PASS=dmlwjdqn!Wkd24

# 사이트 설정
SITE_URL=http://bpmapp.dothome.co.kr
ADMIN_DEFAULT_USER=bpmapp
ADMIN_DEFAULT_PASS=dmlwjdqn!Wkd24

# 보안
APP_KEY=BPM_SECRET_KEY_2025_CHANGE_THIS
JWT_SECRET=JWT_SECRET_KEY_FOR_BPM_SYSTEM

# Git 설정
LOCAL_GIT_REPOSITORY_PATH="C:\Program Files\Git"
GITHUB_USERNAME=man4korea
GITHUB_REPO=ai-collaboration

# 배포 경로
LOCAL_PATH=C:\\xampp\\htdocs\\BPM
ONEDRIVE_PATH=C:\\Users\\man4k\\OneDrive\\문서\\APP\\bpm
WEBSERVER_PATH=W:\\html
```

---

## 📊 프로젝트 구조

```
BPM/
├── SHRIMP_Tasks.md                    # ⭐ 작업 관리
├── BPM_PROJECT_GUIDE.md               # ⭐ 공통 지침서
├── BPM_CLAUDE_CODE_GUIDE.md           # ⭐ Claude Code 전용
├── BPM_CLAUDE_DESKTOP_GUIDE.md        # ⭐ Claude Desktop 전용
├── .env                               # 환경 설정
├── modules/                           # 🌈 10개 모듈
│   ├── organization/                  # 🔴 조직관리
│   ├── members/                       # 🟠 구성원관리
│   ├── tasks/                         # 🟡 Task관리
│   └── ...
├── shared/                            # 공통 컴포넌트
├── assets/                            # CSS/JS/이미지
├── tests/                             # 자동화 테스트
└── scripts/                           # 배포 스크립트
```

---

## 🧪 테스트 자동화

**테스트 통과 기준**:
1. ✅ 모든 모듈 정상 로딩
2. ✅ 색상 테마 올바른 적용  
3. ✅ 기본 기능 동작 확인
4. ✅ 반응형 디자인 검증
5. ✅ 에러 없음

---

## 📚 주요 파일

1. **[SHRIMP_Tasks.md](./SHRIMP_Tasks.md)** - 실시간 작업 관리
2. **[README.md](./README.md)** - 프로젝트 소개
3. **[.env](./.env)** - 환경 설정
4. **[BPM_CLAUDE_CODE_GUIDE.md](./BPM_CLAUDE_CODE_GUIDE.md)** - Claude Code 가이드
5. **[BPM_CLAUDE_DESKTOP_GUIDE.md](./BPM_CLAUDE_DESKTOP_GUIDE.md)** - Claude Desktop 가이드

---

## 🎯 협업 방식

### Claude Code 담당
- 실제 코드 개발
- 파일 생성/편집
- 테스트 실행
- Git 관리
- 자동 배포

### Claude Desktop 담당  
- 프로젝트 기획
- 아키텍처 설계
- 코드 리뷰
- 문서 작성
- 복잡한 논의

### 공동 작업
- SHRIMP 작업 관리
- 문제 해결
- 품질 관리
- 진행 상황 공유

## 🔄 협업 워크플로우

### Claude Desktop → Claude Code 작업 요청
```markdown
## 개발 요청 템플릿

### 작업 제목
[모듈명] - [기능명] 개발

### 작업 설명
- **목적**: [개발 목적]
- **기능**: [구현할 기능 목록]
- **UI/UX**: [디자인 요구사항]
- **API**: [필요한 API 엔드포인트]

### 기술 요구사항
- **색상 테마**: [모듈 색상] ([색상코드])
- **권한 레벨**: [필요한 권한]
- **데이터베이스**: [테이블 구조]
- **테스트**: [테스트 시나리오]

### 완료 기준
- [ ] 기능 구현 완료
- [ ] 색상 테마 적용
- [ ] 테스트 통과
- [ ] 문서화 완료

### 우선순위
- 🔴 긴급 / 🟡 보통 / 🟢 낮음
```

### Claude Code → Claude Desktop 리뷰 요청
```markdown
## 리뷰 요청 템플릿

### 개발 완료 보고
- **작업**: [SHRIMP 작업ID] - [작업명]
- **파일**: [생성/수정된 파일 목록]
- **기능**: [구현된 기능 설명]
- **테스트**: [테스트 결과]

### 리뷰 요청 사항
- [ ] 코드 품질 검토
- [ ] 보안 검토
- [ ] 성능 검토
- [ ] 문서화 검토

### 특별 고려사항
[특별히 검토가 필요한 부분]
```

---

## 🎯 품질 관리 체크리스트

### 코드 리뷰 체크리스트
- [ ] **표준 준수**: 헤더 형식, 네이밍 규칙
- [ ] **보안**: 입력값 검증, SQL 인젝션 방지
- [ ] **성능**: 쿼리 최적화, 캐싱 적용
- [ ] **가독성**: 주석, 코드 구조
- [ ] **테스트**: 단위 테스트, 통합 테스트
- [ ] **문서화**: API 문서, 사용자 가이드

### UI/UX 리뷰 체크리스트
- [ ] **색상 테마**: 모듈별 무지개 색상 적용
- [ ] **반응형**: 모바일, 태블릿, 데스크톱 지원
- [ ] **접근성**: 키보드 네비게이션, 스크린 리더
- [ ] **사용성**: 직관적인 인터페이스
- [ ] **일관성**: 전체 시스템과의 일관성

### 배포 전 체크리스트
- [ ] **테스트**: 모든 테스트 통과
- [ ] **보안**: 보안 스캔 완료
- [ ] **성능**: 성능 테스트 완료
- [ ] **문서**: 사용자 매뉴얼 업데이트
- [ ] **백업**: OneDrive 백업 완료

---

## 📞 이슈 관리 가이드

### 버그 리포트 템플릿
```markdown
## 🐛 버그 리포트

### 환경 정보
- **브라우저**: [Chrome/Firefox/Safari] [버전]
- **OS**: [Windows/Mac/Linux] [버전]
- **URL**: [문제가 발생한 페이지]

### 문제 설명
[문제에 대한 명확한 설명]

### 재현 단계
1. [1단계]
2. [2단계]
3. [3단계]

### 예상 결과
[어떤 결과를 예상했는지]

### 실제 결과
[실제로 어떤 일이 발생했는지]

### 스크린샷
[가능하면 스크린샷 첨부]

### 우선순위
- 🔴 치명적 / 🟡 중요 / 🟢 보통 / 🔵 낮음
```

### 기능 요청 템플릿
```markdown
## ✨ 기능 요청

### 요청 기능
[요청하는 기능에 대한 간단한 설명]

### 필요성
[왜 이 기능이 필요한지]

### 상세 설명
[기능의 상세한 동작 방식]

### 수용 기준
- [ ] [기준 1]
- [ ] [기준 2]
- [ ] [기준 3]

### 우선순위
- 🔴 높음 / 🟡 보통 / 🟢 낮음
```

---

## 📈 프로젝트 진행 모니터링

### 주간 진행 보고서 템플릿
```markdown
# 📊 BPM 프로젝트 주간 보고서

## 📅 보고 기간
2025년 [월] [주차] (MM/DD ~ MM/DD)

## 🎯 주요 성과
- [완료된 주요 작업들]

## 📊 진행 현황
### SHRIMP 작업 현황
- ✅ 완료: [개수]
- 🟡 진행중: [개수]
- ⏳ 대기중: [개수]
- 🔴 차단: [개수]

### 모듈별 진행률
- 🔴 조직관리: [%]%
- 🟠 구성원관리: [%]%
- 🟡 Task관리: [%]%
- 🟢 문서관리: [%]%

## 🎨 기술적 성과
- [새로 도입된 기술이나 개선사항]

## 🚀 다음 주 계획
- [다음 주 주요 목표]

## ⚠️ 이슈 및 리스크
- [발생한 문제점과 해결 방안]
```

---

## 🎓 베스트 프랙티스

### 협업 효율성 향상
1. **명확한 소통**: 요구사항과 완료 기준을 구체적으로 명시
2. **표준화**: 템플릿과 체크리스트 활용
3. **문서화**: 모든 결정과 변경사항 기록
4. **피드백**: 정기적인 리뷰와 개선

### 코드 품질 향상
1. **리뷰 문화**: 모든 코드는 리뷰 후 병합
2. **자동화**: 테스트와 배포 프로세스 자동화
3. **모니터링**: 성능과 오류 실시간 모니터링
4. **학습**: 새로운 기술과 베스트 프랙티스 지속 학습

---

## 🔮 미래 확장 계획

### 단기 목표 (1-3개월)
- 핵심 10개 모듈 완성
- 멀티테넌트 기능 구현
- 성능 최적화

### 중기 목표 (3-6개월)
- API 플랫폼화
- 모바일 앱 개발
- 화이트라벨 서비스

### 장기 목표 (6-12개월)
- AI 기능 통합
- 글로벌 확장
- SaaS 비즈니스 모델


---

*Last updated: 2025-08-02 20:00 JST*  
*Version: 3.00 - 공통 지침서*