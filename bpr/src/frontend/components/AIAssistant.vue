<!-- BPR AI 어시스턴트 메인 컴포넌트 -->
<template>
  <div class="ai-assistant">
    <!-- AI 기능 선택 탭 -->
    <div class="ai-tabs">
      <button 
        v-for="feature in aiFeatures" 
        :key="feature.id"
        :class="['tab-button', { active: activeFeature === feature.id }]"
        @click="setActiveFeature(feature.id)"
        :disabled="!isPremiumUser || !feature.available"
      >
        <i :class="feature.icon"></i>
        {{ feature.name }}
        <span v-if="!feature.available" class="unavailable-badge">준비중</span>
      </button>
    </div>

    <!-- 사용량 표시 -->
    <div class="usage-indicator" v-if="isPremiumUser">
      <div class="usage-bar">
        <div 
          class="usage-fill" 
          :style="{ width: usagePercentage + '%' }"
          :class="{ warning: usagePercentage > 80, danger: usagePercentage > 95 }"
        ></div>
      </div>
      <div class="usage-text">
        {{ usage.monthlyTokens.toLocaleString() }} / {{ limits.monthlyTokens.toLocaleString() }} 토큰 사용
      </div>
    </div>

    <!-- 프리미엄 업그레이드 알림 -->
    <div v-if="!isPremiumUser" class="premium-notice">
      <i class="fas fa-crown"></i>
      <p>AI 기능은 프리미엄 회원만 이용할 수 있습니다.</p>
      <button @click="showUpgradeModal" class="upgrade-button">
        프리미엄 업그레이드
      </button>
    </div>

    <!-- AI 기능별 컴포넌트 -->
    <div v-if="isPremiumUser" class="ai-content">
      <!-- Task 매뉴얼 생성 -->
      <TaskManualGenerator 
        v-if="activeFeature === 'task-manual'"
        @generate="generateTaskManual"
        :loading="isGenerating"
      />

      <!-- Process Task 제안 -->
      <TaskSuggestionPanel
        v-if="activeFeature === 'task-suggestions'"
        @analyze="analyzeProcessTasks"
        :loading="isGenerating"
      />

      <!-- 프로세스 최적화 -->
      <ProcessOptimizer
        v-if="activeFeature === 'process-optimization'"
        @optimize="optimizeProcess"
        :loading="isGenerating"
      />

      <!-- BPR 리포트 생성 -->
      <ReportGenerator
        v-if="activeFeature === 'bpr-report'"
        @generate="generateBPRReport"
        :loading="isGenerating"
        :progress="reportProgress"
      />

      <!-- 조직 최적화 -->
      <OrganizationOptimizer
        v-if="activeFeature === 'org-optimization'"
        @optimize="optimizeOrganization"
        :loading="isGenerating"
      />
    </div>

    <!-- AI 엔진 상태 표시 -->
    <div class="engine-status" v-if="showEngineStatus">
      <div class="status-header">
        <span>AI 엔진 상태</span>
        <button @click="refreshEngineStatus" class="refresh-button">
          <i class="fas fa-sync-alt" :class="{ spinning: refreshingStatus }"></i>
        </button>
      </div>
      <div class="engine-list">
        <div 
          v-for="(status, engine) in engineStatus" 
          :key="engine"
          :class="['engine-item', status]"
        >
          <span class="engine-name">{{ getEngineName(engine) }}</span>
          <span :class="['status-badge', status]">{{ getStatusText(status) }}</span>
        </div>
      </div>
    </div>

    <!-- 결과 표시 모달 -->
    <AIResultModal
      v-if="showResultModal"
      :result="currentResult"
      :type="resultType"
      @close="showResultModal = false"
      @save="saveResult"
      @export="exportResult"
    />

    <!-- 스트리밍 진행 상황 모달 -->
    <StreamingProgressModal
      v-if="showProgressModal"
      :progress="streamingProgress"
      :currentSection="currentSection"
      @cancel="cancelGeneration"
    />
  </div>
</template>

<script>
import TaskManualGenerator from './TaskManualGenerator.vue';
import TaskSuggestionPanel from './TaskSuggestionPanel.vue';
import ProcessOptimizer from './ProcessOptimizer.vue';
import ReportGenerator from './ReportGenerator.vue';
import OrganizationOptimizer from './OrganizationOptimizer.vue';
import AIResultModal from './AIResultModal.vue';
import StreamingProgressModal from './StreamingProgressModal.vue';
import { aiService } from '../services/aiService';
import { userService } from '../services/userService';

export default {
  name: 'AIAssistant',
  components: {
    TaskManualGenerator,
    TaskSuggestionPanel,
    ProcessOptimizer,
    ReportGenerator,
    OrganizationOptimizer,
    AIResultModal,
    StreamingProgressModal
  },
  data() {
    return {
      activeFeature: 'task-manual',
      isGenerating: false,
      showResultModal: false,
      showProgressModal: false,
      showEngineStatus: false,
      refreshingStatus: false,
      
      currentResult: null,
      resultType: '',
      streamingProgress: 0,
      currentSection: '',
      reportProgress: 0,
      
      usage: {
        monthlyTokens: 0,
        dailyRequests: 0
      },
      limits: {
        monthlyTokens: 100000,
        dailyRequests: 500
      },
      
      engineStatus: {},
      
      aiFeatures: [
        {
          id: 'task-manual',
          name: 'Task 매뉴얼',
          icon: 'fas fa-book',
          available: true,
          description: 'Task별 상세 매뉴얼 자동 생성'
        },
        {
          id: 'task-suggestions',
          name: 'Task 제안',
          icon: 'fas fa-lightbulb',
          available: true,
          description: 'Process별 최적 Task 목록 제안'
        },
        {
          id: 'process-optimization',
          name: '프로세스 최적화',
          icon: 'fas fa-cogs',
          available: true,
          description: 'AI 기반 프로세스 최적화 분석'
        },
        {
          id: 'bpr-report',
          name: 'BPR 리포트',
          icon: 'fas fa-chart-line',
          available: true,
          description: '종합 BPR 분석 리포트 생성'
        },
        {
          id: 'org-optimization',
          name: '조직 최적화',
          icon: 'fas fa-sitemap',
          available: true,
          description: '조직 구조 최적화 제안'
        }
      ]
    };
  },
  
  computed: {
    isPremiumUser() {
      return this.$store.getters['user/isPremium'];
    },
    
    usagePercentage() {
      return Math.round((this.usage.monthlyTokens / this.limits.monthlyTokens) * 100);
    }
  },
  
  async mounted() {
    if (this.isPremiumUser) {
      await this.loadUsageData();
      await this.loadEngineStatus();
    }
  },
  
  methods: {
    setActiveFeature(featureId) {
      this.activeFeature = featureId;
    },
    
    async loadUsageData() {
      try {
        const response = await aiService.getUsage();
        this.usage = response.data.usage;
        this.limits = response.data.limits;
      } catch (error) {
        console.error('Usage data loading failed:', error);
        this.$toast.error('사용량 정보를 불러올 수 없습니다.');
      }
    },
    
    async loadEngineStatus() {
      try {
        const response = await aiService.getEngineStatus();
        this.engineStatus = response.data.engines;
      } catch (error) {
        console.error('Engine status loading failed:', error);
      }
    },
    
    async refreshEngineStatus() {
      this.refreshingStatus = true;
      try {
        await this.loadEngineStatus();
      } finally {
        this.refreshingStatus = false;
      }
    },
    
    async generateTaskManual(taskData) {
      this.isGenerating = true;
      try {
        const response = await aiService.generateTaskManual(taskData);
        this.showResult(response.data.manual, 'task-manual');
        
        this.$toast.success('Task 매뉴얼이 생성되었습니다.');
        await this.loadUsageData(); // 사용량 업데이트
        
      } catch (error) {
        console.error('Task manual generation failed:', error);
        this.$toast.error(error.response?.data?.message || 'Task 매뉴얼 생성에 실패했습니다.');
      } finally {
        this.isGenerating = false;
      }
    },
    
    async analyzeProcessTasks(processData) {
      this.isGenerating = true;
      try {
        const response = await aiService.suggestProcessTasks(processData);
        this.showResult(response.data.suggestions, 'task-suggestions');
        
        this.$toast.success('Task 제안이 완료되었습니다.');
        await this.loadUsageData();
        
      } catch (error) {
        console.error('Task suggestion failed:', error);
        this.$toast.error(error.response?.data?.message || 'Task 제안 생성에 실패했습니다.');
      } finally {
        this.isGenerating = false;
      }
    },
    
    async optimizeProcess(processData) {
      this.isGenerating = true;
      try {
        const response = await aiService.optimizeProcess(processData);
        this.showResult(response.data.optimization, 'process-optimization');
        
        this.$toast.success('프로세스 최적화 분석이 완료되었습니다.');
        await this.loadUsageData();
        
      } catch (error) {
        console.error('Process optimization failed:', error);
        this.$toast.error(error.response?.data?.message || '프로세스 최적화 분석에 실패했습니다.');
      } finally {
        this.isGenerating = false;
      }
    },
    
    async generateBPRReport(projectData) {
      this.isGenerating = true;
      this.showProgressModal = true;
      this.reportProgress = 0;
      
      try {
        // Server-Sent Events로 스트리밍 수신
        const eventSource = await aiService.generateBPRReportStream(projectData);
        
        eventSource.onmessage = (event) => {
          const data = JSON.parse(event.data);
          
          if (data.type === 'progress') {
            this.reportProgress = data.progress;
            this.currentSection = data.section;
          } else if (data.type === 'complete') {
            this.showResult(data.data, 'bpr-report');
            this.showProgressModal = false;
            this.$toast.success('BPR 리포트가 생성되었습니다.');
            eventSource.close();
          } else if (data.type === 'error') {
            throw new Error(data.message);
          }
        };
        
        eventSource.onerror = (error) => {
          console.error('Report generation stream error:', error);
          this.$toast.error('리포트 생성 중 오류가 발생했습니다.');
          eventSource.close();
        };
        
        await this.loadUsageData();
        
      } catch (error) {
        console.error('BPR report generation failed:', error);
        this.$toast.error(error.response?.data?.message || 'BPR 리포트 생성에 실패했습니다.');
        this.showProgressModal = false;
      } finally {
        this.isGenerating = false;
      }
    },
    
    async optimizeOrganization(orgData) {
      this.isGenerating = true;
      try {
        const response = await aiService.optimizeOrganization(orgData);
        this.showResult(response.data.optimization, 'org-optimization');
        
        this.$toast.success('조직 최적화 분석이 완료되었습니다.');
        await this.loadUsageData();
        
      } catch (error) {
        console.error('Organization optimization failed:', error);
        this.$toast.error(error.response?.data?.message || '조직 최적화 분석에 실패했습니다.');
      } finally {
        this.isGenerating = false;
      }
    },
    
    showResult(result, type) {
      this.currentResult = result;
      this.resultType = type;
      this.showResultModal = true;
    },
    
    async saveResult(result) {
      try {
        // 결과를 데이터베이스에 저장하는 로직
        await this.$store.dispatch('ai/saveResult', {
          type: this.resultType,
          data: result,
          timestamp: new Date()
        });
        
        this.$toast.success('결과가 저장되었습니다.');
      } catch (error) {
        console.error('Result saving failed:', error);
        this.$toast.error('결과 저장에 실패했습니다.');
      }
    },
    
    async exportResult(result, format) {
      try {
        const blob = await aiService.exportResult(result, format);
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `bpr-result-${Date.now()}.${format}`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        this.$toast.success('결과가 내보내기 되었습니다.');
      } catch (error) {
        console.error('Result export failed:', error);
        this.$toast.error('결과 내보내기에 실패했습니다.');
      }
    },
    
    cancelGeneration() {
      // 생성 취소 로직
      this.showProgressModal = false;
      this.isGenerating = false;
      this.$toast.info('생성이 취소되었습니다.');
    },
    
    showUpgradeModal() {
      this.$modal.show('premium-upgrade');
    },
    
    getEngineName(engine) {
      const names = {
        'openai': 'OpenAI',
        'anthropic': 'Anthropic',
        'google': 'Google AI',
        'azure': 'Azure OpenAI'
      };
      return names[engine] || engine;
    },
    
    getStatusText(status) {
      const texts = {
        'healthy': '정상',
        'error': '오류',
        'maintenance': '점검중'
      };
      return texts[status] || status;
    }
  },
  
  watch: {
    isPremiumUser(newVal) {
      if (newVal) {
        this.loadUsageData();
        this.loadEngineStatus();
      }
    }
  }
};
</script>

<style scoped>
.ai-assistant {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.ai-tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 2px solid #e1e5e9;
}

.tab-button {
  padding: 12px 20px;
  border: none;
  background: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  border-radius: 8px 8px 0 0;
  transition: all 0.2s ease;
  position: relative;
}

.tab-button:hover:not(:disabled) {
  background-color: #f8f9fa;
}

.tab-button.active {
  background-color: #007bff;
  color: white;
}

.tab-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.unavailable-badge {
  font-size: 10px;
  background: #ffc107;
  color: #000;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 5px;
}

.usage-indicator {
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.usage-bar {
  width: 100%;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 8px;
}

.usage-fill {
  height: 100%;
  background: #28a745;
  transition: all 0.3s ease;
}

.usage-fill.warning {
  background: #ffc107;
}

.usage-fill.danger {
  background: #dc3545;
}

.usage-text {
  font-size: 14px;
  color: #6c757d;
  text-align: center;
}

.premium-notice {
  text-align: center;
  padding: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  margin-bottom: 20px;
}

.premium-notice i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #ffd700;
}

.upgrade-button {
  background: #ffd700;
  color: #333;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  margin-top: 16px;
  transition: all 0.2s ease;
}

.upgrade-button:hover {
  background: #ffed4e;
  transform: translateY(-2px);
}

.ai-content {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.engine-status {
  margin-top: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
}

.status-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  font-weight: bold;
}

.refresh-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
}

.refresh-button:hover {
  background: #e9ecef;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.engine-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.engine-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: white;
  border-radius: 6px;
  border-left: 4px solid #dee2e6;
}

.engine-item.healthy {
  border-left-color: #28a745;
}

.engine-item.error {
  border-left-color: #dc3545;
}

.status-badge {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 12px;
  font-weight: bold;
}

.status-badge.healthy {
  background: #d4edda;
  color: #155724;
}

.status-badge.error {
  background: #f8d7da;
  color: #721c24;
}
</style>