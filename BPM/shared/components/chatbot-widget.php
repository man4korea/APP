<!-- 📁 C:\xampp\htdocs\BPM\shared\components\chatbot-widget.php -->
<!-- Create at 2508031220 Ver1.00 -->

<?php
/**
 * BPM AI 챗봇 위젯 컴포넌트
 * 모든 페이지에서 사용할 수 있는 플로팅 챗봇 인터페이스
 * 모듈: AI 지원 시스템 (색상: #00ff88)
 */

// 현재 사용자 정보
$currentUser = $auth->getCurrentUser() ?? null;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- 챗봇 플로팅 버튼 -->
<div id="chatbot-widget" class="chatbot-widget">
    <!-- 챗봇 열기 버튼 -->
    <button type="button" class="chatbot-toggle-btn" id="chatbot-toggle-btn" aria-label="AI 도우미">
        <div class="chatbot-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect>
                <circle cx="12" cy="5" r="2"></circle>
                <path d="M12 7v4"></path>
                <line x1="8" y1="16" x2="8" y2="16"></line>
                <line x1="16" y1="16" x2="16" y2="16"></line>
                <path d="M1 16h6m10 0h6"></path>
            </svg>
        </div>
        <div class="chatbot-notification" id="chatbot-notification" style="display: none;">
            <span class="notification-dot"></span>
        </div>
    </button>
    
    <!-- 챗봇 패널 -->
    <div class="chatbot-panel" id="chatbot-panel">
        <!-- 헤더 -->
        <div class="chatbot-header">
            <div class="chatbot-title">
                <div class="chatbot-avatar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect>
                        <circle cx="12" cy="5" r="2"></circle>
                        <path d="M12 7v4"></path>
                    </svg>
                </div>
                <div class="chatbot-info">
                    <h4>BPM AI 도우미</h4>
                    <span class="chatbot-status">온라인</span>
                </div>
            </div>
            <div class="chatbot-actions">
                <button type="button" class="chatbot-minimize-btn" id="chatbot-minimize-btn" title="최소화">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </button>
                <button type="button" class="chatbot-close-btn" id="chatbot-close-btn" title="닫기">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- 채팅 영역 -->
        <div class="chatbot-messages" id="chatbot-messages">
            <!-- 초기 메시지 -->
            <div class="message bot-message">
                <div class="message-avatar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect>
                        <circle cx="12" cy="5" r="2"></circle>
                    </svg>
                </div>
                <div class="message-content">
                    <div class="message-text">
                        안녕하세요! BPM AI 도우미입니다. 🤖<br>
                        시스템 사용법이나 궁금한 점이 있으시면 언제든 물어보세요!
                    </div>
                    <div class="message-time"><?= date('H:i') ?></div>
                </div>
            </div>
            
            <!-- 빠른 질문 버튼들 -->
            <div class="quick-questions">
                <button type="button" class="quick-question-btn" data-message="이 페이지 사용법을 알려주세요">
                    📖 이 페이지 사용법
                </button>
                <button type="button" class="quick-question-btn" data-message="BPM 시스템 전체 기능을 설명해주세요">
                    🏢 시스템 소개
                </button>
                <button type="button" class="quick-question-btn" data-message="문제점이나 개선사항을 제안하고 싶어요">
                    💡 피드백 제출
                </button>
            </div>
        </div>
        
        <!-- 입력 영역 -->
        <div class="chatbot-input-area">
            <form id="chatbot-form" class="chatbot-form">
                <div class="input-group">
                    <input type="text" 
                           id="chatbot-input" 
                           class="chatbot-input" 
                           placeholder="궁금한 점을 입력하세요..." 
                           maxlength="500"
                           autocomplete="off">
                    <button type="submit" class="chatbot-send-btn" id="chatbot-send-btn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22,2 15,22 11,13 2,9 22,2"></polygon>
                        </svg>
                    </button>
                </div>
                <div class="input-footer">
                    <span class="char-count">0/500</span>
                    <span class="typing-indicator" id="typing-indicator" style="display: none;">
                        AI가 답변을 준비하고 있습니다...
                    </span>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 피드백 모달 -->
    <div class="feedback-modal" id="feedback-modal" style="display: none;">
        <div class="feedback-modal-content">
            <div class="feedback-header">
                <h4>피드백 제출</h4>
                <button type="button" class="feedback-close-btn" id="feedback-close-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="feedback-form" class="feedback-form">
                <div class="form-group">
                    <label for="feedback-type">피드백 유형</label>
                    <select id="feedback-type" name="type" required>
                        <option value="">선택하세요</option>
                        <option value="bug">버그 신고</option>
                        <option value="improvement">개선 제안</option>
                        <option value="feature_request">기능 요청</option>
                        <option value="general">일반 문의</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="feedback-title">제목</label>
                    <input type="text" id="feedback-title" name="title" required maxlength="255" 
                           placeholder="피드백 제목을 입력하세요">
                </div>
                <div class="form-group">
                    <label for="feedback-description">상세 내용</label>
                    <textarea id="feedback-description" name="description" required rows="4" maxlength="2000"
                              placeholder="구체적인 내용을 입력해주세요"></textarea>
                </div>
                <div class="form-group">
                    <label for="feedback-priority">우선순위</label>
                    <select id="feedback-priority" name="priority">
                        <option value="low">낮음</option>
                        <option value="medium" selected>보통</option>
                        <option value="high">높음</option>
                        <option value="critical">긴급</option>
                    </select>
                </div>
                <div class="feedback-actions">
                    <button type="button" class="btn btn-secondary" id="feedback-cancel-btn">취소</button>
                    <button type="submit" class="btn btn-primary">제출하기</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 챗봇 스타일 -->
<style>
.chatbot-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* 토글 버튼 */
.chatbot-toggle-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00ff88, #00d4aa);
    border: none;
    box-shadow: 0 4px 20px rgba(0, 255, 136, 0.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s ease;
    position: relative;
}

.chatbot-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(0, 255, 136, 0.4);
}

.chatbot-notification {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 20px;
    height: 20px;
    background: #ff4757;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-dot {
    width: 6px;
    height: 6px;
    background: white;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

/* 챗봇 패널 */
.chatbot-panel {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 600px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    display: none;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e1e8ed;
}

.chatbot-panel.active {
    display: flex;
}

/* 헤더 */
.chatbot-header {
    background: linear-gradient(135deg, #00ff88, #00d4aa);
    color: white;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chatbot-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chatbot-avatar {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-info h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chatbot-status {
    font-size: 12px;
    opacity: 0.9;
}

.chatbot-actions {
    display: flex;
    gap: 8px;
}

.chatbot-minimize-btn,
.chatbot-close-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.chatbot-minimize-btn:hover,
.chatbot-close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* 메시지 영역 */
.chatbot-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #f8f9fa;
}

.message {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bot-message .message-avatar {
    background: linear-gradient(135deg, #00ff88, #00d4aa);
    color: white;
}

.user-message {
    flex-direction: row-reverse;
}

.user-message .message-avatar {
    background: #007bff;
    color: white;
}

.message-content {
    flex: 1;
    max-width: 280px;
}

.user-message .message-content {
    text-align: right;
}

.message-text {
    background: white;
    padding: 12px 16px;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    word-wrap: break-word;
    line-height: 1.4;
}

.user-message .message-text {
    background: #007bff;
    color: white;
}

.message-time {
    font-size: 11px;
    color: #8e8e93;
    margin-top: 4px;
    padding: 0 4px;
}

/* 빠른 질문 버튼들 */
.quick-questions {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.quick-question-btn {
    background: white;
    border: 1px solid #e1e8ed;
    padding: 10px 16px;
    border-radius: 20px;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
}

.quick-question-btn:hover {
    background: #f0f8ff;
    border-color: #00ff88;
    transform: translateX(4px);
}

/* 입력 영역 */
.chatbot-input-area {
    padding: 16px 20px;
    background: white;
    border-top: 1px solid #e1e8ed;
}

.input-group {
    display: flex;
    gap: 8px;
    align-items: center;
}

.chatbot-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #e1e8ed;
    border-radius: 24px;
    outline: none;
    font-size: 14px;
    transition: border-color 0.2s;
}

.chatbot-input:focus {
    border-color: #00ff88;
}

.chatbot-send-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: #00ff88;
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.chatbot-send-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.chatbot-send-btn:not(:disabled):hover {
    background: #00d4aa;
    transform: scale(1.05);
}

.input-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
    font-size: 11px;
    color: #8e8e93;
}

.typing-indicator {
    color: #00ff88;
    font-style: italic;
}

/* 피드백 모달 */
.feedback-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.feedback-modal-content {
    background: white;
    border-radius: 12px;
    width: 500px;
    max-width: 90vw;
    max-height: 90vh;
    overflow-y: auto;
}

.feedback-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e1e8ed;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.feedback-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.feedback-close-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #f5f5f5;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.feedback-form {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e1e8ed;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #00ff88;
}

.feedback-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-secondary {
    background: #f5f5f5;
    color: #666;
}

.btn-secondary:hover {
    background: #e9e9e9;
}

.btn-primary {
    background: #00ff88;
    color: white;
}

.btn-primary:hover {
    background: #00d4aa;
}

/* 반응형 */
@media (max-width: 480px) {
    .chatbot-panel {
        width: calc(100vw - 40px);
        height: calc(100vh - 100px);
        bottom: 80px;
        right: 20px;
    }
    
    .feedback-modal-content {
        width: calc(100vw - 32px);
        margin: 16px;
    }
}

/* 스크롤바 스타일 */
.chatbot-messages::-webkit-scrollbar {
    width: 4px;
}

.chatbot-messages::-webkit-scrollbar-track {
    background: transparent;
}

.chatbot-messages::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 2px;
}

.chatbot-messages::-webkit-scrollbar-thumb:hover {
    background: #999;
}
</style>

<!-- 챗봇 JavaScript -->
<script>
class BPMChatbot {
    constructor() {
        this.isOpen = false;
        this.currentContext = '<?= $currentPage ?>';
        this.apiBaseUrl = '/BPM/api/chatbot';
        
        this.initElements();
        this.initEventListeners();
        this.loadChatHistory();
    }
    
    initElements() {
        this.toggleBtn = document.getElementById('chatbot-toggle-btn');
        this.panel = document.getElementById('chatbot-panel');
        this.minimizeBtn = document.getElementById('chatbot-minimize-btn');
        this.closeBtn = document.getElementById('chatbot-close-btn');
        this.messagesArea = document.getElementById('chatbot-messages');
        this.chatForm = document.getElementById('chatbot-form');
        this.chatInput = document.getElementById('chatbot-input');
        this.sendBtn = document.getElementById('chatbot-send-btn');
        this.typingIndicator = document.getElementById('typing-indicator');
        this.charCount = document.querySelector('.char-count');
        
        // 피드백 모달 요소들
        this.feedbackModal = document.getElementById('feedback-modal');
        this.feedbackForm = document.getElementById('feedback-form');
        this.feedbackCloseBtn = document.getElementById('feedback-close-btn');
        this.feedbackCancelBtn = document.getElementById('feedback-cancel-btn');
    }
    
    initEventListeners() {
        // 챗봇 토글
        this.toggleBtn.addEventListener('click', () => this.toggleChatbot());
        this.minimizeBtn.addEventListener('click', () => this.closeChatbot());
        this.closeBtn.addEventListener('click', () => this.closeChatbot());
        
        // 채팅 폼
        this.chatForm.addEventListener('submit', (e) => this.handleChatSubmit(e));
        this.chatInput.addEventListener('input', () => this.handleInputChange());
        this.chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.chatForm.requestSubmit();
            }
        });
        
        // 빠른 질문 버튼들
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quick-question-btn')) {
                const message = e.target.dataset.message;
                this.sendMessage(message);
            }
        });
        
        // 피드백 모달
        this.feedbackCloseBtn.addEventListener('click', () => this.closeFeedbackModal());
        this.feedbackCancelBtn.addEventListener('click', () => this.closeFeedbackModal());
        this.feedbackForm.addEventListener('submit', (e) => this.handleFeedbackSubmit(e));
        
        // 모달 외부 클릭시 닫기
        this.feedbackModal.addEventListener('click', (e) => {
            if (e.target === this.feedbackModal) {
                this.closeFeedbackModal();
            }
        });
        
        // ESC 키로 닫기
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.feedbackModal.style.display !== 'none') {
                    this.closeFeedbackModal();
                } else if (this.isOpen) {
                    this.closeChatbot();
                }
            }
        });
    }
    
    toggleChatbot() {
        if (this.isOpen) {
            this.closeChatbot();
        } else {
            this.openChatbot();
        }
    }
    
    openChatbot() {
        this.panel.classList.add('active');
        this.isOpen = true;
        this.chatInput.focus();
        
        // 알림 제거
        document.getElementById('chatbot-notification').style.display = 'none';
    }
    
    closeChatbot() {
        this.panel.classList.remove('active');
        this.isOpen = false;
    }
    
    handleInputChange() {
        const length = this.chatInput.value.length;
        this.charCount.textContent = `${length}/500`;
        this.sendBtn.disabled = length === 0;
    }
    
    async handleChatSubmit(e) {
        e.preventDefault();
        
        const message = this.chatInput.value.trim();
        if (!message) return;
        
        // 사용자 메시지 표시
        this.addMessage(message, 'user');
        this.chatInput.value = '';
        this.handleInputChange();
        
        // AI 응답 대기 표시
        this.showTypingIndicator();
        
        try {
            const response = await this.sendChatRequest(message);
            this.hideTypingIndicator();
            
            if (response.success) {
                this.handleBotResponse(response.data);
            } else {
                this.addMessage('죄송합니다. 오류가 발생했습니다: ' + response.message, 'bot');
            }
        } catch (error) {
            this.hideTypingIndicator();
            this.addMessage('네트워크 오류가 발생했습니다. 다시 시도해주세요.', 'bot');
            console.error('Chat error:', error);
        }
    }
    
    async sendChatRequest(message) {
        const response = await fetch(this.apiBaseUrl + '/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                context: this.currentContext
            })
        });
        
        return await response.json();
    }
    
    handleBotResponse(responseData) {
        switch (responseData.type) {
            case 'answer':
            case 'guide':
                this.addMessage(responseData.message, 'bot');
                break;
                
            case 'feedback_saved':
                this.addMessage(responseData.message, 'bot');
                break;
                
            case 'error':
                this.addMessage(responseData.message, 'bot');
                break;
                
            default:
                this.addMessage(responseData.message || '알 수 없는 응답입니다.', 'bot');
        }
        
        // 피드백 폼 표시 액션
        if (responseData.data && responseData.data.action === 'show_feedback_form') {
            setTimeout(() => {
                this.showFeedbackModal();
            }, 1000);
        }
    }
    
    addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        
        if (sender === 'bot') {
            avatar.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect>
                    <circle cx="12" cy="5" r="2"></circle>
                </svg>
            `;
        } else {
            avatar.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            `;
        }
        
        const content = document.createElement('div');
        content.className = 'message-content';
        
        const textDiv = document.createElement('div');
        textDiv.className = 'message-text';
        textDiv.innerHTML = this.formatMessage(text);
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.textContent = new Date().toLocaleTimeString('ko-KR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        content.appendChild(textDiv);
        content.appendChild(timeDiv);
        
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(content);
        
        this.messagesArea.appendChild(messageDiv);
        this.scrollToBottom();
    }
    
    formatMessage(text) {
        // 간단한 마크다운 지원
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code>$1</code>')
            .replace(/\n/g, '<br>');
    }
    
    showTypingIndicator() {
        this.typingIndicator.style.display = 'block';
        this.sendBtn.disabled = true;
    }
    
    hideTypingIndicator() {
        this.typingIndicator.style.display = 'none';
        this.sendBtn.disabled = this.chatInput.value.length === 0;
    }
    
    scrollToBottom() {
        this.messagesArea.scrollTop = this.messagesArea.scrollHeight;
    }
    
    showFeedbackModal() {
        this.feedbackModal.style.display = 'flex';
        document.getElementById('feedback-title').focus();
    }
    
    closeFeedbackModal() {
        this.feedbackModal.style.display = 'none';
        this.feedbackForm.reset();
    }
    
    async handleFeedbackSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(this.feedbackForm);
        const feedbackData = {
            type: formData.get('type'),
            title: formData.get('title'),
            description: formData.get('description'),
            priority: formData.get('priority'),
            page_url: window.location.href
        };
        
        try {
            const response = await fetch(this.apiBaseUrl + '/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(feedbackData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.closeFeedbackModal();
                this.addMessage('피드백이 성공적으로 제출되었습니다. 검토 후 개선에 반영하겠습니다. 감사합니다! 🙏', 'bot');
            } else {
                alert('피드백 제출 중 오류가 발생했습니다: ' + result.message);
            }
            
        } catch (error) {
            alert('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
            console.error('Feedback error:', error);
        }
    }
    
    async loadChatHistory() {
        try {
            const response = await fetch(this.apiBaseUrl + '/history?limit=5');
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                // 기존 메시지들 제거 (초기 메시지와 빠른 질문 버튼 제외)
                const existingMessages = this.messagesArea.querySelectorAll('.message:not(:first-child)');
                existingMessages.forEach(msg => msg.remove());
                
                // 최근 대화 일부 표시
                result.data.reverse().forEach(item => {
                    this.addMessage(item.user_message, 'user');
                    this.addMessage(item.bot_response.message, 'bot');
                });
            }
        } catch (error) {
            console.log('Chat history load failed:', error);
        }
    }
    
    sendMessage(message) {
        this.chatInput.value = message;
        this.chatForm.requestSubmit();
    }
}

// 챗봇 초기화
document.addEventListener('DOMContentLoaded', () => {
    window.bpmChatbot = new BPMChatbot();
});
</script>