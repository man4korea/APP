-- 📁 C:\xampp\htdocs\BPM\sql\performance-indexes.sql
-- Create at 2508031147 Ver1.00

/**
 * BPM 성능 최적화 인덱스 스크립트
 * 목적: 주요 쿼리 성능 향상을 위한 복합 인덱스 생성
 * Tech Lead 권장사항 반영
 */

-- ================================
-- 1. 멀티테넌트 기본 인덱스
-- ================================

-- 회사별 사용자 조회 최적화
CREATE INDEX idx_company_users_lookup 
ON bpm_company_users(company_id, user_id, status, role) 
COMMENT '회사별 사용자 조회 및 권한 확인 최적화';

-- 회사별 사용자 권한 빠른 조회
CREATE INDEX idx_company_users_permissions 
ON bpm_company_users(user_id, company_id, status) 
COMMENT '사용자별 회사 권한 조회 최적화';

-- ================================
-- 2. 조직관리 모듈 인덱스
-- ================================

-- 회사별 부서 계층구조 조회
CREATE INDEX idx_departments_hierarchy 
ON bpm_departments(company_id, parent_id, status, sort_order) 
COMMENT '부서 계층구조 조회 최적화';

-- 부서별 사용자 배치 조회
CREATE INDEX idx_department_assignments 
ON bpm_department_assignments(department_id, user_id, status, assigned_at) 
COMMENT '부서별 구성원 조회 최적화';

-- ================================
-- 3. Task 관리 모듈 인덱스  
-- ================================

-- 회사별 Task 성능 조회 (가장 자주 사용되는 쿼리)
CREATE INDEX idx_tasks_performance 
ON bpm_tasks(company_id, status, priority, created_at) 
COMMENT 'Task 목록 조회 및 필터링 최적화';

-- 담당자별 Task 조회
CREATE INDEX idx_tasks_assignee 
ON bpm_tasks(assigned_to, status, due_date) 
COMMENT '담당자별 Task 조회 최적화';

-- Task 카테고리별 조회
CREATE INDEX idx_tasks_category 
ON bpm_tasks(company_id, category, type, status) 
COMMENT 'Task 분류 및 통계 조회 최적화';

-- Task 시간 추적 조회
CREATE INDEX idx_task_time_tracking 
ON bpm_task_time_logs(task_id, user_id, created_at) 
COMMENT 'Task 시간 추적 데이터 조회 최적화';

-- ================================
-- 4. 문서관리 모듈 인덱스
-- ================================

-- 회사별 문서 검색 최적화
CREATE INDEX idx_documents_search 
ON bpm_documents(company_id, category, title, status) 
COMMENT '문서 검색 및 분류 최적화';

-- 문서 버전 관리 조회
CREATE INDEX idx_document_versions 
ON bpm_document_versions(document_id, version, created_at) 
COMMENT '문서 버전 히스토리 조회 최적화';

-- 문서 권한 확인
CREATE INDEX idx_document_permissions 
ON bpm_document_permissions(document_id, user_id, permission_type) 
COMMENT '문서별 사용자 권한 확인 최적화';

-- ================================
-- 5. 인증 및 세션 인덱스
-- ================================

-- 사용자 로그인 조회
CREATE INDEX idx_users_login 
ON bpm_users(email, status, email_verified_at) 
COMMENT '사용자 로그인 프로세스 최적화';

-- 활성 세션 조회
CREATE INDEX idx_user_sessions 
ON bpm_user_sessions(user_id, expires_at, is_active) 
COMMENT '사용자 세션 관리 최적화';

-- JWT 토큰 블랙리스트 조회
CREATE INDEX idx_token_blacklist 
ON bpm_token_blacklist(token_id, expires_at) 
COMMENT 'JWT 토큰 블랙리스트 확인 최적화';

-- ================================
-- 6. 감사 로그 인덱스
-- ================================

-- 회사별 감사 로그 조회
CREATE INDEX idx_audit_logs_company 
ON bpm_audit_logs(company_id, created_at, action) 
COMMENT '회사별 감사 로그 조회 최적화';

-- 사용자별 감사 로그 조회
CREATE INDEX idx_audit_logs_user 
ON bpm_audit_logs(user_id, created_at, action) 
COMMENT '사용자별 활동 로그 조회 최적화';

-- IP별 보안 로그 조회
CREATE INDEX idx_audit_logs_security 
ON bpm_audit_logs(ip_address, action, created_at) 
COMMENT '보안 이벤트 분석 최적화';

-- ================================
-- 7. 알림 시스템 인덱스
-- ================================

-- 사용자별 알림 조회
CREATE INDEX idx_notifications_user 
ON bpm_notifications(user_id, read_at, created_at) 
COMMENT '사용자별 알림 조회 최적화';

-- 알림 타입별 조회
CREATE INDEX idx_notifications_type 
ON bpm_notifications(notification_type, created_at, status) 
COMMENT '알림 타입별 분석 최적화';

-- ================================
-- 8. 파일 관리 인덱스
-- ================================

-- 회사별 파일 조회
CREATE INDEX idx_files_company 
ON bpm_files(company_id, file_type, created_at) 
COMMENT '회사별 파일 관리 최적화';

-- 파일 해시 중복 확인
CREATE INDEX idx_files_hash 
ON bpm_files(file_hash, file_size) 
COMMENT '파일 중복 확인 및 스토리지 최적화';

-- ================================
-- 9. 캐시 테이블 인덱스
-- ================================

-- 캐시 키 조회 최적화
CREATE INDEX idx_cache_key_expiration 
ON cache(`key`, expiration) 
COMMENT '캐시 조회 및 만료 확인 최적화';

-- ================================
-- 10. 복합 비즈니스 로직 인덱스
-- ================================

-- 사용자의 회사별 권한 및 부서 정보 (JOIN 최적화)
CREATE INDEX idx_user_company_department 
ON bpm_company_users(user_id, company_id, department_id, status) 
COMMENT '사용자-회사-부서 관계 JOIN 최적화';

-- Task 담당자 변경 히스토리 조회
CREATE INDEX idx_task_assignments 
ON bpm_task_assignments(task_id, assigned_to, assigned_at) 
COMMENT 'Task 담당자 변경 히스토리 최적화';

-- 문서 접근 로그 분석
CREATE INDEX idx_document_access_logs 
ON bpm_document_access_logs(document_id, user_id, accessed_at) 
COMMENT '문서 접근 패턴 분석 최적화';

-- ================================
-- 11. 시계열 데이터 최적화
-- ================================

-- 월별 데이터 파티셔닝을 위한 인덱스
CREATE INDEX idx_audit_logs_monthly 
ON bpm_audit_logs(created_at, company_id) 
COMMENT '감사 로그 월별 파티셔닝 지원';

-- 시간별 Task 생성 통계
CREATE INDEX idx_tasks_hourly_stats 
ON bpm_tasks(created_at, company_id, status) 
COMMENT 'Task 생성 시간별 통계 최적화';

-- ================================
-- 12. 전문 검색 최적화
-- ================================

-- 전문 검색 인덱스 (MySQL 5.7+)
ALTER TABLE bpm_documents 
ADD FULLTEXT INDEX ft_documents_content (title, description, content)
COMMENT '문서 전문 검색 최적화';

ALTER TABLE bpm_tasks 
ADD FULLTEXT INDEX ft_tasks_content (title, description)
COMMENT 'Task 전문 검색 최적화';

-- ================================
-- 13. 성능 모니터링 인덱스
-- ================================

-- 쿼리 성능 로그 분석
CREATE INDEX idx_query_performance 
ON bpm_query_logs(query_hash, execution_time, created_at) 
COMMENT '쿼리 성능 분석 최적화';

-- API 호출 성능 분석
CREATE INDEX idx_api_performance 
ON bpm_api_logs(endpoint, response_time, created_at) 
COMMENT 'API 성능 분석 최적화';

-- ================================
-- 14. 인덱스 사용량 모니터링 뷰
-- ================================

-- 인덱스 사용 통계 뷰 생성
CREATE OR REPLACE VIEW v_index_usage_stats AS
SELECT 
    SCHEMA_NAME as database_name,
    TABLE_NAME as table_name,
    INDEX_NAME as index_name,
    COLUMN_NAME as column_name,
    CARDINALITY,
    CASE 
        WHEN NON_UNIQUE = 0 THEN 'UNIQUE'
        ELSE 'NON_UNIQUE'
    END as index_type
FROM information_schema.STATISTICS 
WHERE SCHEMA_NAME = DATABASE()
ORDER BY TABLE_NAME, INDEX_NAME;

-- ================================
-- 15. 인덱스 최적화 분석 프로시저
-- ================================

DELIMITER //

CREATE PROCEDURE sp_analyze_index_performance()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE table_name VARCHAR(64);
    DECLARE index_name VARCHAR(64);
    
    -- 사용되지 않는 인덱스 찾기
    DECLARE cur CURSOR FOR 
        SELECT DISTINCT 
            TABLE_NAME, INDEX_NAME
        FROM information_schema.STATISTICS 
        WHERE SCHEMA_NAME = DATABASE()
        AND INDEX_NAME != 'PRIMARY';
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- 임시 테이블 생성
    DROP TEMPORARY TABLE IF EXISTS tmp_index_analysis;
    CREATE TEMPORARY TABLE tmp_index_analysis (
        table_name VARCHAR(64),
        index_name VARCHAR(64),
        recommendation TEXT
    );
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO table_name, index_name;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- 인덱스 분석 로직 (실제 구현에서는 PERFORMANCE_SCHEMA 활용)
        INSERT INTO tmp_index_analysis 
        VALUES (table_name, index_name, '성능 분석 필요');
        
    END LOOP;
    
    CLOSE cur;
    
    -- 분석 결과 반환
    SELECT * FROM tmp_index_analysis;
    
END //

DELIMITER ;

-- ================================
-- 16. 인덱스 힌트 사용 예제
-- ================================

-- 복잡한 쿼리에서 인덱스 힌트 사용 예제
/*
-- 회사별 활성 사용자의 최근 Task 조회
SELECT t.id, t.title, t.status, u.name
FROM bpm_tasks t USE INDEX (idx_tasks_performance)
JOIN bpm_company_users cu USE INDEX (idx_company_users_lookup) 
    ON t.assigned_to = cu.user_id
JOIN bpm_users u ON cu.user_id = u.id
WHERE cu.company_id = ?
    AND cu.status = 'active'
    AND t.status IN ('pending', 'in_progress')
    AND t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY t.created_at DESC
LIMIT 20;
*/

-- ================================
-- 실행 후 확인사항
-- ================================

-- 1. 인덱스 생성 확인
SHOW INDEX FROM bpm_tasks;
SHOW INDEX FROM bpm_company_users;
SHOW INDEX FROM bpm_documents;

-- 2. 쿼리 실행 계획 확인 예제
-- EXPLAIN SELECT * FROM bpm_tasks WHERE company_id = 1 AND status = 'active';

-- 3. 인덱스 사용률 통계 확인
-- SELECT * FROM v_index_usage_stats WHERE table_name LIKE 'bmp_%';

/**
 * 주의사항:
 * 1. 인덱스 생성 후 ANALYZE TABLE 실행 권장
 * 2. 주기적으로 인덱스 사용률 모니터링 필요
 * 3. 불필요한 인덱스는 성능 저하 원인이 될 수 있음
 * 4. 대용량 테이블의 경우 인덱스 생성 시간 고려 필요
 */