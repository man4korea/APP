-- BPR Hub Sample Data
-- Business Process Reengineering Management System
-- Created: 2025-01-26

USE bpr_hub;

-- 기존 데이터 정리 (개발 환경에서만 사용)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE ai_usage_log;
TRUNCATE TABLE tasks;
TRUNCATE TABLE process_maps;
TRUNCATE TABLE organization_members;
TRUNCATE TABLE user_subscriptions;
TRUNCATE TABLE organizations;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SAMPLE USERS - 샘플 사용자 데이터
-- ============================================================================

INSERT INTO users (email, password_hash, first_name, last_name, phone, login_type, email_verified, is_active) VALUES
-- 일반 이메일 사용자
('demo@bpr.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '데모', '사용자', '010-1234-5678', 'email', TRUE, TRUE),
('admin@bpr.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '관리자', '사용자', '010-1234-5679', 'email', TRUE, TRUE),
('manager@bpr.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '매니저', '사용자', '010-1234-5680', 'email', TRUE, TRUE),

-- 소셜 로그인 사용자 (카카오)
('kakao_user_demo@kakao.com', '', '카카오', '사용자', '', 'kakao', TRUE, TRUE),
('kakao_manager@kakao.com', '', '카카오', '매니저', '', 'kakao', TRUE, TRUE),

-- 소셜 로그인 사용자 (구글)
('google_user_demo@gmail.com', '', '구글', '사용자', '', 'google', TRUE, TRUE),
('google_admin@gmail.com', '', '구글', '관리자', '', 'google', TRUE, TRUE),

-- 추가 테스트 사용자
('john.doe@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '010-2345-6789', 'email', TRUE, TRUE),
('jane.smith@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '010-3456-7890', 'email', TRUE, TRUE),
('mike.johnson@startup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Johnson', '010-4567-8901', 'email', TRUE, TRUE);

-- ============================================================================
-- SAMPLE ORGANIZATIONS - 샘플 조직 데이터
-- ============================================================================

INSERT INTO organizations (name, slug, description, owner_id, timezone, language, max_members) VALUES
('Demo Corporation', 'demo-corp', 'BPR Hub 데모용 회사입니다. 다양한 비즈니스 프로세스를 테스트할 수 있습니다.', 1, 'Asia/Seoul', 'ko', 50),
('Tech Startup Inc', 'tech-startup', '혁신적인 기술 스타트업으로 빠른 프로세스 개선이 필요합니다.', 8, 'Asia/Seoul', 'ko', 25),
('Global Enterprise', 'global-enterprise', '글로벌 기업으로 복잡한 조직 구조를 가지고 있습니다.', 7, 'Asia/Seoul', 'ko', 200),
('Small Business', 'small-biz', '소규모 비즈니스로 효율적인 프로세스 관리가 중요합니다.', 10, 'Asia/Seoul', 'ko', 15),
('Consulting Firm', 'consulting-firm', '컨설팅 회사로 클라이언트의 BPR을 지원합니다.', 2, 'Asia/Seoul', 'ko', 30);

-- ============================================================================
-- SAMPLE ORGANIZATION MEMBERS - 조직 구성원
-- ============================================================================
-- 트리거에 의해 소유자는 자동으로 추가되므로 추가 멤버만 삽입

INSERT INTO organization_members (organization_id, user_id, role, title, department, status, joined_at) VALUES
-- Demo Corporation 멤버들
(1, 2, 'admin', 'IT 관리자', 'IT팀', 'active', NOW()),
(1, 3, 'manager', '운영 매니저', '운영팀', 'active', NOW()),
(1, 4, 'member', '프로세스 분석가', '기획팀', 'active', NOW()),
(1, 5, 'member', '데이터 분석가', 'IT팀', 'active', NOW()),

-- Tech Startup Inc 멤버들  
(2, 9, 'admin', 'CTO', '개발팀', 'active', NOW()),
(2, 6, 'member', '개발자', '개발팀', 'active', NOW()),

-- Global Enterprise 멤버들
(3, 2, 'manager', '프로젝트 매니저', 'PMO', 'active', NOW()),
(3, 9, 'member', '비즈니스 분석가', '전략기획팀', 'active', NOW()),

-- Small Business 멤버들
(4, 3, 'admin', '운영 책임자', '운영팀', 'active', NOW()),

-- Consulting Firm 멤버들
(5, 7, 'admin', '시니어 컨설턴트', '컨설팅팀', 'active', NOW()),
(5, 8, 'manager', '주니어 컨설턴트', '컨설팅팀', 'active', NOW());

-- ============================================================================
-- SAMPLE USER SUBSCRIPTIONS - 구독 정보 업데이트
-- ============================================================================
-- 트리거에 의해 기본 무료 구독이 생성되므로 프리미엄 사용자만 업데이트

UPDATE user_subscriptions SET 
    plan_type = 'premium',
    plan_name = 'Premium Plan',
    max_process_maps = 50,
    max_organization_members = 100,
    ai_tokens_limit = 10000,
    expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR)
WHERE user_id IN (2, 7, 8); -- admin@bpr.com, google_admin@gmail.com, john.doe@company.com

UPDATE user_subscriptions SET 
    plan_type = 'enterprise',
    plan_name = 'Enterprise Plan',
    max_process_maps = 500,
    max_organization_members = 1000,
    ai_tokens_limit = 100000,
    expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR)
WHERE user_id = 1; -- demo@bpr.com (데모용 엔터프라이즈)

-- ============================================================================
-- SAMPLE PROCESS MAPS - 프로세스 맵
-- ============================================================================

INSERT INTO process_maps (title, description, organization_id, created_by, map_data, version, status, visibility) VALUES
('고객 주문 처리 프로세스', '온라인 쇼핑몰의 주문 접수부터 배송까지의 전체 프로세스입니다.', 1, 1, 
'{"nodes": [{"id": "start", "type": "start", "label": "주문 접수"}, {"id": "validate", "type": "process", "label": "주문 검증"}, {"id": "payment", "type": "process", "label": "결제 처리"}, {"id": "ship", "type": "process", "label": "배송 준비"}, {"id": "end", "type": "end", "label": "배송 완료"}], "edges": [{"from": "start", "to": "validate"}, {"from": "validate", "to": "payment"}, {"from": "payment", "to": "ship"}, {"from": "ship", "to": "end"}]}', 
1, 'active', 'organization'),

('신입사원 온보딩 프로세스', '신입사원의 입사부터 업무 시작까지의 온보딩 프로세스입니다.', 1, 2,
'{"nodes": [{"id": "start", "type": "start", "label": "입사 확정"}, {"id": "docs", "type": "process", "label": "서류 작성"}, {"id": "training", "type": "process", "label": "신입 교육"}, {"id": "assignment", "type": "process", "label": "부서 배정"}, {"id": "end", "type": "end", "label": "업무 시작"}], "edges": [{"from": "start", "to": "docs"}, {"from": "docs", "to": "training"}, {"from": "training", "to": "assignment"}, {"from": "assignment", "to": "end"}]}',
1, 'active', 'organization'),

('제품 개발 프로세스', '아이디어부터 제품 출시까지의 개발 프로세스입니다.', 2, 8,
'{"nodes": [{"id": "idea", "type": "start", "label": "아이디어 도출"}, {"id": "research", "type": "process", "label": "시장 조사"}, {"id": "design", "type": "process", "label": "설계"}, {"id": "develop", "type": "process", "label": "개발"}, {"id": "test", "type": "process", "label": "테스트"}, {"id": "launch", "type": "end", "label": "출시"}], "edges": [{"from": "idea", "to": "research"}, {"from": "research", "to": "design"}, {"from": "design", "to": "develop"}, {"from": "develop", "to": "test"}, {"from": "test", "to": "launch"}]}',
1, 'active', 'organization'),

('고객 서비스 프로세스', '고객 문의부터 해결까지의 서비스 프로세스입니다.', 3, 7,
'{"nodes": [{"id": "inquiry", "type": "start", "label": "고객 문의"}, {"id": "classify", "type": "process", "label": "문의 분류"}, {"id": "assign", "type": "process", "label": "담당자 배정"}, {"id": "resolve", "type": "process", "label": "문제 해결"}, {"id": "followup", "type": "end", "label": "만족도 조사"}], "edges": [{"from": "inquiry", "to": "classify"}, {"from": "classify", "to": "assign"}, {"from": "assign", "to": "resolve"}, {"from": "resolve", "to": "followup"}]}',
1, 'active', 'organization'),

('재무 결산 프로세스', '월별 재무 결산 프로세스입니다.', 4, 10,
'{"nodes": [{"id": "collect", "type": "start", "label": "데이터 수집"}, {"id": "validate", "type": "process", "label": "데이터 검증"}, {"id": "calculate", "type": "process", "label": "재무 계산"}, {"id": "report", "type": "process", "label": "보고서 작성"}, {"id": "approval", "type": "end", "label": "승인"}], "edges": [{"from": "collect", "to": "validate"}, {"from": "validate", "to": "calculate"}, {"from": "calculate", "to": "report"}, {"from": "report", "to": "approval"}]}',
1, 'active', 'private');

-- ============================================================================
-- SAMPLE TASKS - 작업 데이터
-- ============================================================================

INSERT INTO tasks (title, description, organization_id, process_map_id, task_type, category, processing_time, lead_time, assigned_to, status, priority, progress_percentage, due_date) VALUES
-- Demo Corporation 작업들
('주문 데이터 검증', '고객 주문 정보의 정확성을 확인하는 작업입니다.', 1, 1, 'DB', '데이터 검증', 15, 30, 4, 'completed', 'high', 100, '2025-01-20'),
('결제 시스템 연동', '외부 결제 게이트웨이와의 연동 작업입니다.', 1, 1, 'CM', '시스템 연동', 120, 240, 5, 'in_progress', 'high', 75, '2025-01-30'),
('배송 상태 업데이트', '배송 상태를 실시간으로 업데이트하는 작업입니다.', 1, 1, 'DM', '상태 관리', 30, 60, 3, 'todo', 'medium', 0, '2025-02-05'),

('신입사원 정보 입력', '신입사원의 기본 정보를 시스템에 입력합니다.', 1, 2, 'DB', '정보 입력', 45, 60, 2, 'completed', 'medium', 100, '2025-01-15'),
('교육 일정 관리', '신입사원 교육 일정을 관리합니다.', 1, 2, 'CM', '일정 관리', 90, 180, 3, 'in_progress', 'medium', 60, '2025-02-01'),
('부서 배정 승인', '신입사원의 부서 배정을 승인 처리합니다.', 1, 2, 'DM', '승인 처리', 20, 1440, 2, 'todo', 'low', 0, '2025-02-10'),

-- Tech Startup Inc 작업들
('시장 조사 데이터 수집', '제품 개발을 위한 시장 조사 데이터를 수집합니다.', 2, 3, 'DB', '데이터 수집', 180, 2880, 9, 'completed', 'high', 100, '2025-01-18'),
('프로토타입 개발', '제품의 초기 프로토타입을 개발합니다.', 2, 3, 'CM', '개발', 2400, 4320, 6, 'in_progress', 'high', 40, '2025-02-15'),
('사용자 테스트 실행', '프로토타입에 대한 사용자 테스트를 실행합니다.', 2, 3, 'DM', '테스트', 480, 720, 9, 'todo', 'medium', 0, '2025-02-20'),

-- Global Enterprise 작업들
('고객 문의 분류', '접수된 고객 문의를 카테고리별로 분류합니다.', 3, 4, 'DB', '분류', 10, 15, 9, 'completed', 'high', 100, '2025-01-22'),
('전문가 배정', '문의 유형에 따라 적절한 전문가를 배정합니다.', 3, 4, 'CM', '배정', 30, 120, 2, 'in_progress', 'high', 80, '2025-01-28'),
('해결 방안 수립', '고객 문제에 대한 해결 방안을 수립합니다.', 3, 4, 'DM', '문제 해결', 60, 240, 2, 'review', 'medium', 90, '2025-02-03'),

-- Small Business 작업들
('재무 데이터 수집', '월별 재무 데이터를 수집합니다.', 4, 5, 'DB', '데이터 수집', 120, 240, 3, 'completed', 'high', 100, '2025-01-25'),
('손익 계산', '수집된 데이터를 바탕으로 손익을 계산합니다.', 4, 5, 'CM', '계산', 90, 180, 10, 'in_progress', 'high', 70, '2025-01-31'),
('결산 보고서 작성', '최종 결산 보고서를 작성합니다.', 4, 5, 'DM', '보고서 작성', 180, 360, 10, 'todo', 'medium', 0, '2025-02-07');

-- ============================================================================
-- SAMPLE AI USAGE LOG - AI 사용 로그
-- ============================================================================

INSERT INTO ai_usage_log (user_id, organization_id, feature_type, request_data, response_data, tokens_used, cost, status) VALUES
(1, 1, 'task_analysis', 
'{"task_id": 1, "analysis_type": "optimization"}',
'{"suggestions": ["자동화 가능한 검증 룰 추가", "배치 처리로 효율성 개선"], "optimization_score": 85, "estimated_time_saving": "20%"}',
250, 0.0025, 'success'),

(2, 1, 'process_optimization',
'{"process_map_id": 1, "optimization_focus": "time_reduction"}',
'{"recommendations": ["병렬 처리 도입", "중복 단계 제거"], "potential_improvement": "35% 시간 단축"}',
180, 0.0018, 'success'),

(8, 2, 'task_manual_generation',
'{"task_id": 7, "manual_type": "user_guide"}',
'{"manual_content": "시장 조사 데이터 수집 가이드라인...", "sections": ["준비사항", "수집 방법", "품질 검증"]}',
320, 0.0032, 'success'),

(7, 3, 'process_bottleneck_analysis',
'{"process_map_id": 4, "analysis_depth": "detailed"}',
'{"bottlenecks": [{"step": "전문가 배정", "delay_cause": "업무량 불균형"}], "solutions": ["로드밸런싱 도입"]}',
150, 0.0015, 'success');

-- ============================================================================
-- 데이터 확인 쿼리
-- ============================================================================

-- 삽입된 데이터 확인
SELECT 'Users created:' as info, COUNT(*) as count FROM users
UNION ALL
SELECT 'Organizations created:', COUNT(*) FROM organizations  
UNION ALL
SELECT 'Organization members:', COUNT(*) FROM organization_members
UNION ALL
SELECT 'Process maps created:', COUNT(*) FROM process_maps
UNION ALL
SELECT 'Tasks created:', COUNT(*) FROM tasks
UNION ALL
SELECT 'AI usage logs:', COUNT(*) FROM ai_usage_log;

-- 조직별 요약 정보
SELECT 
    o.name as organization_name,
    COUNT(DISTINCT om.user_id) as member_count,
    COUNT(DISTINCT pm.id) as process_map_count,
    COUNT(DISTINCT t.id) as task_count
FROM organizations o
LEFT JOIN organization_members om ON o.id = om.organization_id AND om.status = 'active'
LEFT JOIN process_maps pm ON o.id = pm.organization_id
LEFT JOIN tasks t ON o.id = t.organization_id
GROUP BY o.id, o.name
ORDER BY o.name;

SELECT 'Sample data inserted successfully!' as message;