// 📁 C:\xampp\htdocs\BPM\agents\security-reviewer-test.js
// Create at 2508031254 Ver1.00

/**
 * Security Reviewer 에이전트 테스트 파일
 * 목적: 보안 취약점 분석 기능을 검증
 */

// 테스트용 취약한 코드 샘플들
const securityTestCases = {
    // 테스트 케이스 1: SQL 인젝션 취약점
    sqlInjection: {
        code: `
        // 취약한 코드 - SQL 인젝션 위험
        app.get('/user/:id', (req, res) => {
            const query = \`SELECT * FROM users WHERE id = \${req.params.id}\`;
            db.query(query, (err, results) => {
                res.json(results);
            });
        });
        `,
        vulnerability: "SQL Injection",
        risk_level: "🔴 Critical"
    },

    // 테스트 케이스 2: XSS 취약점
    xssVulnerability: {
        code: `
        // 취약한 코드 - XSS 공격 가능
        app.post('/comment', (req, res) => {
            const comment = req.body.comment;
            res.send(\`<div>댓글: \${comment}</div>\`);
        });
        `,
        vulnerability: "Cross-Site Scripting (XSS)",
        risk_level: "🔴 Critical"
    },

    // 테스트 케이스 3: 인증 우회
    authBypass: {
        code: `
        // 취약한 코드 - 인증 우회 가능
        app.get('/admin', (req, res) => {
            if (req.query.debug === 'true') {
                return res.json({ admin: true, users: getAllUsers() });
            }
            // 정상적인 인증 로직...
        });
        `,
        vulnerability: "Authentication Bypass",
        risk_level: "🔴 Critical"
    },

    // 테스트 케이스 4: 민감정보 노출
    informationDisclosure: {
        code: `
        // 취약한 코드 - 민감정보 노출
        app.get('/api/error', (req, res) => {
            try {
                // 어떤 작업 수행
            } catch (error) {
                res.status(500).json({
                    error: error.message,
                    stack: error.stack,
                    env: process.env
                });
            }
        });
        `,
        vulnerability: "Information Disclosure",
        risk_level: "🟠 High"
    },

    // 테스트 케이스 5: 약한 암호화
    weakCrypto: {
        code: `
        // 취약한 코드 - 약한 암호화
        const crypto = require('crypto');
        
        function hashPassword(password) {
            return crypto.createHash('md5').update(password).digest('hex');
        }
        
        const userPassword = hashPassword('password123');
        `,
        vulnerability: "Weak Cryptographic Hash",
        risk_level: "🟡 Medium"
    }
};

// 테스트용 취약한 환경 설정 파일들
const configTestCases = {
    // 취약한 .env 파일
    vulnerableEnv: `
# 🔴 취약한 .env 파일 예시
DATABASE_PASSWORD=123456
API_KEY=sk-1234567890abcdef
JWT_SECRET=secret
DEBUG=true
CORS_ORIGIN=*
SSL_DISABLED=true
    `,

    // 취약한 데이터베이스 설정
    vulnerableDbConfig: `
{
    "database": {
        "host": "localhost",
        "user": "root",
        "password": "",
        "ssl": false,
        "allowPublicKeyRetrieval": true
    }
}
    `,

    // 취약한 CORS 설정
    vulnerableCors: `
app.use(cors({
    origin: '*',
    credentials: true,
    methods: ['GET', 'POST', 'PUT', 'DELETE'],
    allowedHeaders: ['*']
}));
    `
};

// 보안 체크리스트 템플릿
const securityChecklist = {
    환경설정보안: [
        "✅ .env 파일이 .gitignore에 포함되어 있는가?",
        "✅ API 키와 비밀번호가 강력한가?",
        "✅ DEBUG 모드가 프로덕션에서 비활성화되어 있는가?",
        "✅ HTTPS가 강제로 사용되고 있는가?",
        "✅ 보안 헤더가 설정되어 있는가?"
    ],
    
    인증인가시스템: [
        "✅ 세션 타임아웃이 설정되어 있는가?",
        "✅ 강력한 비밀번호 정책이 적용되어 있는가?",
        "✅ JWT 토큰에 적절한 만료시간이 설정되어 있는가?",
        "✅ 권한 검증이 모든 API 엔드포인트에 적용되어 있는가?",
        "✅ 다중 인증(2FA)이 지원되는가?"
    ],
    
    데이터보호: [
        "✅ 민감한 데이터가 암호화되어 저장되는가?",
        "✅ 데이터베이스 연결이 SSL로 암호화되어 있는가?",
        "✅ 개인정보가 로그에 기록되지 않는가?",
        "✅ 데이터 백업이 암호화되어 있는가?",
        "✅ GDPR/개인정보보호법 준수 체계가 있는가?"
    ],
    
    코드보안: [
        "✅ SQL 쿼리가 매개변수화되어 있는가?",
        "✅ 사용자 입력이 검증 및 sanitize되는가?",
        "✅ CSRF 토큰이 사용되고 있는가?",
        "✅ 파일 업로드에 보안 검증이 있는가?",
        "✅ 의존성 라이브러리가 최신 버전인가?"
    ],
    
    API보안: [
        "✅ API 속도 제한(Rate Limiting)이 적용되어 있는가?",
        "✅ API 키 또는 인증 토큰이 필요한가?",
        "✅ CORS 정책이 적절히 설정되어 있는가?",
        "✅ API 응답에서 민감한 정보가 노출되지 않는가?",
        "✅ API 버전 관리가 되고 있는가?"
    ]
};

// 보안 개선 권장사항
const securityRecommendations = {
    즉시개선: [
        "SQL 인젝션 취약점 수정 (매개변수화된 쿼리 사용)",
        "XSS 방지를 위한 입력 검증 및 출력 인코딩",
        "강력한 암호화 알고리즘 사용 (bcrypt, scrypt 등)",
        "민감한 정보 하드코딩 제거"
    ],
    
    보안강화: [
        "HTTPS 강제 사용 및 HSTS 헤더 추가",
        "보안 헤더 설정 (CSP, X-Frame-Options 등)",
        "API 속도 제한 및 모니터링 구현",
        "보안 로깅 및 모니터링 시스템 구축"
    ],
    
    장기개선: [
        "정기적인 보안 감사 및 테스트",
        "보안 교육 및 가이드라인 수립",
        "자동화된 보안 스캔 도구 도입",
        "인시던트 대응 계획 수립"
    ]
};

// 보안 도구 추천
const securityTools = {
    정적분석: [
        "ESLint Security Plugin",
        "Semgrep",
        "SonarQube",
        "CodeQL"
    ],
    
    의존성스캔: [
        "npm audit",
        "Snyk",
        "OWASP Dependency Check",
        "GitHub Dependabot"
    ],
    
    동적분석: [
        "OWASP ZAP",
        "Burp Suite",
        "Nmap",
        "SQLMap"
    ],
    
    보안모니터링: [
        "Fail2ban",
        "OSSEC",
        "Suricata", 
        "Elastic Security"
    ]
};

// 테스트 실행 함수
function runSecurityReviewerTest() {
    console.log("=== Security Reviewer 에이전트 테스트 ===\n");
    
    console.log("🔍 코드 보안 테스트 케이스:");
    Object.keys(securityTestCases).forEach((testName, index) => {
        const testCase = securityTestCases[testName];
        console.log(`\n${index + 1}. ${testName}`);
        console.log(`취약점: ${testCase.vulnerability}`);
        console.log(`위험도: ${testCase.risk_level}`);
        console.log("코드:", testCase.code);
        console.log("=".repeat(60));
    });
    
    console.log("\n🛠️ 환경 설정 테스트 케이스:");
    Object.keys(configTestCases).forEach((configName) => {
        console.log(`\n${configName}:`);
        console.log(configTestCases[configName]);
    });
    
    console.log("\n✅ 보안 체크리스트:");
    Object.keys(securityChecklist).forEach((category) => {
        console.log(`\n📋 ${category}:`);
        securityChecklist[category].forEach(item => console.log(`  ${item}`));
    });
    
    console.log("\n테스트 완료! 위 케이스들을 Security Reviewer 에이전트에 입력하여 결과를 확인하세요.");
}

// 검증 가이드라인
const validationGuidelines = {
    취약점탐지정확도: [
        "모든 SQL 인젝션 취약점이 탐지되는가?",
        "XSS 취약점이 정확히 식별되는가?",
        "인증 우회 로직이 발견되는가?",
        "민감정보 노출 위험이 식별되는가?"
    ],
    
    위험도평가정확성: [
        "Critical/High/Medium/Low 분류가 적절한가?",
        "비즈니스 임팩트가 고려되었는가?",
        "기술적 복잡도가 반영되었는가?",
        "수정 우선순위가 명확한가?"
    ],
    
    개선방안실용성: [
        "구체적이고 실행 가능한 해결책인가?",
        "단계별 구현 가이드가 제공되는가?",
        "대안적 접근법이 제시되는가?",
        "비용과 효과가 고려되었는가?"
    ]
};

// 모듈 export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        securityTestCases,
        configTestCases,
        securityChecklist,
        securityRecommendations,
        securityTools,
        validationGuidelines,
        runSecurityReviewerTest
    };
}

// 브라우저 환경에서 사용
if (typeof window !== 'undefined') {
    window.SecurityReviewerTest = {
        securityTestCases,
        configTestCases,
        securityChecklist,
        securityRecommendations,
        securityTools,
        validationGuidelines,
        runSecurityReviewerTest
    };
}

// 테스트 실행
runSecurityReviewerTest();