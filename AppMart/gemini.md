# AppMart 프로젝트 Gemini & Claude Code 협업 가이드

## 🇰🇷 1. 공통 원칙

- **모든 대화는 한글로 진행합니다.**
- `SHRIMP_Tasks.md`를 모든 작업의 **마스터 기준**으로 삼습니다.
- 모든 코드와 문서의 헤더에는 표준 양식을 반드시 포함합니다.

---

## 🌊 2. 핵심 작업 프로세스 (Gemini 주도)

**Gemini는 다음 프로세스에 따라 프로젝트를 주도적으로 진행합니다.**

1.  **상세 작업 계획 수립 (Plan)**
    - `app_mart_prd.md`를 기반으로 `SHRIMP_Tasks.md`의 작업을 구체적으로 세분화하고 우선순위를 설정합니다.

2.  **환경 설정 및 구조 생성 (Setup)**
    - 계획된 작업에 필요한 모든 폴더와 기본 파일을 생성합니다.

3.  **순차적 작업 실행 (Execute)**
    - `SHRIMP_Tasks.md`에 정의된 순서에 따라 각 작업을 실행합니다. 코드 구현은 Claude Code에게 위임할 수 있습니다.

4.  **자동 문서 업데이트 (Update)**
    - **하나의 작업이 완료될 때마다**, 해당 변경사항을 `SHRIMP_Tasks.md`, `APP_MART_PROJECT_GUIDE.md` 등 관련된 모든 문서에 즉시 반영하여 항상 최신 상태를 유지합니다.

---

## 🚀 3. 빠른 시작 가이드 (For Claude Code)

**프로젝트에 처음 참여했거나, 오랜만에 복귀했을 경우 아래 절차를 따라주세요.**

1.  **핵심 문서 5종 숙지**
    - `gemini.md` (본 파일): Gemini가 주도하는 프로젝트 프로세스를 이해합니다.
    - `APP_MART_PROJECT_GUIDE.md`: 프로젝트의 기술적 표준과 구조를 파악합니다.
    - `app_mart_prd.md`: 프로젝트의 최종 목표와 요구사항을 확인합니다.
    - "C:\xampp\htdocs\AppMart\README.md"
    - "C:\xampp\htdocs\AppMart\DEPLOYMENT_GUIDE.md"

2.  **현재 작업 현황 파악**
    - `SHRIMP_Tasks.md` 파일을 열어 Gemini가 계획한 전체 작업 목록과 현재 진행 상태를 확인합니다.

3.  **개발 참여**
    - Gemini의 작업 요청에 따라 코드 구현을 시작합니다.

---

*Last updated: 2025-08-04 KST*
*Version: 2.0 - Gemini 주도 프로세스 도입*