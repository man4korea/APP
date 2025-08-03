// 📁 C:\xampp\htdocs\BPM\agents\code-reviewer-test.js
// Create at 2508031302 Ver1.00

/**
 * Code Reviewer 에이전트 테스트 파일
 * 목적: 코드 품질 검토 기능을 검증
 */

// 테스트용 코드 샘플들 (다양한 품질 수준)
const codeReviewTestCases = {
    // 테스트 케이스 1: 개선이 필요한 JavaScript 코드
    poorQualityJS: {
        code: `
        function calculateTotal(items) {
            var total = 0;
            for (var i = 0; i < items.length; i++) {
                if (items[i].price) {
                    total = total + items[i].price * items[i].quantity;
                }
            }
            return total;
        }
        `,
        expectedScore: "50-69",
        expectedGrade: "🟠 보통",
        issues: ["var 사용", "배열 메서드 미활용", "에러 처리 부족"]
    },

    // 테스트 케이스 2: 복잡도가 높은 함수
    complexFunction: {
        code: `
        function processUserData(users) {
            var result = [];
            for (var i = 0; i < users.length; i++) {
                if (users[i].active) {
                    if (users[i].age >= 18) {
                        if (users[i].email && users[i].email.includes('@')) {
                            if (users[i].subscription === 'premium') {
                                result.push({
                                    id: users[i].id,
                                    name: users[i].name,
                                    type: 'premium_adult'
                                });
                            } else {
                                result.push({
                                    id: users[i].id,
                                    name: users[i].name,
                                    type: 'basic_adult'
                                });
                            }
                        }
                    }
                }
            }
            return result;
        }
        `,
        expectedScore: "30-49",
        expectedGrade: "🔴 미흡",
        issues: ["높은 순환 복잡도", "깊은 중첩", "코드 중복", "가독성 부족"]
    },

    // 테스트 케이스 3: 성능 문제가 있는 코드
    performanceIssue: {
        code: `
        function findDuplicates(arr) {
            var duplicates = [];
            for (var i = 0; i < arr.length; i++) {
                for (var j = i + 1; j < arr.length; j++) {
                    if (arr[i] === arr[j]) {
                        if (duplicates.indexOf(arr[i]) === -1) {
                            duplicates.push(arr[i]);
                        }
                    }
                }
            }
            return duplicates;
        }
        `,
        expectedScore: "40-59",
        expectedGrade: "🔴 미흡",
        issues: ["O(n³) 시간 복잡도", "비효율적 알고리즘", "중복 검사 로직"]
    },

    // 테스트 케이스 4: 좋은 품질의 코드 (개선 예시)
    goodQualityJS: {
        code: `
        /**
         * 장바구니 아이템들의 총 금액을 계산합니다.
         * @param {Array<Object>} items - 계산할 아이템 배열
         * @param {number} items[].price - 아이템 가격
         * @param {number} items[].quantity - 아이템 수량
         * @returns {number} 총 금액
         */
        const calculateTotal = (items = []) => {
            if (!Array.isArray(items)) {
                throw new Error('Items must be an array');
            }
            
            return items
                .filter(item => 
                    item?.price > 0 && 
                    item?.quantity > 0 && 
                    typeof item.price === 'number' && 
                    typeof item.quantity === 'number'
                )
                .reduce((total, item) => total + (item.price * item.quantity), 0);
        };
        `,
        expectedScore: "85-95",
        expectedGrade: "🟢 우수",
        strengths: ["JSDoc 문서화", "에러 처리", "함수형 프로그래밍", "타입 검증"]
    },

    // 테스트 케이스 5: Python 코드 (다양한 언어 지원 테스트)
    pythonCode: {
        code: `
        def calculate_average(numbers):
            total = 0
            count = 0
            for num in numbers:
                total += num
                count += 1
            return total / count
        `,
        expectedScore: "60-75",
        expectedGrade: "🟡 양호",
        issues: ["ZeroDivisionError 처리 부족", "내장 함수 미활용", "타입 힌트 부족"]
    }
};

// 개선된 코드 예시들
const improvedCodeExamples = {
    calculateTotal: `
    /**
     * 장바구니 아이템들의 총 금액을 안전하게 계산합니다.
     * @param {Array<{price: number, quantity: number}>} items 
     * @returns {number} 총 금액
     */
    const calculateTotal = (items = []) => {
        if (!Array.isArray(items)) {
            console.warn('calculateTotal: items is not an array, returning 0');
            return 0;
        }
        
        return items
            .filter(item => 
                item?.price > 0 && 
                item?.quantity > 0 && 
                Number.isFinite(item.price) && 
                Number.isFinite(item.quantity)
            )
            .reduce((total, item) => total + (item.price * item.quantity), 0);
    };
    `,

    processUserData: `
    /**
     * 사용자 데이터를 필터링하고 변환합니다.
     */
    const processUserData = (users = []) => {
        const isValidUser = (user) => 
            user?.active && 
            user?.age >= 18 && 
            user?.email?.includes('@');
        
        const createUserRecord = (user) => ({
            id: user.id,
            name: user.name,
            type: user.subscription === 'premium' ? 'premium_adult' : 'basic_adult'
        });
        
        return users
            .filter(isValidUser)
            .map(createUserRecord);
    };
    `,

    findDuplicates: `
    /**
     * 배열에서 중복된 요소를 효율적으로 찾습니다.
     * 시간 복잡도: O(n)
     */
    const findDuplicates = (arr = []) => {
        const seen = new Set();
        const duplicates = new Set();
        
        for (const item of arr) {
            if (seen.has(item)) {
                duplicates.add(item);
            } else {
                seen.add(item);
            }
        }
        
        return Array.from(duplicates);
    };
    `,

    calculateAverage: `
    def calculate_average(numbers: list[float]) -> float:
        """
        숫자 리스트의 평균을 계산합니다.
        
        Args:
            numbers: 평균을 계산할 숫자 리스트
            
        Returns:
            float: 평균값
            
        Raises:
            ValueError: 빈 리스트이거나 유효하지 않은 입력인 경우
        """
        if not numbers:
            raise ValueError("Cannot calculate average of empty list")
        
        if not all(isinstance(n, (int, float)) for n in numbers):
            raise ValueError("All elements must be numbers")
        
        return sum(numbers) / len(numbers)
    `
};

// 코드 품질 평가 기준
const qualityMetrics = {
    readability: {
        weight: 30,
        criteria: [
            "변수/함수명이 의미가 명확한가?",
            "코드 구조가 논리적인가?",
            "적절한 주석이 있는가?",
            "들여쓰기와 공백이 일관적인가?",
            "함수가 적절한 크기인가?"
        ]
    },
    
    performance: {
        weight: 25,
        criteria: [
            "시간 복잡도가 효율적인가?",
            "메모리 사용량이 적절한가?",
            "불필요한 연산이 없는가?",
            "적절한 자료구조를 사용했는가?",
            "최적화 여지가 있는가?"
        ]
    },
    
    maintainability: {
        weight: 25,
        criteria: [
            "함수가 단일 책임을 가지는가?",
            "재사용 가능한 구조인가?",
            "확장하기 쉬운 구조인가?",
            "의존성이 적절한가?",
            "테스트하기 쉬운 구조인가?"
        ]
    },
    
    standards: {
        weight: 20,
        criteria: [
            "코딩 컨벤션을 준수하는가?",
            "적절한 디자인 패턴을 사용했는가?",
            "에러 처리가 되어 있는가?",
            "타입 안정성이 고려되었는가?",
            "문서화가 되어 있는가?"
        ]
    }
};

// 개선 우선순위 가이드라인
const improvementPriorities = {
    high: [
        "기능적 버그나 에러",
        "보안 취약점",
        "성능 병목 지점",
        "메모리 누수 위험",
        "타입 에러 가능성"
    ],
    
    medium: [
        "가독성 개선",
        "코드 중복 제거",
        "함수 분리",
        "명명 규칙 개선",
        "주석 추가"
    ],
    
    low: [
        "코드 스타일 통일",
        "더 나은 알고리즘 적용",
        "추가적인 최적화",
        "더 나은 디자인 패턴",
        "확장성 고려"
    ]
};

// 테스트 실행 함수
function runCodeReviewerTest() {
    console.log("=== Code Reviewer 에이전트 테스트 ===\n");
    
    console.log("📝 코드 리뷰 테스트 케이스:");
    Object.keys(codeReviewTestCases).forEach((testName, index) => {
        const testCase = codeReviewTestCases[testName];
        console.log(`\n${index + 1}. ${testName}`);
        console.log(`예상 점수: ${testCase.expectedScore}`);
        console.log(`예상 등급: ${testCase.expectedGrade}`);
        
        if (testCase.issues) {
            console.log("주요 이슈:", testCase.issues.join(", "));
        }
        
        if (testCase.strengths) {
            console.log("강점:", testCase.strengths.join(", "));
        }
        
        console.log("코드:", testCase.code);
        console.log("=".repeat(80));
    });
    
    console.log("\n🚀 개선된 코드 예시:");
    Object.keys(improvedCodeExamples).forEach((funcName) => {
        console.log(`\n📋 ${funcName}:`);
        console.log(improvedCodeExamples[funcName]);
    });
    
    console.log("\n📊 품질 평가 기준:");
    Object.keys(qualityMetrics).forEach((category) => {
        const metric = qualityMetrics[category];
        console.log(`\n${category.toUpperCase()} (가중치: ${metric.weight}%):`);
        metric.criteria.forEach(criterion => console.log(`  • ${criterion}`));
    });
    
    console.log("\n테스트 완료! 위 코드들을 Code Reviewer 에이전트에 입력하여 점수와 개선사항을 확인하세요.");
}

// 검증 체크리스트
const validationChecklist = {
    점수정확성: [
        "100점 만점 기준으로 점수가 산출되는가?",
        "4개 카테고리별 가중치가 적용되는가?",
        "등급 분류가 정확한가? (🟢🟡🟠🔴)",
        "점수와 등급이 일치하는가?"
    ],
    
    분석정확성: [
        "카테고리별 분석이 구체적인가?",
        "좋은 점과 개선점이 명확히 구분되는가?",
        "실제 코드 이슈가 정확히 식별되는가?",
        "개선 방안이 실행 가능한가?"
    ],
    
    코드개선품질: [
        "리팩토링된 코드가 기능적으로 동일한가?",
        "성능이 실제로 개선되었는가?",
        "가독성이 향상되었는가?",
        "베스트 프랙티스가 적용되었는가?"
    ],
    
    교육적가치: [
        "개선 이유가 명확히 설명되었는가?",
        "우선순위가 적절히 분류되었는가?",
        "초보자도 이해할 수 있는 설명인가?",
        "추가 학습 자료가 제공되었는가?"
    ]
};

// 모듈 export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        codeReviewTestCases,
        improvedCodeExamples,
        qualityMetrics,
        improvementPriorities,
        validationChecklist,
        runCodeReviewerTest
    };
}

// 브라우저 환경에서 사용
if (typeof window !== 'undefined') {
    window.CodeReviewerTest = {
        codeReviewTestCases,
        improvedCodeExamples,
        qualityMetrics,
        improvementPriorities,
        validationChecklist,
        runCodeReviewerTest
    };
}

// 테스트 실행
runCodeReviewerTest();