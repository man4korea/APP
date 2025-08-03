// 📁 C:\xampp\htdocs\BPM\agents\code-simplifier-test.js
// Create at 2508031247 Ver1.00

/**
 * Code Simplifier 에이전트 테스트 파일
 * 목적: 에이전트의 코드 단순화 기능을 검증
 */

// 테스트용 복잡한 코드 샘플들
const testCases = {
    // 테스트 케이스 1: 복잡한 체이닝
    complexChaining: `const processData = (data) => data.filter(item => item.active && item.score > 80).map(item => ({...item, grade: item.score >= 90 ? 'A' : 'B'})).sort((a, b) => b.score - a.score);`,
    
    // 테스트 케이스 2: 중첩된 조건문
    nestedConditions: `function checkUser(user) {
        if (user) {
            if (user.age >= 18) {
                if (user.verified) {
                    if (user.subscription === 'premium') {
                        return user.permissions.includes('admin') ? 'full_access' : 'limited_access';
                    } else {
                        return 'basic_access';
                    }
                } else {
                    return 'verification_required';
                }
            } else {
                return 'age_restricted';
            }
        } else {
            return 'no_user';
        }
    }`,
    
    // 테스트 케이스 3: 복잡한 배열 처리
    complexArrayProcessing: `const result = items.reduce((acc, item) => { const key = item.category; if (!acc[key]) acc[key] = []; acc[key].push({...item, processed: true}); return acc; }, {});`,
    
    // 테스트 케이스 4: 함수형 프로그래밍 패턴
    functionalPattern: `const pipeline = (...fns) => (value) => fns.reduce((acc, fn) => fn(acc), value);
    const transform = pipeline(
        x => x.filter(Boolean),
        x => x.map(item => item.toString().toLowerCase()),
        x => x.sort(),
        x => [...new Set(x)]
    );`
};

// 예상되는 단순화 결과 (참조용)
const expectedSimplifications = {
    complexChaining: {
        description: "한 줄 체이닝을 3단계로 분해",
        steps: ["필터링", "매핑 및 등급 추가", "정렬"]
    },
    
    nestedConditions: {
        description: "중첩된 if문을 guard clause 패턴으로 변경",
        steps: ["조기 반환 패턴", "단계별 검증", "명확한 조건 분리"]
    },
    
    complexArrayProcessing: {
        description: "reduce를 forEach와 객체 초기화로 분해",
        steps: ["빈 결과 객체 생성", "각 항목별 처리", "카테고리별 그룹화"]
    },
    
    functionalPattern: {
        description: "파이프라인을 명시적인 단계별 함수로 분해",
        steps: ["각 변환 단계 개별 함수화", "순차적 호출", "중간 결과 확인 가능"]
    }
};

// 테스트 실행 함수
function runCodeSimplifierTest() {
    console.log("=== Code Simplifier 에이전트 테스트 ===\n");
    
    Object.keys(testCases).forEach((testName, index) => {
        console.log(`테스트 ${index + 1}: ${testName}`);
        console.log("원본 코드:");
        console.log(testCases[testName]);
        console.log("\n예상 단순화:");
        console.log(expectedSimplifications[testName]);
        console.log("=".repeat(50) + "\n");
    });
    
    console.log("테스트 완료! 위 코드들을 Code Simplifier 에이전트에 입력하여 결과를 확인하세요.");
}

// 에이전트 검증 체크리스트
const validationChecklist = {
    기능검증: [
        "원본 코드의 모든 기능이 유지되는가?",
        "변수명과 함수명이 더 직관적인가?",
        "복잡한 로직이 단계별로 분해되었는가?",
        "각 단계에 상세한 주석이 있는가?"
    ],
    
    가독성검증: [
        "초보자가 이해하기 쉬운가?",
        "코드의 흐름이 명확한가?",
        "중간 결과를 확인할 수 있는가?",
        "디버깅이 용이한가?"
    ],
    
    교육적가치: [
        "학습 포인트가 명확한가?",
        "관련 개념 설명이 포함되어 있는가?",
        "개선 전후 비교가 명확한가?",
        "추가 학습 자료가 제공되는가?"
    ]
};

// 모듈 export (Node.js 환경에서 사용시)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        testCases,
        expectedSimplifications,
        validationChecklist,
        runCodeSimplifierTest
    };
}

// 브라우저 환경에서 직접 실행
if (typeof window !== 'undefined') {
    window.CodeSimplifierTest = {
        testCases,
        expectedSimplifications,
        validationChecklist,
        runCodeSimplifierTest
    };
}

// 테스트 실행
runCodeSimplifierTest();