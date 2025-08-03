// 📁 C:\xampp\htdocs\BPM\agents\register-agents.js
// Create at 2508031340 Ver1.00

/**
 * Claude Code 에이전트 등록 스크립트
 * 목적: 5개의 전문 에이전트를 Claude Code에서 사용할 수 있도록 등록
 */

const fs = require('fs');
const path = require('path');

// 에이전트 정보 정의
const agents = {
    'code-simplifier': {
        name: 'Code Simplifier',
        description: '복잡한 코드를 초보자도 이해할 수 있게 간단하게 변환',
        promptFile: 'code-simplifier-system-prompt.md',
        configFile: 'code-simplifier-config.json',
        testFile: 'code-simplifier-test.js'
    },
    'security-reviewer': {
        name: 'Security Reviewer', 
        description: '보안 취약점 분석 및 데이터 보호 방안 제시',
        promptFile: 'security-reviewer-system-prompt.md',
        configFile: 'security-reviewer-config.json',
        testFile: 'security-reviewer-test.js'
    },
    'code-reviewer': {
        name: 'Code Reviewer',
        description: '코드 품질, 성능, 유지보수성 종합 검토',
        promptFile: 'code-reviewer-system-prompt.md',
        configFile: 'code-reviewer-config.json', 
        testFile: 'code-reviewer-test.js'
    },
    'tech-lead': {
        name: 'Tech Lead',
        description: '시스템 아키텍처 설계 및 기술적 의사결정',
        promptFile: 'tech-lead-system-prompt.md',
        configFile: 'tech-lead-config.json',
        testFile: 'tech-lead-test.js'
    },
    'ux-reviewer': {
        name: 'UX Reviewer',
        description: '사용자 경험 및 인터페이스 전문 분석',
        promptFile: 'ux-reviewer-system-prompt.md',
        configFile: 'ux-reviewer-config.json',
        testFile: 'ux-reviewer-test.js'
    }
};

// 에이전트 등록 함수
function registerAgents() {
    console.log('=== BPM 프로젝트 전문 에이전트 등록 ===\n');
    
    const agentDir = __dirname;
    const registeredAgents = [];
    
    Object.keys(agents).forEach((agentId, index) => {
        const agent = agents[agentId];
        
        // 파일 존재 확인
        const promptPath = path.join(agentDir, agent.promptFile);
        const configPath = path.join(agentDir, agent.configFile);
        const testPath = path.join(agentDir, agent.testFile);
        
        const status = {
            id: agentId,
            name: agent.name,
            description: agent.description,
            files: {
                prompt: fs.existsSync(promptPath) ? '✅' : '❌',
                config: fs.existsSync(configPath) ? '✅' : '❌', 
                test: fs.existsSync(testPath) ? '✅' : '❌'
            }
        };
        
        registeredAgents.push(status);
        
        console.log(`${index + 1}. ${agent.name}`);
        console.log(`   ID: ${agentId}`);
        console.log(`   설명: ${agent.description}`);
        console.log(`   파일 상태: 프롬프트 ${status.files.prompt} | 설정 ${status.files.config} | 테스트 ${status.files.test}`);
        console.log('');
    });
    
    // 등록 완료 요약
    const totalAgents = registeredAgents.length;
    const completeAgents = registeredAgents.filter(agent => 
        agent.files.prompt === '✅' && 
        agent.files.config === '✅' && 
        agent.files.test === '✅'
    ).length;
    
    console.log('='.repeat(60));
    console.log(`📊 등록 완료: ${completeAgents}/${totalAgents}개 에이전트`);
    console.log('');
    
    if (completeAgents === totalAgents) {
        console.log('🎉 모든 에이전트가 성공적으로 등록되었습니다!');
        console.log('');
        console.log('📋 사용 방법:');
        console.log('1. Claude Code에서 `/agents` 명령어로 에이전트 목록 확인');
        console.log('2. 각 에이전트의 system-prompt.md를 Claude 웹에서 복사하여 에이전트 생성');
        console.log('3. config.json의 설정을 참고하여 도구 활성화');
        console.log('4. test.js 파일로 에이전트 기능 검증');
    } else {
        console.log('⚠️ 일부 에이전트 파일이 누락되었습니다.');
        console.log('누락된 파일을 확인하고 재생성해주세요.');
    }
    
    return registeredAgents;
}

// Claude Code 에이전트 사용 가이드
function showUsageGuide() {
    console.log('\n📖 Claude Code 에이전트 사용 가이드\n');
    
    console.log('🔧 에이전트 활용 방법:');
    console.log('1. 복잡한 코드 → code-simplifier');
    console.log('2. 보안 검토 → security-reviewer');
    console.log('3. 코드 품질 → code-reviewer');
    console.log('4. 아키텍처 → tech-lead');
    console.log('5. 사용자 경험 → ux-reviewer');
    console.log('');
    
    console.log('💡 워크플로우 예시:');
    console.log('1. tech-lead로 전체 아키텍처 설계');
    console.log('2. code-reviewer로 코드 품질 검토');
    console.log('3. security-reviewer로 보안 점검');
    console.log('4. ux-reviewer로 사용자 경험 개선');
    console.log('5. code-simplifier로 복잡한 코드 단순화');
    console.log('');
    
    console.log('🚀 각 에이전트별 테스트:');
    console.log('node agents/[agent-name]-test.js');
}

// 스크립트 실행
if (require.main === module) {
    const registeredAgents = registerAgents();
    showUsageGuide();
    
    // 결과를 JSON 파일로 저장
    const registrationResult = {
        timestamp: new Date().toISOString(),
        totalAgents: registeredAgents.length,
        completedAgents: registeredAgents.filter(agent => 
            agent.files.prompt === '✅' && 
            agent.files.config === '✅' && 
            agent.files.test === '✅'
        ).length,
        agents: registeredAgents
    };
    
    try {
        fs.writeFileSync(
            path.join(__dirname, 'registration-status.json'), 
            JSON.stringify(registrationResult, null, 2)
        );
        console.log('📁 등록 상태가 registration-status.json에 저장되었습니다.');
    } catch (error) {
        console.error('❌ 등록 상태 저장 실패:', error.message);
    }
}

module.exports = {
    registerAgents,
    showUsageGuide,
    agents
};