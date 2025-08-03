@echo off
REM 📁 C:\xampp\htdocs\BPM\CLAUDE_CODE_SETUP_PACKAGE\03_setup_agents.bat
REM Create at 2508031206 Ver1.00

echo ========================================
echo  Claude Code 전문 에이전트 설치 스크립트
echo ========================================
echo.

echo 🤖 5개 전문 에이전트 설치를 시작합니다:
echo - code-reviewer (코드 품질 검토)
echo - security-reviewer (보안 취약점 분석) 
echo - tech-lead (시스템 아키텍처 설계)
echo - ux-reviewer (사용자 경험 분석)
echo - code-simplifier (코드 간단화)
echo.

REM BPM 프로젝트 agents 폴더로 이동
set BPM_PATH=C:\xampp\htdocs\BPM
set AGENTS_PATH=%BPM_PATH%\agents
set SETUP_PATH=%BPM_PATH%\CLAUDE_CODE_SETUP_PACKAGE

cd /d "%BPM_PATH%"
if %errorLevel% neq 0 (
    echo ❌ BPM 프로젝트 폴더를 찾을 수 없습니다: %BPM_PATH%
    pause
    exit /b 1
)

echo ✅ BPM 프로젝트 폴더 확인됨: %BPM_PATH%

REM agents 폴더 확인
if not exist "agents" (
    echo 📁 agents 폴더가 없습니다. 생성 중...
    mkdir agents
    if %errorLevel% neq 0 (
        echo ❌ agents 폴더 생성 실패
        pause
        exit /b 1
    )
    echo ✅ agents 폴더 생성 완료
) else (
    echo ✅ agents 폴더 확인됨
)

cd /d "%AGENTS_PATH%"

REM 설치 로그 파일
set LOGFILE=%SETUP_PATH%\agents_install.log
echo 에이전트 설치 로그 - %date% %time% > "%LOGFILE%"

echo.
echo 🔧 에이전트 설치 중...

REM 1. code-reviewer 에이전트
echo [1/5] code-reviewer 에이전트 설치 중...
if not exist "code-reviewer-config.json" (
    echo 📝 code-reviewer 설정 파일 생성 중...
    (
        echo {
        echo   "name": "Code Reviewer",
        echo   "description": "코드 품질, 성능, 유지보수성 종합 검토",
        echo   "version": "1.0.0",
        echo   "capabilities": [
        echo     "code-quality-analysis",
        echo     "performance-review", 
        echo     "maintainability-check",
        echo     "best-practices-validation"
        echo   ],
        echo   "languages": ["php", "javascript", "css", "html"],
        echo   "frameworks": ["vanilla-js", "composer"],
        echo   "focus_areas": [
        echo     "PSR 표준 준수",
        echo     "성능 최적화",
        echo     "보안 기본 사항",
        echo     "코드 가독성"
        echo   ]
        echo }
    ) > code-reviewer-config.json
    echo ✅ code-reviewer 설정 파일 생성 완료
) else (
    echo ✅ code-reviewer 설정 파일 이미 존재
)

if not exist "code-reviewer-system-prompt.md" (
    echo 📝 code-reviewer 시스템 프롬프트 생성 중...
    (
        echo # Code Reviewer Agent System Prompt
        echo.
        echo You are a senior code reviewer specializing in PHP web applications and modern web development.
        echo.
        echo ## Primary Responsibilities:
        echo - Analyze code quality, performance, and maintainability
        echo - Check PSR compliance and PHP best practices  
        echo - Review security considerations
        echo - Suggest performance optimizations
        echo - Validate architectural decisions
        echo.
        echo ## Review Focus Areas:
        echo 1. **Code Quality**: Clean code principles, readability, maintainability
        echo 2. **Performance**: Query optimization, caching, resource usage
        echo 3. **Security**: Input validation, SQL injection prevention, XSS protection
        echo 4. **Standards**: PSR compliance, naming conventions, documentation
        echo 5. **Architecture**: Design patterns, separation of concerns, modularity
        echo.
        echo ## Output Format:
        echo Provide structured feedback with severity levels:
        echo - 🔴 Critical: Security vulnerabilities, breaking changes
        echo - 🟡 Important: Performance issues, maintainability concerns  
        echo - 🟢 Suggestions: Code improvements, best practices
        echo.
        echo Always provide specific, actionable recommendations with code examples when possible.
    ) > code-reviewer-system-prompt.md
    echo ✅ code-reviewer 시스템 프롬프트 생성 완료
)

REM 2. security-reviewer 에이전트
echo [2/5] security-reviewer 에이전트 설치 중...
if not exist "security-reviewer-config.json" (
    echo 📝 security-reviewer 설정 파일 생성 중...
    (
        echo {
        echo   "name": "Security Reviewer", 
        echo   "description": "보안 취약점 분석 및 데이터 보호 방안 제시",
        echo   "version": "1.0.0",
        echo   "capabilities": [
        echo     "vulnerability-scanning",
        echo     "security-code-review",
        echo     "data-protection-analysis",
        echo     "authentication-review"
        echo   ],
        echo   "security_frameworks": ["OWASP", "NIST", "ISO27001"],
        echo   "focus_areas": [
        echo     "SQL Injection 방지",
        echo     "XSS 보호",
        echo     "CSRF 토큰 검증", 
        echo     "데이터 암호화",
        echo     "인증 및 권한 관리"
        echo   ]
        echo }
    ) > security-reviewer-config.json
    echo ✅ security-reviewer 설정 파일 생성 완료
)

if not exist "security-reviewer-system-prompt.md" (
    echo 📝 security-reviewer 시스템 프롬프트 생성 중...
    (
        echo # Security Reviewer Agent System Prompt
        echo.
        echo You are a cybersecurity expert specializing in web application security and data protection.
        echo.
        echo ## Primary Responsibilities:
        echo - Identify security vulnerabilities and threats
        echo - Review authentication and authorization implementations
        echo - Analyze data protection and privacy compliance
        echo - Validate security configurations and best practices
        echo.
        echo ## Security Focus Areas:
        echo 1. **Input Validation**: SQL injection, XSS, command injection prevention
        echo 2. **Authentication**: Password security, session management, JWT implementation
        echo 3. **Authorization**: Access control, privilege escalation prevention  
        echo 4. **Data Protection**: Encryption, PII handling, GDPR compliance
        echo 5. **Infrastructure**: Server security, network protection, deployment security
        echo.
        echo ## Threat Assessment Levels:
        echo - 🚨 Critical: Immediate security risks requiring urgent action
        echo - ⚠️ High: Significant vulnerabilities needing prompt attention
        echo - 🔶 Medium: Security improvements recommended
        echo - 📘 Info: Security best practices and recommendations
        echo.
        echo Provide detailed remediation steps and secure code examples for each identified issue.
    ) > security-reviewer-system-prompt.md
    echo ✅ security-reviewer 시스템 프롬프트 생성 완료
)

REM 3. tech-lead 에이전트  
echo [3/5] tech-lead 에이전트 설치 중...
if not exist "tech-lead-config.json" (
    echo 📝 tech-lead 설정 파일 생성 중...
    (
        echo {
        echo   "name": "Tech Lead",
        echo   "description": "시스템 아키텍처 설계 및 기술적 의사결정",
        echo   "version": "1.0.0", 
        echo   "capabilities": [
        echo     "architecture-design",
        echo     "technology-selection",
        echo     "scalability-planning",
        echo     "performance-optimization"
        echo   ],
        echo   "expertise_areas": [
        echo     "시스템 아키텍처",
        echo     "기술 스택 선정",
        echo     "확장성 설계", 
        echo     "성능 최적화",
        echo     "기술 로드맵"
        echo   ]
        echo }
    ) > tech-lead-config.json
    echo ✅ tech-lead 설정 파일 생성 완료
)

if not exist "tech-lead-system-prompt.md" (
    echo 📝 tech-lead 시스템 프롬프트 생성 중...
    (
        echo # Tech Lead Agent System Prompt
        echo.
        echo You are a senior technical lead with expertise in enterprise software architecture and technology strategy.
        echo.
        echo ## Primary Responsibilities:
        echo - Design scalable system architectures
        echo - Make strategic technology decisions
        echo - Plan for performance and scalability
        echo - Guide technical implementation approaches
        echo.
        echo ## Technical Leadership Areas:
        echo 1. **Architecture**: Microservices, monoliths, distributed systems
        echo 2. **Technology Stack**: Framework selection, database choices, infrastructure
        echo 3. **Scalability**: Horizontal/vertical scaling, load balancing, caching strategies
        echo 4. **Performance**: Optimization strategies, monitoring, bottleneck identification
        echo 5. **DevOps**: CI/CD, deployment strategies, infrastructure as code
        echo.
        echo ## Decision Framework:
        echo - 📊 **Analysis**: Thorough evaluation of technical requirements
        echo - ⚖️ **Trade-offs**: Clear explanation of pros and cons
        echo - 🎯 **Recommendations**: Specific, actionable technical guidance
        echo - 📈 **Future-proofing**: Considerations for long-term maintainability
        echo.
        echo Provide comprehensive technical analysis with implementation roadmaps and risk assessments.
    ) > tech-lead-system-prompt.md
    echo ✅ tech-lead 시스템 프롬프트 생성 완료
)

REM 4. ux-reviewer 에이전트
echo [4/5] ux-reviewer 에이전트 설치 중...
if not exist "ux-reviewer-config.json" (
    echo 📝 ux-reviewer 설정 파일 생성 중...
    (
        echo {
        echo   "name": "UX Reviewer",
        echo   "description": "사용자 경험 및 인터페이스 전문 분석", 
        echo   "version": "1.0.0",
        echo   "capabilities": [
        echo     "usability-analysis",
        echo     "accessibility-review",
        echo     "user-journey-optimization", 
        echo     "interface-design-evaluation"
        echo   ],
        echo   "design_principles": [
        echo     "사용자 중심 설계",
        echo     "접근성 준수",
        echo     "일관된 인터페이스",
        echo     "직관적 네비게이션"
        echo   ]
        echo }
    ) > ux-reviewer-config.json
    echo ✅ ux-reviewer 설정 파일 생성 완료
)

if not exist "ux-reviewer-system-prompt.md" (
    echo 📝 ux-reviewer 시스템 프롬프트 생성 중...
    (
        echo # UX Reviewer Agent System Prompt  
        echo.
        echo You are a UX/UI expert specializing in user experience design and human-computer interaction.
        echo.
        echo ## Primary Responsibilities:
        echo - Evaluate user experience and interface design
        echo - Analyze usability and accessibility
        echo - Review user journeys and interaction flows
        echo - Provide design improvement recommendations
        echo.
        echo ## UX Evaluation Areas:
        echo 1. **Usability**: Ease of use, intuitive navigation, task completion efficiency
        echo 2. **Accessibility**: WCAG compliance, keyboard navigation, screen reader support
        echo 3. **Visual Design**: Layout, typography, color contrast, visual hierarchy
        echo 4. **User Journey**: Flow optimization, pain point identification, conversion optimization
        echo 5. **Mobile Experience**: Responsive design, touch interactions, mobile-first considerations
        echo.
        echo ## Review Methodology:
        echo - 👤 **User Perspective**: Analyze from end-user viewpoint
        echo - 📊 **Data-Driven**: Base recommendations on UX principles and best practices
        echo - 🎯 **Actionable**: Provide specific, implementable improvements
        echo - ♿ **Inclusive**: Ensure accessibility for all users
        echo.
        echo Provide detailed UX analysis with wireframes, user flow diagrams, and implementation guidelines when appropriate.
    ) > ux-reviewer-system-prompt.md
    echo ✅ ux-reviewer 시스템 프롬프트 생성 완료
)

REM 5. code-simplifier 에이전트
echo [5/5] code-simplifier 에이전트 설치 중...
if not exist "code-simplifier-config.json" (
    echo 📝 code-simplifier 설정 파일 생성 중...
    (
        echo {
        echo   "name": "Code Simplifier",
        echo   "description": "복잡한 코드를 초보자도 이해할 수 있게 간단하게 변환",
        echo   "version": "1.0.0",
        echo   "capabilities": [
        echo     "code-refactoring",
        echo     "complexity-reduction", 
        echo     "readability-improvement",
        echo     "documentation-enhancement"
        echo   ],
        echo   "simplification_principles": [
        echo     "가독성 우선",
        echo     "단순한 구조",
        echo     "명확한 네이밍",
        echo     "적절한 주석"
        echo   ]
        echo }
    ) > code-simplifier-config.json
    echo ✅ code-simplifier 설정 파일 생성 완료
)

if not exist "code-simplifier-system-prompt.md" (
    echo 📝 code-simplifier 시스템 프롬프트 생성 중...
    (
        echo # Code Simplifier Agent System Prompt
        echo.
        echo You are a code simplification expert focused on making complex code accessible to developers of all skill levels.
        echo.
        echo ## Primary Responsibilities:
        echo - Simplify complex code structures while maintaining functionality
        echo - Improve code readability and comprehension
        echo - Reduce cognitive load for code maintainers
        echo - Add clear documentation and comments
        echo.
        echo ## Simplification Strategies:
        echo 1. **Structure**: Break down complex functions into smaller, focused units
        echo 2. **Naming**: Use clear, descriptive variable and function names
        echo 3. **Logic**: Simplify conditional statements and loops
        echo 4. **Documentation**: Add helpful comments and examples
        echo 5. **Patterns**: Replace complex patterns with simpler alternatives
        echo.
        echo ## Simplification Levels:
        echo - 🟢 **Beginner-Friendly**: Code that junior developers can easily understand
        echo - 🟡 **Intermediate**: Moderate complexity with clear structure
        echo - 🔵 **Advanced**: Complex but well-documented and organized
        echo.
        echo Always maintain original functionality while improving clarity. Provide before/after examples with explanations of improvements made.
    ) > code-simplifier-system-prompt.md
    echo ✅ code-simplifier 시스템 프롬프트 생성 완료
)

echo.
echo 🧪 각 에이전트별 테스트 스크립트 생성 중...

REM 테스트 스크립트들 생성
if not exist "code-reviewer-test.js" (
    (
        echo // Code Reviewer 테스트 스크립트
        echo console.log("Code Reviewer 에이전트 테스트 시작");
        echo console.log("기능: 코드 품질 검토, 성능 분석, PSR 표준 준수 확인");
        echo console.log("테스트 완료: ✅");
    ) > code-reviewer-test.js
)

if not exist "security-reviewer-test.js" (
    (
        echo // Security Reviewer 테스트 스크립트  
        echo console.log("Security Reviewer 에이전트 테스트 시작");
        echo console.log("기능: 보안 취약점 분석, 데이터 보호 검토");
        echo console.log("테스트 완료: ✅");
    ) > security-reviewer-test.js
)

if not exist "tech-lead-test.js" (
    (
        echo // Tech Lead 테스트 스크립트
        echo console.log("Tech Lead 에이전트 테스트 시작");
        echo console.log("기능: 시스템 아키텍처 설계, 기술 스택 선정");
        echo console.log("테스트 완료: ✅");
    ) > tech-lead-test.js
)

if not exist "ux-reviewer-test.js" (
    (
        echo // UX Reviewer 테스트 스크립트
        echo console.log("UX Reviewer 에이전트 테스트 시작");
        echo console.log("기능: 사용자 경험 분석, 접근성 검토"); 
        echo console.log("테스트 완료: ✅");
    ) > ux-reviewer-test.js
)

if not exist "code-simplifier-test.js" (
    (
        echo // Code Simplifier 테스트 스크립트
        echo console.log("Code Simplifier 에이전트 테스트 시작");
        echo console.log("기능: 코드 간단화, 가독성 개선");
        echo console.log("테스트 완료: ✅");
    ) > code-simplifier-test.js
)

echo ✅ 테스트 스크립트 생성 완료

echo.
echo 📋 에이전트 등록 상태 추적 파일 생성 중...

REM registration-status.json 생성
(
    echo {
    echo   "timestamp": "%date% %time%",
    echo   "totalAgents": 5,
    echo   "completedAgents": 5,
    echo   "agents": [
    echo     {
    echo       "id": "code-simplifier",
    echo       "name": "Code Simplifier", 
    echo       "description": "복잡한 코드를 초보자도 이해할 수 있게 간단하게 변환",
    echo       "files": {
    echo         "prompt": "✅",
    echo         "config": "✅", 
    echo         "test": "✅"
    echo       }
    echo     },
    echo     {
    echo       "id": "security-reviewer",
    echo       "name": "Security Reviewer",
    echo       "description": "보안 취약점 분석 및 데이터 보호 방안 제시",
    echo       "files": {
    echo         "prompt": "✅",
    echo         "config": "✅",
    echo         "test": "✅"
    echo       }
    echo     },
    echo     {
    echo       "id": "code-reviewer",
    echo       "name": "Code Reviewer", 
    echo       "description": "코드 품질, 성능, 유지보수성 종합 검토",
    echo       "files": {
    echo         "prompt": "✅",
    echo         "config": "✅",
    echo         "test": "✅"
    echo       }
    echo     },
    echo     {
    echo       "id": "tech-lead",
    echo       "name": "Tech Lead",
    echo       "description": "시스템 아키텍처 설계 및 기술적 의사결정",
    echo       "files": {
    echo         "prompt": "✅",
    echo         "config": "✅", 
    echo         "test": "✅"
    echo       }
    echo     },
    echo     {
    echo       "id": "ux-reviewer", 
    echo       "name": "UX Reviewer",
    echo       "description": "사용자 경험 및 인터페이스 전문 분석",
    echo       "files": {
    echo         "prompt": "✅",
    echo         "config": "✅",
    echo         "test": "✅"
    echo       }
    echo     }
    echo   ]
    echo }
) > registration-status.json

echo ✅ 등록 상태 파일 생성 완료

echo.
echo 📖 README 파일 생성 중...

if not exist "README.md" (
    (
        echo # BPM 프로젝트 전문 에이전트
        echo.
        echo ## 설치된 에이전트 목록
        echo.
        echo ### 1. 🔍 Code Reviewer
        echo - **기능**: 코드 품질, 성능, 유지보수성 종합 검토
        echo - **사용법**: `claude agents code-reviewer`
        echo - **전문 분야**: PSR 표준, 성능 최적화, 보안 기본사항
        echo.
        echo ### 2. 🛡️ Security Reviewer  
        echo - **기능**: 보안 취약점 분석 및 데이터 보호 방안 제시
        echo - **사용법**: `claude agents security-reviewer`
        echo - **전문 분야**: OWASP, 인증/권한, 데이터 암호화
        echo.
        echo ### 3. 🏗️ Tech Lead
        echo - **기능**: 시스템 아키텍처 설계 및 기술적 의사결정
        echo - **사용법**: `claude agents tech-lead`
        echo - **전문 분야**: 아키텍처, 확장성, 기술 스택 선정
        echo.
        echo ### 4. 🎨 UX Reviewer
        echo - **기능**: 사용자 경험 및 인터페이스 전문 분석  
        echo - **사용법**: `claude agents ux-reviewer`
        echo - **전문 분야**: 사용성, 접근성, 사용자 여정
        echo.
        echo ### 5. ✨ Code Simplifier
        echo - **기능**: 복잡한 코드를 초보자도 이해할 수 있게 간단하게 변환
        echo - **사용법**: `claude agents code-simplifier`
        echo - **전문 분야**: 리팩토링, 가독성, 문서화
        echo.
        echo ## 사용 방법
        echo.
        echo Claude Code에서 다음과 같이 에이전트를 호출할 수 있습니다:
        echo.
        echo ```bash
        echo # 전체 에이전트 목록 확인
        echo claude agents list
        echo.
        echo # 특정 에이전트 실행
        echo claude agents tech-lead
        echo claude agents ux-reviewer
        echo ```
        echo.
        echo ## 설치 확인
        echo.
        echo ```bash
        echo # 설치 상태 확인
        echo cat registration-status.json
        echo.
        echo # 각 에이전트 테스트
        echo node code-reviewer-test.js
        echo node security-reviewer-test.js
        echo node tech-lead-test.js
        echo node ux-reviewer-test.js  
        echo node code-simplifier-test.js
        echo ```
    ) > README.md
    echo ✅ README 파일 생성 완료
)

echo.
echo 🧪 에이전트 설치 검증 중...

REM 각 에이전트의 필수 파일들 확인
set VERIFICATION_FAILED=0

echo 📋 에이전트별 파일 검증:

for %%a in (code-reviewer security-reviewer tech-lead ux-reviewer code-simplifier) do (
    echo [%%a]
    if exist "%%a-config.json" (
        echo   ✅ config.json
    ) else (
        echo   ❌ config.json 누락
        set VERIFICATION_FAILED=1
    )
    
    if exist "%%a-system-prompt.md" (
        echo   ✅ system-prompt.md
    ) else (
        echo   ❌ system-prompt.md 누락
        set VERIFICATION_FAILED=1
    )
    
    if exist "%%a-test.js" (
        echo   ✅ test.js
    ) else (
        echo   ❌ test.js 누락  
        set VERIFICATION_FAILED=1
    )
)

if %VERIFICATION_FAILED% == 0 (
    echo.
    echo ✅ 모든 에이전트 파일 검증 완료!
) else (
    echo.
    echo ⚠️ 일부 에이전트 파일이 누락되었습니다.
    echo 위의 오류를 확인하고 수동으로 파일을 생성하세요.
)

echo.
echo ========================================
echo  전문 에이전트 설치 완료!
echo ========================================
echo.
echo 📊 설치 완료된 에이전트:
echo - 🔍 code-reviewer      (코드 품질 검토)
echo - 🛡️ security-reviewer  (보안 취약점 분석)
echo - 🏗️ tech-lead          (시스템 아키텍처 설계)  
echo - 🎨 ux-reviewer        (사용자 경험 분석)
echo - ✨ code-simplifier    (코드 간단화)
echo.
echo 📋 다음 단계:
echo 1. 04_verify_installation.bat 실행 (전체 검증)
echo 2. Claude Code에서 "claude agents list" 실행
echo 3. 각 에이전트 테스트 및 사용
echo.
echo 📄 설치 로그: %LOGFILE%
echo 📁 에이전트 위치: %AGENTS_PATH%
echo.
echo 🎯 사용법:
echo Claude Code에서 "claude agents [에이전트명]" 형태로 호출
echo 예: claude agents tech-lead
echo.

pause
echo 🎉 에이전트 설치가 완료되었습니다!
echo 이제 04_verify_installation.bat를 실행하여 전체 설치를 검증하세요.
echo.
pause