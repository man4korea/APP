// 📁 C:\xampp\htdocs\BPM\scripts\deploy-full.js
// Create at 2508021947 Ver1.00

#!/usr/bin/env node
/**
 * BPM Total Business Process Management - 완전 자동화 배포 스크립트
 * 
 * 기능: Git 커밋/푸시 + 테스트 + 파일 배포 통합
 * 실행: node scripts/deploy-full.js
 */

const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');
const { promisify } = require('util');

const execAsync = promisify(exec);

class FullBPMDeployer {
  constructor() {
    this.config = {
      localPath: 'C:\\xampp\\htdocs\\BPM',
      oneDrivePath: 'C:\\Users\\man4k\\OneDrive\\문서\\APP\\bmp',
      webServerPath: 'Z:\\html\\bmp',
      gitBranch: 'main',
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

  async checkGitStatus() {
    this.log('Git 상태 확인 중...');
    
    try {
      const { stdout } = await execAsync('git status --porcelain', {
        cwd: this.config.localPath
      });
      
      if (stdout.trim() === '') {
        this.log('⚠️ 변경사항이 없습니다.');
        return false;
      }
      
      this.log(`📝 변경된 파일: ${stdout.split('\n').length - 1}개`);
      return true;
      
    } catch (error) {
      this.log(`❌ Git 상태 확인 실패: ${error.message}`, 'error');
      return false;
    }
  }

  async gitCommitAndPush(commitMessage) {
    this.log('Git 커밋 및 푸시 중...');
    
    try {
      // Git add
      await execAsync('git add .', {
        cwd: this.config.localPath
      });
      this.log('✅ git add 완료');

      // Git commit
      const message = commitMessage || `auto: 자동 배포 커밋 - ${new Date().toISOString()}`;
      await execAsync(`git commit -m "${message}"`, {
        cwd: this.config.localPath
      });
      this.log('✅ git commit 완료');

      // Git push
      await execAsync(`git push origin ${this.config.gitBranch}`, {
        cwd: this.config.localPath
      });
      this.log('✅ git push 완료');

      return true;
      
    } catch (error) {
      this.log(`❌ Git 작업 실패: ${error.message}`, 'error');
      return false;
    }
  }

  async runTests() {
    this.log('테스트 실행 중...');
    
    try {
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
    this.log(`${description} 중...`);
    
    try {
      if (!fs.existsSync(destination)) {
        fs.mkdirSync(destination, { recursive: true });
        this.log(`📁 디렉토리 생성: ${destination}`);
      }

      const command = `xcopy "${source}\\*" "${destination}\\" /E /Y /I /Q`;
      await execAsync(command);
      
      this.log(`✅ ${description} 완료`);
      return true;
      
    } catch (error) {
      this.log(`❌ ${description} 실패: ${error.message}`, 'error');
      return false;
    }
  }

  async saveDeployLog() {
    const logDir = path.join(this.config.localPath, 'logs');
    if (!fs.existsSync(logDir)) {
      fs.mkdirSync(logDir, { recursive: true });
    }

    const logFile = path.join(logDir, 'deploy-full.log');
    const logContent = this.deployLog.join('\n') + '\n';
    
    fs.appendFileSync(logFile, logContent);
    this.log(`📄 배포 로그 저장: ${logFile}`);
  }

  async deployFull(commitMessage = null) {
    console.log('\n================================');
    console.log('  🚀 BPM 완전 자동 배포 시스템');
    console.log('================================\n');

    const startTime = new Date();
    this.log(`완전 배포 시작: ${startTime.toLocaleString()}`);

    try {
      // 1단계: Git 상태 확인
      const hasChanges = await this.checkGitStatus();
      if (!hasChanges && !process.argv.includes('--force')) {
        this.log('변경사항이 없어 배포를 건너뜁니다. (--force 옵션으로 강제 실행 가능)', 'warn');
        return;
      }

      // 2단계: 테스트 실행
      const testsOk = await this.runTests();
      if (!testsOk) {
        this.log('테스트 실패 - 배포 중단', 'error');
        process.exit(1);
      }

      // 3단계: Git 커밋 및 푸시
      const gitOk = await this.gitCommitAndPush(commitMessage);
      if (!gitOk) {
        this.log('Git 작업 실패 - 배포 중단', 'error');
        process.exit(1);
      }

      // 4단계: OneDrive 백업
      const backupOk = await this.copyFiles(
        this.config.localPath,
        this.config.oneDrivePath,
        'OneDrive 백업'
      );

      // 5단계: 웹서버 배포
      let deployOk = true;
      if (fs.existsSync('Z:\\')) {
        deployOk = await this.copyFiles(
          this.config.localPath,
          this.config.webServerPath,
          '웹서버 배포'
        );
      } else {
        this.log('⚠️ Z 드라이브가 연결되지 않음 - 웹서버 배포 건너뜀', 'warn');
      }

      // 완료
      const endTime = new Date();
      const duration = Math.round((endTime - startTime) / 1000);

      console.log('\n================================');
      console.log('  🎉 완전 배포 완료!');
      console.log('================================\n');

      console.log('📊 배포 요약:');
      console.log(`  - Git 푸시: ✅ ${this.config.gitBranch} 브랜치`);
      console.log(`  - 테스트: ✅ 통과`);
      console.log(`  - OneDrive: ${backupOk ? '✅' : '❌'} 백업`);
      console.log(`  - 웹서버: ${deployOk ? '✅' : '❌'} 배포`);
      console.log(`  - 소요시간: ${duration}초`);
      
      console.log('\n🔗 접속 URL:');
      console.log('  - 로컬: http://localhost/BPM/');
      console.log('  - Git: 원격 저장소 업데이트 완료');
      console.log('  - 웹서버: [웹서버 URL]/bpm/\n');

      this.log(`완전 배포 완료 - 소요시간: ${duration}초`);
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
  const commitMessage = process.argv[2]; // 커밋 메시지를 인수로 받음
  
  const deployer = new FullBPMDeployer();
  deployer.deployFull(commitMessage).catch(error => {
    console.error('🚨 완전 배포 실패:', error);
    process.exit(1);
  });
}

module.exports = FullBPMDeployer;