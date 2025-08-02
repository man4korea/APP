// 📁 C:\xampp\htdocs\BPM\scripts\load-guides.js
// Create at 2508022008 Ver1.00

/**
 * BPM 프로젝트 지침서 동적 로딩 유틸리티
 * Claude Code와 Claude Desktop에서 사용 가능
 */

const fs = require('fs').promises;
const path = require('path');

class GuideLoader {
    constructor(projectPath = 'C:\\xampp\\htdocs\\BPM') {
        this.projectPath = projectPath;
        this.guides = {
            common: null,
            claudeCode: null,
            claudeDesktop: null,
            tasks: null
        };
    }

    /**
     * 모든 지침서를 동적으로 로딩
     */
    async loadAllGuides() {
        try {
            console.log('📚 BPM 프로젝트 지침서 로딩 시작...');

            // 공통 지침서 로딩
            this.guides.common = await this.loadGuide('BPM_PROJECT_GUIDE.md');
            console.log('✅ 공통 지침서 로딩 완료');

            // Claude Code 전용 지침서 로딩
            this.guides.claudeCode = await this.loadGuide('BPM_CLAUDE_CODE_GUIDE.md');
            console.log('✅ Claude Code 지침서 로딩 완료');

            // Claude Desktop 전용 지침서 로딩
            this.guides.claudeDesktop = await this.loadGuide('BPM_CLAUDE_DESKTOP_GUIDE.md');
            console.log('✅ Claude Desktop 지침서 로딩 완료');

            // SHRIMP 작업 관리 로딩
            this.guides.tasks = await this.loadGuide('SHRIMP_Tasks.md');
            console.log('✅ SHRIMP 작업 관리 로딩 완료');

            console.log('🎉 모든 지침서 로딩 완료!\n');
            
            return this.guides;
        } catch (error) {
            console.error('❌ 지침서 로딩 실패:', error.message);
            return null;
        }
    }

    /**
     * 개별 지침서 파일 로딩
     * @param {string} filename - 로딩할 파일명
     */
    async loadGuide(filename) {
        const filePath = path.join(this.projectPath, filename);
        try {
            const content = await fs.readFile(filePath, 'utf8');
            return {
                filename,
                content,
                lastModified: (await fs.stat(filePath)).mtime,
                size: content.length
            };
        } catch (error) {
            console.warn(`⚠️ ${filename} 로딩 실패:`, error.message);
            return null;
        }
    }

    /**
     * 환경별 지침서 가져오기
     * @param {string} environment - 'code' | 'desktop' | 'common'
     */
    getGuideForEnvironment(environment) {
        const envMap = {
            'code': 'claudeCode',
            'desktop': 'claudeDesktop', 
            'common': 'common'
        };

        const guideKey = envMap[environment];
        if (!guideKey || !this.guides[guideKey]) {
            console.error(`❌ ${environment} 환경의 지침서를 찾을 수 없습니다.`);
            return null;
        }

        return this.guides[guideKey];
    }

    /**
     * 현재 작업 상황 요약
     */
    getTaskSummary() {
        if (!this.guides.tasks) {
            return '❌ 작업 관리 정보를 로딩할 수 없습니다.';
        }

        const content = this.guides.tasks.content;
        
        // 간단한 작업 상태 파싱 (실제 SHRIMP_Tasks.md 구조에 맞게 조정 필요)
        const pendingTasks = (content.match(/⏳/g) || []).length;
        const inProgressTasks = (content.match(/🟡/g) || []).length;
        const completedTasks = (content.match(/✅/g) || []).length;
        const blockedTasks = (content.match(/🔴/g) || []).length;

        return `
📊 현재 작업 현황:
- ✅ 완료: ${completedTasks}개
- 🟡 진행중: ${inProgressTasks}개  
- ⏳ 대기중: ${pendingTasks}개
- 🔴 차단: ${blockedTasks}개
        `.trim();
    }

    /**
     * 모듈별 색상 테마 정보 추출
     */
    getColorThemes() {
        if (!this.guides.common) {
            return null;
        }

        // 공통 지침서에서 색상 테마 정보 추출
        const content = this.guides.common.content;
        const colorSection = content.match(/## 🌈 모듈별 무지개 색상 테마([\s\S]*?)---/);
        
        if (colorSection) {
            return colorSection[1].trim();
        }

        return '❌ 색상 테마 정보를 찾을 수 없습니다.';
    }

    /**
     * 지침서 정보 출력
     */
    printGuideInfo() {
        console.log('\n📋 로딩된 지침서 정보:');
        
        Object.entries(this.guides).forEach(([key, guide]) => {
            if (guide) {
                console.log(`✅ ${key}: ${guide.filename} (${Math.round(guide.size/1024)}KB)`);
                console.log(`   최종 수정: ${guide.lastModified.toLocaleString('ko-KR')}`);
            } else {
                console.log(`❌ ${key}: 로딩 실패`);
            }
        });
        
        console.log('\n' + this.getTaskSummary());
        console.log('\n');
    }

    /**
     * 지침서 새로고침 (파일 변경 감지)
     */
    async refreshGuides() {
        console.log('🔄 지침서 새로고침 중...');
        await this.loadAllGuides();
        this.printGuideInfo();
    }
}

// Claude Code에서 사용할 수 있는 전역 함수들
global.loadProjectGuides = async function() {
    const loader = new GuideLoader();
    const guides = await loader.loadAllGuides();
    
    if (guides) {
        loader.printGuideInfo();
        
        // 사용 예시 출력
        console.log('📖 사용법:');
        console.log('- 공통 지침: guides.common.content');
        console.log('- Code 지침: guides.claudeCode.content');
        console.log('- Desktop 지침: guides.claudeDesktop.content');
        console.log('- 작업 관리: guides.tasks.content');
        console.log('- 색상 테마: loader.getColorThemes()');
        console.log('- 작업 현황: loader.getTaskSummary()');
    }
    
    return { guides, loader };
};

// 빠른 작업 현황 확인 함수
global.checkTaskStatus = async function() {
    const loader = new GuideLoader();
    await loader.loadGuide('SHRIMP_Tasks.md');
    console.log(loader.getTaskSummary());
};

// 색상 테마 빠른 확인 함수
global.getModuleColors = async function() {
    const loader = new GuideLoader();
    await loader.loadGuide('BPM_PROJECT_GUIDE.md');
    console.log('\n🌈 모듈별 색상 테마:');
    console.log(loader.getColorThemes());
};

module.exports = GuideLoader;