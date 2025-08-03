// 📁 C:\xampp\htdocs\BPM\agents\ux-reviewer-test.js
// Create at 2508031329 Ver1.00

/**
 * UX Reviewer 에이전트 테스트 파일
 * 목적: 사용자 경험(UX/UI) 분석 기능을 검증
 */

// 테스트용 웹사이트 분석 시나리오들
const uxAnalysisTestCases = {
    // 테스트 케이스 1: 이커머스 웹사이트
    ecommerceStore: {
        type: "온라인 쇼핑몰",
        url: "https://example-store.com",
        primaryTasks: [
            "상품 검색 및 찾기",
            "상품 상세 정보 확인",
            "장바구니에 추가",
            "체크아웃 및 결제",
            "계정 생성 및 로그인"
        ],
        targetUsers: ["일반 소비자", "모바일 사용자", "시니어 사용자"],
        expectedIssues: [
            "복잡한 체크아웃 프로세스",
            "모바일 최적화 부족",
            "검색 결과 필터링 어려움",
            "접근성 미준수"
        ],
        uxScore: "70-80점 예상"
    },

    // 테스트 케이스 2: SaaS 대시보드
    saasDashboard: {
        type: "비즈니스 대시보드",
        url: "https://example-dashboard.com",
        primaryTasks: [
            "로그인 및 인증",
            "데이터 시각화 확인",
            "보고서 생성",
            "설정 변경",
            "팀 멤버 초대"
        ],
        targetUsers: ["비즈니스 사용자", "데이터 분석가", "관리자"],
        expectedIssues: [
            "정보 과부하",
            "복잡한 내비게이션",
            "불명확한 액션 버튼",
            "반응형 디자인 부족"
        ],
        uxScore: "60-70점 예상"
    },

    // 테스트 케이스 3: 뉴스/미디어 사이트
    newsWebsite: {
        type: "뉴스 포털",
        url: "https://example-news.com",
        primaryTasks: [
            "최신 뉴스 읽기",
            "카테고리별 기사 탐색",
            "검색 기능 사용",
            "기사 공유",
            "구독 및 알림 설정"
        ],
        targetUsers: ["일반 독자", "모바일 사용자", "접근성 필요 사용자"],
        expectedIssues: [
            "광고로 인한 콘텐츠 방해",
            "느린 로딩 속도",
            "모바일 가독성 부족",
            "복잡한 레이아웃"
        ],
        uxScore: "65-75점 예상"
    },

    // 테스트 케이스 4: 금융 서비스 앱
    financeApp: {
        type: "모바일 뱅킹 앱",
        url: "https://example-bank.com",
        primaryTasks: [
            "계좌 잔액 확인",
            "송금 및 이체",
            "거래 내역 조회",
            "투자 상품 확인",
            "고객센터 문의"
        ],
        targetUsers: ["은행 고객", "모바일 우선 사용자", "시니어 사용자"],
        expectedIssues: [
            "보안과 사용성의 균형",
            "복잡한 인증 절차",
            "전문 용어 사용",
            "접근성 고려 부족"
        ],
        uxScore: "55-65점 예상"
    }
};

// Nielsen의 10가지 사용성 휴리스틱 체크리스트
const nielsenHeuristics = {
    "1_visibility_of_system_status": {
        name: "시스템 상태의 가시성",
        checkpoints: [
            "로딩 인디케이터가 표시되는가?",
            "현재 페이지 위치를 알 수 있는가? (breadcrumbs)",
            "진행 상황이 표시되는가? (단계별 프로세스)",
            "시스템 응답을 적절히 피드백하는가?"
        ],
        examples: {
            good: "아마존의 주문 진행 단계 표시",
            bad: "로딩 없이 멈춘 것처럼 보이는 페이지"
        }
    },

    "2_match_system_real_world": {
        name: "시스템과 현실 세계의 일치",
        checkpoints: [
            "사용자에게 친숙한 용어를 사용하는가?",
            "논리적인 순서로 정보가 배치되었는가?",
            "현실 세계의 관습을 따르는가?",
            "업계 표준 아이콘을 사용하는가?"
        ],
        examples: {
            good: "휴지통 아이콘으로 삭제 표현",
            bad: "기술적 전문용어로만 설명된 에러 메시지"
        }
    },

    "3_user_control_freedom": {
        name: "사용자 제어와 자유",
        checkpoints: [
            "뒤로 가기 기능이 있는가?",
            "실행 취소 기능을 제공하는가?",
            "원치 않는 상황에서 빠져나올 수 있는가?",
            "사용자가 작업을 중단할 수 있는가?"
        ],
        examples: {
            good: "Gmail의 메일 전송 취소 기능",
            bad: "취소 불가능한 결제 프로세스"
        }
    },

    "4_consistency_standards": {
        name: "일관성과 표준",
        checkpoints: [
            "전체 사이트에서 일관된 디자인을 사용하는가?",
            "같은 기능은 같은 방식으로 동작하는가?",
            "플랫폼 규칙을 따르는가?",
            "용어 사용이 일관적인가?"
        ],
        examples: {
            good: "구글 제품군의 일관된 Material Design",
            bad: "페이지마다 다른 버튼 스타일"
        }
    },

    "5_error_prevention": {
        name: "에러 방지",
        checkpoints: [
            "사용자 입력을 검증하는가?",
            "중요한 작업에 확인 절차가 있는가?",
            "제약 조건을 명확히 안내하는가?",
            "실수하기 쉬운 요소를 제거했는가?"
        ],
        examples: {
            good: "비밀번호 강도 실시간 표시",
            bad: "제출 후에야 알 수 있는 입력 오류"
        }
    }
};

// 접근성 (WCAG 2.1) 체크리스트
const accessibilityChecklist = {
    perceivable: {
        name: "인식 가능성",
        guidelines: [
            {
                criterion: "1.1.1 비텍스트 콘텐츠",
                check: "모든 이미지에 적절한 대체 텍스트가 있는가?",
                test: "스크린 리더로 이미지 설명 확인"
            },
            {
                criterion: "1.4.3 색상 대비",
                check: "텍스트와 배경의 명도 대비가 4.5:1 이상인가?",
                test: "컬러 대비 분석 도구 사용"
            },
            {
                criterion: "1.4.4 텍스트 크기 조정",
                check: "200% 확대 시에도 가독성이 유지되는가?",
                test: "브라우저 확대 기능으로 테스트"
            }
        ]
    },

    operable: {
        name: "운용 가능성",
        guidelines: [
            {
                criterion: "2.1.1 키보드 접근",
                check: "키보드만으로 모든 기능을 사용할 수 있는가?",
                test: "Tab 키로 전체 페이지 내비게이션 테스트"
            },
            {
                criterion: "2.4.3 포커스 순서",
                check: "논리적인 순서로 포커스가 이동하는가?",
                test: "Tab 키 순서 확인"
            },
            {
                criterion: "2.4.7 포커스 표시",
                check: "현재 포커스된 요소가 명확히 표시되는가?",
                test: "키보드 내비게이션으로 포커스 확인"
            }
        ]
    },

    understandable: {
        name: "이해 가능성",
        guidelines: [
            {
                criterion: "3.1.1 페이지 언어",
                check: "HTML lang 속성이 설정되었는가?",
                test: "HTML 소스 코드 확인"
            },
            {
                criterion: "3.2.1 포커스 시 변화",
                check: "포커스만으로 예상치 못한 변화가 발생하지 않는가?",
                test: "폼 요소 포커스 테스트"
            },
            {
                criterion: "3.3.1 에러 식별",
                check: "에러가 명확히 식별되고 설명되는가?",
                test: "폼 검증 에러 메시지 확인"
            }
        ]
    },

    robust: {
        name: "견고성",
        guidelines: [
            {
                criterion: "4.1.1 구문 분석",
                check: "HTML이 유효한가?",
                test: "W3C HTML 유효성 검사기 사용"
            },
            {
                criterion: "4.1.2 이름, 역할, 값",
                check: "모든 UI 요소에 적절한 이름과 역할이 있는가?",
                test: "스크린 리더로 요소 정보 확인"
            }
        ]
    }
};

// 반응형 디자인 테스트 케이스
const responsiveTestCases = {
    breakpoints: {
        mobile_small: "320px",
        mobile_large: "414px", 
        tablet_portrait: "768px",
        tablet_landscape: "1024px",
        desktop: "1200px",
        desktop_large: "1440px"
    },

    test_scenarios: [
        {
            device: "iPhone SE (320px)",
            tests: [
                "모든 텍스트가 가독 가능한 크기인가?",
                "터치 타겟이 최소 44px 이상인가?",
                "가로 스크롤이 발생하지 않는가?",
                "핵심 기능에 쉽게 접근할 수 있는가?"
            ]
        },
        {
            device: "iPad (768px)",
            tests: [
                "데스크톱과 모바일 사이의 적절한 레이아웃인가?",
                "터치와 마우스 인터랙션 모두 지원하는가?",
                "콘텐츠가 적절히 재배치되었는가?",
                "내비게이션이 터치에 최적화되었는가?"
            ]
        },
        {
            device: "Desktop (1200px)",
            tests: [
                "화면 공간을 효율적으로 활용하는가?",
                "마우스 호버 상태가 명확한가?",
                "키보드 단축키를 지원하는가?",
                "다중 창 사용을 고려했는가?"
            ]
        }
    ]
};

// 성능 및 사용성 메트릭
const performanceMetrics = {
    core_web_vitals: {
        LCP: {
            name: "Largest Contentful Paint",
            good: "2.5초 이하",
            poor: "4초 이상",
            description: "가장 큰 콘텐츠 요소의 로딩 시간"
        },
        FID: {
            name: "First Input Delay", 
            good: "100ms 이하",
            poor: "300ms 이상",
            description: "첫 번째 사용자 상호작용까지의 지연 시간"
        },
        CLS: {
            name: "Cumulative Layout Shift",
            good: "0.1 이하",
            poor: "0.25 이상",
            description: "예상치 못한 레이아웃 변화의 누적"
        }
    },

    usability_metrics: {
        task_completion_rate: {
            excellent: "95% 이상",
            good: "85-94%",
            acceptable: "70-84%",
            poor: "70% 미만"
        },
        error_rate: {
            excellent: "5% 미만",
            good: "5-10%",
            acceptable: "10-15%",
            poor: "15% 이상"
        },
        user_satisfaction: {
            excellent: "4.5/5 이상",
            good: "4.0-4.4/5",
            acceptable: "3.5-3.9/5",
            poor: "3.5/5 미만"
        }
    }
};

// 사용자 테스트 시나리오
const userTestingScenarios = {
    task_based_testing: [
        {
            task: "신규 사용자 온보딩",
            scenario: "처음 방문한 사용자가 회원가입 후 첫 번째 주요 기능을 사용하기까지",
            success_criteria: [
                "10분 이내 회원가입 완료",
                "도움말 없이 핵심 기능 찾기",
                "에러 없이 첫 작업 완료"
            ],
            observation_points: [
                "혼란스러워하는 지점",
                "포기하려는 순간",
                "긍정적 반응 지점"
            ]
        },
        {
            task: "일반적인 업무 수행",
            scenario: "기존 사용자가 일상적인 작업을 수행하는 상황",
            success_criteria: [
                "예상 시간 내 작업 완료",
                "에러 최소화",
                "효율적인 워크플로우"
            ],
            observation_points: [
                "반복적인 불편 사항",
                "우회 경로 사용",
                "개선 제안 사항"
            ]
        }
    ],

    ab_testing_ideas: [
        {
            element: "CTA 버튼",
            variant_a: "기본 파란색 버튼",
            variant_b: "대비가 강한 주황색 버튼",
            hypothesis: "대비가 강한 색상이 클릭률을 높일 것",
            metric: "클릭률 (CTR)"
        },
        {
            element: "내비게이션 메뉴",
            variant_a: "수평 메뉴바",
            variant_b: "햄버거 메뉴",
            hypothesis: "모바일에서 햄버거 메뉴가 사용성을 높일 것",
            metric: "페이지 탐색 깊이"
        },
        {
            element: "폼 레이아웃",
            variant_a: "단일 컬럼 폼",
            variant_b: "다중 컬럼 폼",
            hypothesis: "단일 컬럼이 완성률을 높일 것",
            metric: "폼 완성률"
        }
    ]
};

// 테스트 실행 함수
function runUXReviewerTest() {
    console.log("=== UX Reviewer 에이전트 테스트 ===\n");
    
    console.log("🎯 UX 분석 테스트 케이스:");
    Object.keys(uxAnalysisTestCases).forEach((testName, index) => {
        const testCase = uxAnalysisTestCases[testName];
        console.log(`\n${index + 1}. ${testCase.type}`);
        console.log(`대상 사용자: ${testCase.targetUsers.join(", ")}`);
        console.log(`주요 작업: ${testCase.primaryTasks.slice(0, 3).join(", ")}...`);
        console.log(`예상 이슈: ${testCase.expectedIssues.join(", ")}`);
        console.log(`예상 UX 점수: ${testCase.uxScore}`);
        console.log("=".repeat(80));
    });
    
    console.log("\n📋 Nielsen 휴리스틱 체크포인트 (샘플):");
    Object.keys(nielsenHeuristics).slice(0, 3).forEach((heuristic) => {
        const info = nielsenHeuristics[heuristic];
        console.log(`\n${info.name}:`);
        console.log("체크포인트:", info.checkpoints.slice(0, 2).join(", "), "...");
        console.log(`좋은 예: ${info.examples.good}`);
    });
    
    console.log("\n♿ 접근성 테스트 가이드라인 (샘플):");
    Object.keys(accessibilityChecklist).slice(0, 2).forEach((category) => {
        const info = accessibilityChecklist[category];
        console.log(`\n${info.name}:`);
        info.guidelines.slice(0, 1).forEach(guideline => {
            console.log(`- ${guideline.criterion}: ${guideline.check}`);
        });
    });
    
    console.log("\n📱 반응형 디자인 테스트:");
    responsiveTestCases.test_scenarios.slice(0, 2).forEach((scenario) => {
        console.log(`\n${scenario.device}:`);
        console.log("테스트:", scenario.tests.slice(0, 2).join(", "), "...");
    });
    
    console.log("\n📊 성능 지표:");
    Object.keys(performanceMetrics.core_web_vitals).forEach((metric) => {
        const info = performanceMetrics.core_web_vitals[metric];
        console.log(`${info.name}: 좋음 ${info.good}, 나쁨 ${info.poor}`);
    });
    
    console.log("\n테스트 완료! 위 시나리오들을 UX Reviewer 에이전트에 입력하여 사용자 경험 분석을 확인하세요.");
}

// 검증 체크리스트
const validationChecklist = {
    분석완성도: [
        "사용자 여정이 명확히 매핑되었는가?",
        "Nielsen 휴리스틱이 체계적으로 검토되었는가?",
        "접근성 기준이 적절히 평가되었는가?",
        "반응형 디자인이 충분히 분석되었는가?"
    ],
    
    실행가능성: [
        "개선 제안이 구체적이고 실행 가능한가?",
        "우선순위가 명확히 설정되었는가?",
        "개발팀이 이해하기 쉬운 가이드인가?",
        "예산과 일정이 고려되었는가?"
    ],
    
    사용자중심성: [
        "실제 사용자 관점이 반영되었는가?",
        "다양한 사용자 그룹이 고려되었는가?",
        "접근성 요구사항이 포함되었는가?",
        "사용자 테스트 방안이 제시되었는가?"
    ],
    
    측정가능성: [
        "개선 효과를 측정할 지표가 있는가?",
        "A/B 테스트 계획이 구체적인가?",
        "정량적 목표가 설정되었는가?",
        "추적 가능한 메트릭이 정의되었는가?"
    ]
};

// 모듈 export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        uxAnalysisTestCases,
        nielsenHeuristics,
        accessibilityChecklist,
        responsiveTestCases,
        performanceMetrics,
        userTestingScenarios,
        validationChecklist,
        runUXReviewerTest
    };
}

// 브라우저 환경에서 사용
if (typeof window !== 'undefined') {
    window.UXReviewerTest = {
        uxAnalysisTestCases,
        nielsenHeuristics,
        accessibilityChecklist,
        responsiveTestCases,
        performanceMetrics,
        userTestingScenarios,
        validationChecklist,
        runUXReviewerTest
    };
}

// 테스트 실행
runUXReviewerTest();