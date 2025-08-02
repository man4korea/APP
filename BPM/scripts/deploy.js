// 📁 C:\xampp\htdocs\BPM\scripts\deploy.js
// Create at 2508021941 Ver1.01

#!/usr/bin/env node
/**
 * BPM Total Business Process Management - 배포 자동화 스크립트 (Node.js)
 * 
 * 용도: 크로스 플랫폼 배포 자동화
 * 실행: node scripts/deploy.js
 */

const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');
const { promisify } = require('util');

const execAsync = promisify(exec);

class BPMDeployer {
  constructor() {
    this.config = {
      localPath: 'C:\\xampp\\htdocs\\BPM',
      oneDrivePath: 'C:\\Users\\man4k\\OneDrive\\문서\\APP\\bpm',
      webServerPath: 'Z:\\html\\bpm',
      testCommand: 'node tests/bpm-module-test.js'
    };
    
    this.deployLog = [];
  }

  log(message, type = 'info') {
    const timestamp = new Date().toISOString();
    const logEntry = `[${timestamp}] ${type.toUpperCase()}: ${message}`;
    
    console.log(logEntry);
    this.deployLog.push(logEntry);
  }

  async checkServices() {
    this.log('XAMPP 서비스 확인 중...');
    
    try {
      // Apache 확인
      const { stdout: apacheResult } = await execAsync('tasklist /FI "IMAGENAME eq httpd.exe"');
      if (apacheResult.includes('httpd.exe')) {
        this.log('✅ Apache 서버 실행중');
      } else {
        throw new Error('Apache 서버가 실행되지 않음');
      }

      // MySQL 확인  
      const { stdout: mysqlResult } = await execAsync('tasklist /FI "IMAGENAME eq mysqld.exe"');
      if (mysqlResult.includes('mysqld.exe')) {
        this.log('✅ MySQL 서버 실행중');
      } else {
        throw new Error('MySQL 서버가 실행되지 않음');
      }

      return true;
    } catch (error) {
      this.log(`❌ 서비스 확인 실패: ${error.message}`, 'error');
      return false;
    }
  }

  async runTests() {
    this.log('Playwright 테스트 실행 중...');
    
    try {
      // package.json 확인 및 생성
      const packageJsonPath = path.join(this.config.localPath, 'package.json');
      if (!fs.existsSync(packageJsonPath)) {
        this.log('📦 package.json 생성 중...');
        
        const packageJson = {
          name: 'bmp-tests',
          version: '1.0.0',
          dependencies: {
            playwright: '^1.40.0'
          }
        };
        
        fs.writeFileSync(packageJsonPath, JSON.stringify(packageJson, null, 2));
        
        this.log('📥 Playwright 설치 중...');
        process.chdir(this.config.localPath);
        await execAsync('npm install');
      }

      // 테스트 실행
      const { stdout, stderr } = await execAsync(this.config.testCommand, {
        cwd: this.config.localPath
      });
      
      if (stderr && !stderr.includes('Warning')) {
        throw new Error(stderr);
      }
      
      this.log('✅ 모든 테스트 통과!');
      return true;
      
    } catch (error) {
      this.log(`❌ 테스트 실패: ${error.message}`, 'error');
      return false;
    }
  }

  async copyFiles(source, destination, description) {
    this.log(`${description} 복사 중...`);
    
    try {
      // 대상 디렉토리 생성
      if (!fs.existsSync(destination)) {
        fs.mkdirSync(destination, { recursive: true });
        this.log(`📁 디렉토리 생성: ${destination}`);
      }

      // Windows xcopy 명령 사용
      const command = `xcopy "${source}\\*" "${destination}\\" /E /Y /I /Q`;
      await execAsync(command);
      
      this.log(`✅ ${description} 완료`);
      return true;
      
    } catch (error) {
      this.log(`❌ ${description} 실패: ${error.message}`, 'error');
      return false;
    }
  }

  async backupToOneDrive() {
    return await this.copyFiles(
      this.config.localPath,
      this.config.oneDrivePath,
      'OneDrive 백업'
    );
  }

  async deployToWebServer() {
    // Z 드라이브 확인
    if (!fs.existsSync('Z:\\')) {
      this.log('⚠️ Z 드라이브가 연결되지 않음 - 웹서버 배포 건너뜀', 'warn');
      return true; // 실패로 처리하지 않음
    }

    return await this.copyFiles(
      this.config.localPath,
      this.config.webServerPath,
      '웹서버 배포'
    );
  }

  async saveDeployLog() {
    const logDir = path.join(this.config.localPath, 'logs');
    if (!fs.existsSync(logDir)) {
      fs.mkdirSync(logDir, { recursive: true });
    }

    const logFile = path.join(logDir, 'deploy.log');
    const logContent = this.deployLog.join('\n') + '\n';
    
    fs.appendFileSync(logFile, logContent);
    this.log(`📄 배포 로그 저장: ${logFile}`);
  }

  async deploy() {
    console.log('\n================================');
    console.log('  🚀 BPM 자동 배포 시스템');
    console.log('================================\n');

    const startTime = new Date();
    this.log(`배포 시작: ${startTime.toLocaleString()}`);

    try {
      // 1단계: XAMPP 서비스 확인
      const servicesOk = await this.checkServices();
      if (!servicesOk) {
        this.log('XAMPP 서비스 확인 실패 - 배포 중단', 'error');
        process.exit(1);
      }

      // 2단계: 테스트 실행
      const testsOk = await this.runTests();
      if (!testsOk) {
        this.log('테스트 실패 - 배포 중단', 'error');
        process.exit(1);
      }

      // 3단계: OneDrive 백업
      const backupOk = await this.backupToOneDrive();
      if (!backupOk) {
        this.log('OneDrive 백업 실패', 'error');
      }

      // 4단계: 웹서버 배포
      const deployOk = await this.deployToWebServer();
      if (!deployOk) {
        this.log('웹서버 배포 실패', 'warn');
      }

      // 완료
      const endTime = new Date();
      const duration = Math.round((endTime - startTime) / 1000);

      console.log('\n================================');
      console.log('  🎉 배포 완료!');
      console.log('================================\n');

      console.log('📊 배포 요약:');
      console.log(`  - 로컬 개발: ${this.config.localPath}`);
      console.log(`  - OneDrive 백업: ${this.config.oneDrivePath}`);
      console.log(`  - 웹서버: ${this.config.webServerPath}`);
      console.log(`  - 소요 시간: ${duration}초`);
      console.log('\n🔗 접속 URL:');
      console.log('  - 로컬: http://localhost/BPM/');
      console.log('  - 웹서버: [웹서버 URL]/bpm/\n');

      this.log(`배포 완료 - 소요시간: ${duration}초`);
      await this.saveDeployLog();

      process.exit(0);

    } catch (error) {
      this.log(`배포 중 오류 발생: ${error.message}`, 'error');
      await this.saveDeployLog();
      process.exit(1);
    }
  }
}

// 메인 실행
if (require.main === module) {
  const deployer = new BPMDeployer();
  deployer.deploy().catch(error => {
    console.error('🚨 배포 실패:', error);
    process.exit(1);
  });
}

module.exports = BPMDeployer;