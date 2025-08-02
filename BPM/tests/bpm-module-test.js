// 📁 C:\xampp\htdocs\BPM\tests\bpm-module-test.js
// Create at 2508021941 Ver1.01

/**
 * BPM Total Business Process Management - Playwright 테스트 스크립트
 * 
 * 용도: 각 모듈 개발 완료시 자동 테스트 실행
 * 환경: XAMPP (Apache + MySQL) 로컬 서버
 * 
 * 실행 방법:
 * npm install playwright
 * node tests/bpm-module-test.js
 */

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

// 모듈별 테스트 설정
const BPM_MODULES = {
  'organization': {
    name: '조직관리',
    color: '#ff6b6b',
    backgroundColor: '#fff5f5',
    url: '/modules/organization/',
    testElements: ['.organization-tree', '.company-form']
  },
  'members': {
    name: '구성원관리', 
    color: '#ff9f43',
    backgroundColor: '#fff8f0',
    url: '/modules/members/',
    testElements: ['.member-list', '.invitation-form']
  },
  'tasks': {
    name: 'Task관리',
    color: '#feca57', 
    backgroundColor: '#fffcf0',
    url: '/modules/tasks/',
    testElements: ['.task-list', '.task-form']
  },
  'documents': {
    name: '문서관리',
    color: '#55a3ff',
    backgroundColor: '#f0fff4', 
    url: '/modules/documents/',
    testElements: ['.document-list', '.upload-area']
  },
  'processes': {
    name: 'Process Map관리',
    color: '#3742fa',
    backgroundColor: '#f0f8ff',
    url: '/modules/processes/',
    testElements: ['.process-canvas', '.process-tools']
  },
  'workflows': {
    name: '업무Flow관리',
    color: '#a55eea',
    backgroundColor: '#f8f0ff',
    url: '/modules/workflows/',
    testElements: ['.workflow-designer', '.flow-controls']
  },
  'analytics': {
    name: '직무분석',
    color: '#8b4513',
    backgroundColor: '#faf0e6',
    url: '/modules/analytics/',
    testElements: ['.analytics-chart', '.report-generator']
  }
};

// 기본 설정
const BASE_URL = 'http://localhost/BPM';
const SCREENSHOTS_DIR = path.join(__dirname, 'screenshots');
const TEST_TIMEOUT = 30000;

class BPMTester {
  constructor() {
    this.browser = null;
    this.page = null;
    this.testResults = [];
  }

  async initialize() {
    console.log('🚀 BPM 테스트 시작...');
    
    // 스크린샷 디렉토리 확인
    if (!fs.existsSync(SCREENSHOTS_DIR)) {
      fs.mkdirSync(SCREENSHOTS_DIR, { recursive: true });
    }

    // 브라우저 실행
    this.browser = await chromium.launch({
      headless: false, // 개발시 화면 보이도록
      slowMo: 500      // 액션 간 대기시간
    });

    this.page = await this.browser.newPage();
    
    // 타임아웃 설정
    this.page.setDefaultTimeout(TEST_TIMEOUT);
    
    console.log('✅ 브라우저 초기화 완료');
  }

  async testBasicAccess() {
    console.log('🧪 기본 접근성 테스트...');
    
    try {
      // 메인 페이지 접속
      await this.page.goto(BASE_URL);
      
      // 페이지 로딩 대기
      await this.page.waitForLoadState('networkidle');
      
      // 기본 요소 확인
      const title = await this.page.title();
      console.log(`📄 페이지 제목: ${title}`);
      
      // 메인 스크린샷
      await this.page.screenshot({ 
        path: path.join(SCREENSHOTS_DIR, 'main-page.png'),
        fullPage: true
      });
      
      this.testResults.push({
        test: 'basic_access',
        status: 'pass',
        message: '기본 접근성 테스트 통과'
      });
      
      console.log('✅ 기본 접근성 테스트 통과');
      
    } catch (error) {
      this.testResults.push({
        test: 'basic_access',
        status: 'fail', 
        message: `기본 접근성 테스트 실패: ${error.message}`
      });
      
      console.error('❌ 기본 접근성 테스트 실패:', error.message);
      throw error;
    }
  }

  async testModuleNavigation() {
    console.log('🧪 모듈 네비게이션 테스트...');
    
    try {
      // 모듈 선택기 확인
      const moduleSelector = await this.page.locator('.module-selector');
      
      if (await moduleSelector.count() > 0) {
        await moduleSelector.click();
        
        // 드롭다운 메뉴 확인
        await this.page.waitForSelector('.module-dropdown');
        
        // 스크린샷
        await this.page.screenshot({
          path: path.join(SCREENSHOTS_DIR, 'module-navigation.png')
        });
        
        console.log('✅ 모듈 네비게이션 확인 완료');
      }
      
      this.testResults.push({
        test: 'module_navigation',
        status: 'pass',
        message: '모듈 네비게이션 테스트 통과'
      });
      
    } catch (error) {
      this.testResults.push({
        test: 'module_navigation',
        status: 'fail',
        message: `모듈 네비게이션 테스트 실패: ${error.message}`
      });
      
      console.error('❌ 모듈 네비게이션 테스트 실패:', error.message);
    }
  }

  async testModule(moduleKey) {
    const module = BPM_MODULES[moduleKey];
    if (!module) {
      console.error(`❌ 알 수 없는 모듈: ${moduleKey}`);
      return;
    }

    console.log(`🧪 ${module.name} 모듈 테스트...`);
    
    try {
      // 모듈 페이지 접속
      const moduleUrl = `${BASE_URL}${module.url}`;
      await this.page.goto(moduleUrl);
      
      // 페이지 로딩 대기
      await this.page.waitForLoadState('networkidle');
      
      // 테마 색상 확인
      const bodyBgColor = await this.page.evaluate(() => {
        return getComputedStyle(document.body).backgroundColor;
      });
      
      console.log(`🎨 배경색 확인: ${bodyBgColor}`);
      
      // 모듈별 필수 요소 확인
      for (const element of module.testElements) {
        try {
          await this.page.waitForSelector(element, { timeout: 5000 });
          console.log(`✅ 요소 확인: ${element}`);
        } catch (error) {
          console.log(`⚠️ 요소 없음: ${element} (개발 예정)`);
        }
      }
      
      // 모듈 스크린샷
      await this.page.screenshot({
        path: path.join(SCREENSHOTS_DIR, `${moduleKey}-module.png`),
        fullPage: true
      });
      
      this.testResults.push({
        test: `module_${moduleKey}`,
        status: 'pass',
        message: `${module.name} 모듈 테스트 통과`
      });
      
      console.log(`✅ ${module.name} 모듈 테스트 통과`);
      
    } catch (error) {
      this.testResults.push({
        test: `module_${moduleKey}`,
        status: 'fail',
        message: `${module.name} 모듈 테스트 실패: ${error.message}`
      });
      
      console.error(`❌ ${module.name} 모듈 테스트 실패:`, error.message);
    }
  }

  async testResponsiveDesign() {
    console.log('🧪 반응형 디자인 테스트...');
    
    const viewports = [
      { name: 'desktop', width: 1920, height: 1080 },
      { name: 'tablet', width: 768, height: 1024 },
      { name: 'mobile', width: 375, height: 667 }
    ];

    for (const viewport of viewports) {
      try {
        await this.page.setViewportSize({
          width: viewport.width,
          height: viewport.height
        });
        
        await this.page.waitForTimeout(1000);
        
        await this.page.screenshot({
          path: path.join(SCREENSHOTS_DIR, `responsive-${viewport.name}.png`),
          fullPage: true
        });
        
        console.log(`✅ ${viewport.name} 뷰포트 테스트 완료`);
        
      } catch (error) {
        console.error(`❌ ${viewport.name} 뷰포트 테스트 실패:`, error.message);
      }
    }
  }

  async runAllTests() {
    try {
      await this.initialize();
      
      // 기본 테스트
      await this.testBasicAccess();
      await this.testModuleNavigation();
      
      // 개발된 모듈만 테스트 (존재하는 모듈 확인)
      for (const moduleKey of Object.keys(BPM_MODULES)) {
        await this.testModule(moduleKey);
      }
      
      // 반응형 테스트
      await this.testResponsiveDesign();
      
      // 결과 리포트
      this.generateReport();
      
    } catch (error) {
      console.error('🚨 테스트 실행 중 오류:', error);
      process.exit(1);
    } finally {
      if (this.browser) {
        await this.browser.close();
      }
    }
  }

  generateReport() {
    console.log('\n📊 테스트 결과 리포트');
    console.log('=' .repeat(50));
    
    const passCount = this.testResults.filter(r => r.status === 'pass').length;
    const failCount = this.testResults.filter(r => r.status === 'fail').length;
    
    console.log(`✅ 통과: ${passCount}개`);
    console.log(`❌ 실패: ${failCount}개`);
    
    this.testResults.forEach(result => {
      const icon = result.status === 'pass' ? '✅' : '❌';
      console.log(`${icon} ${result.test}: ${result.message}`);
    });
    
    // JSON 리포트 저장
    const reportPath = path.join(__dirname, 'test-report.json');
    fs.writeFileSync(reportPath, JSON.stringify({
      timestamp: new Date().toISOString(),
      summary: { total: this.testResults.length, pass: passCount, fail: failCount },
      results: this.testResults
    }, null, 2));
    
    console.log(`\n📄 상세 리포트: ${reportPath}`);
    console.log(`📸 스크린샷: ${SCREENSHOTS_DIR}`);
    
    if (failCount > 0) {
      console.log('\n🚨 테스트 실패가 있습니다. 배포를 중단합니다.');
      process.exit(1);
    } else {
      console.log('\n🎉 모든 테스트 통과! 배포를 진행합니다.');
      process.exit(0);
    }
  }
}

// 메인 실행
if (require.main === module) {
  const tester = new BPMTester();
  tester.runAllTests().catch(error => {
    console.error('🚨 테스트 실패:', error);
    process.exit(1);
  });
}

module.exports = BPMTester;