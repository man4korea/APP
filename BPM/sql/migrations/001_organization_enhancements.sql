-- 📁 C:\xampp\htdocs\BPM\sql\migrations\001_organization_enhancements.sql
-- Create at 2508041600 Ver1.00

-- BPM 조직관리 모듈 데이터베이스 확장
-- 조직도 시각화 및 본점-지점 승인 시스템 강화

-- ===========================
-- 1. 조직도 시각화 정보 테이블
-- ===========================

-- 조직도 노드 위치 정보 테이블
CREATE TABLE bpm_org_chart_positions (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    entity_type ENUM('company', 'department') NOT NULL,
    entity_id CHAR(36) NOT NULL COMMENT '회사 또는 부서 ID',
    x_position DECIMAL(10,2) DEFAULT 0 COMMENT 'X 좌표',
    y_position DECIMAL(10,2) DEFAULT 0 COMMENT 'Y 좌표',
    width DECIMAL(10,2) DEFAULT 150 COMMENT '노드 너비',
    height DECIMAL(10,2) DEFAULT 80 COMMENT '노드 높이',
    level INT DEFAULT 0 COMMENT '조직도 레벨 (0: 최상위)',
    order_in_level INT DEFAULT 0 COMMENT '같은 레벨 내 순서',
    is_collapsed BOOLEAN DEFAULT FALSE COMMENT '하위 노드 접힌 상태',
    node_style JSON COMMENT '노드 스타일 설정',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_company_entity (company_id, entity_type, entity_id),
    INDEX idx_position_company (company_id),
    INDEX idx_position_level (level),
    INDEX idx_position_order (level, order_in_level)
);

-- ===========================
-- 2. 조직 변경 승인 시스템
-- ===========================

-- 조직 변경 요청 테이블
CREATE TABLE bpm_org_change_requests (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    request_type ENUM('create_department', 'modify_department', 'delete_department', 'move_department', 'assign_head') NOT NULL,
    entity_type ENUM('company', 'department') NOT NULL,
    entity_id CHAR(36) NOT NULL COMMENT '대상 엔티티 ID',
    
    -- 변경 내용
    change_data JSON NOT NULL COMMENT '변경할 데이터',
    old_data JSON COMMENT '기존 데이터 (수정/삭제시)',
    
    -- 요청자 및 승인자
    requested_by CHAR(36) NOT NULL,
    approval_required_role ENUM('founder', 'admin', 'process_owner') DEFAULT 'admin',
    
    -- 승인 상태
    status ENUM('pending', 'approved', 'rejected', 'auto_approved', 'cancelled') DEFAULT 'pending',
    approved_by CHAR(36) NULL,
    approved_at TIMESTAMP NULL,
    rejected_by CHAR(36) NULL,
    rejected_at TIMESTAMP NULL,
    rejection_reason TEXT,
    
    -- 실행 상태
    executed BOOLEAN DEFAULT FALSE,
    executed_at TIMESTAMP NULL,
    execution_result JSON COMMENT '실행 결과',
    
    -- 만료 설정
    expires_at TIMESTAMP NOT NULL DEFAULT (DATE_ADD(NOW(), INTERVAL 7 DAY)),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    FOREIGN KEY (rejected_by) REFERENCES bpm_users(id) ON DELETE SET NULL,
    
    INDEX idx_request_company (company_id),
    INDEX idx_request_status (status),
    INDEX idx_request_type (request_type),
    INDEX idx_request_expires (expires_at),
    INDEX idx_request_requester (requested_by)
);

-- ===========================
-- 3. 조직 히스토리 및 감사 로그
-- ===========================

-- 조직 변경 히스토리 테이블
CREATE TABLE bpm_org_change_history (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    company_id CHAR(36) NOT NULL,
    entity_type ENUM('company', 'department') NOT NULL,
    entity_id CHAR(36) NOT NULL,
    change_type ENUM('created', 'updated', 'deleted', 'moved', 'head_assigned', 'head_removed') NOT NULL,
    
    -- 변경 내용
    field_name VARCHAR(100) COMMENT '변경된 필드명',
    old_value TEXT COMMENT '이전 값',
    new_value TEXT COMMENT '새 값',
    change_details JSON COMMENT '상세 변경 정보',
    
    -- 변경자 정보
    changed_by CHAR(36) NOT NULL,
    change_reason TEXT COMMENT '변경 사유',
    request_id CHAR(36) COMMENT '관련 변경 요청 ID',
    
    -- IP 및 추적 정보
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (request_id) REFERENCES bpm_org_change_requests(id) ON DELETE SET NULL,
    
    INDEX idx_history_company (company_id),
    INDEX idx_history_entity (entity_type, entity_id),
    INDEX idx_history_type (change_type),
    INDEX idx_history_user (changed_by),
    INDEX idx_history_time (timestamp),
    
    -- 연도별 파티셔닝
    PARTITION BY RANGE (YEAR(timestamp)) (
        PARTITION p2024 VALUES LESS THAN (2025),
        PARTITION p2025 VALUES LESS THAN (2026),
        PARTITION p2026 VALUES LESS THAN (2027),
        PARTITION p_future VALUES LESS THAN MAXVALUE
    )
);

-- ===========================
-- 4. 부서별 추가 정보 테이블
-- ===========================

-- 부서 상세 정보 테이블
CREATE TABLE bpm_department_details (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    department_id CHAR(36) NOT NULL,
    
    -- 부서 상세 정보
    mission_statement TEXT COMMENT '부서 미션',
    responsibilities JSON COMMENT '주요 책임 사항',
    goals JSON COMMENT '부서 목표',
    
    -- 운영 정보
    operating_hours JSON COMMENT '운영 시간',
    location VARCHAR(255) COMMENT '부서 위치',
    contact_info JSON COMMENT '연락처 정보',
    
    -- 예산 및 리소스
    annual_budget DECIMAL(15,2) COMMENT '연간 예산',
    headcount_limit INT COMMENT '정원',
    current_headcount INT DEFAULT 0 COMMENT '현재 인원',
    
    -- 성과 지표
    kpi_metrics JSON COMMENT 'KPI 지표',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (department_id) REFERENCES bpm_departments(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_department_details (department_id),
    INDEX idx_details_location (location),
    INDEX idx_details_budget (annual_budget)
);

-- ===========================
-- 5. 조직도 템플릿 시스템
-- ===========================

-- 조직도 템플릿 테이블
CREATE TABLE bpm_org_chart_templates (
    id CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    template_name VARCHAR(255) NOT NULL,
    description TEXT,
    
    -- 템플릿 설정
    layout_type ENUM('tree', 'matrix', 'circular', 'hierarchical') DEFAULT 'hierarchical',
    default_node_style JSON COMMENT '기본 노드 스타일',
    connection_style JSON COMMENT '연결선 스타일',
    color_scheme JSON COMMENT '색상 체계',
    
    -- 사용 권한
    is_public BOOLEAN DEFAULT TRUE COMMENT '공개 템플릿 여부',
    created_by CHAR(36) NOT NULL,
    company_id CHAR(36) COMMENT '회사별 전용 템플릿',
    
    -- 사용 통계
    usage_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES bpm_users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES bpm_companies(id) ON DELETE CASCADE,
    
    INDEX idx_template_public (is_public),
    INDEX idx_template_company (company_id),
    INDEX idx_template_usage (usage_count DESC)
);

-- ===========================
-- 6. 조직관리 뷰 생성
-- ===========================

-- 조직 계층구조 뷰
CREATE VIEW bpm_org_hierarchy AS
WITH RECURSIVE org_tree AS (
    -- 최상위 회사
    SELECT 
        'company' as entity_type,
        c.id as entity_id,
        c.company_name as name,
        c.id as company_id,
        NULL as parent_id,
        0 as level,
        CAST(c.company_name AS CHAR(1000)) as path
    FROM bpm_companies c
    WHERE c.status = 'active'
    
    UNION ALL
    
    -- 부서들
    SELECT 
        'department' as entity_type,
        d.id as entity_id,
        d.department_name as name,
        d.company_id,
        COALESCE(d.parent_department_id, d.company_id) as parent_id,
        d.level + 1 as level,
        CONCAT(ot.path, ' > ', d.department_name) as path
    FROM bpm_departments d
    JOIN org_tree ot ON (
        (ot.entity_type = 'company' AND d.company_id = ot.entity_id AND d.parent_department_id IS NULL) OR
        (ot.entity_type = 'department' AND d.parent_department_id = ot.entity_id)
    )
)
SELECT 
    ot.*,
    ocp.x_position,
    ocp.y_position,
    ocp.is_collapsed,
    CASE 
        WHEN ot.entity_type = 'company' THEN c.representative_name
        WHEN ot.entity_type = 'department' THEN u.name
        ELSE NULL
    END as head_name
FROM org_tree ot
LEFT JOIN bpm_org_chart_positions ocp ON (
    ocp.company_id = ot.company_id AND 
    ocp.entity_type = ot.entity_type AND 
    ocp.entity_id = ot.entity_id
)
LEFT JOIN bpm_companies c ON (ot.entity_type = 'company' AND c.id = ot.entity_id)
LEFT JOIN bpm_departments d ON (ot.entity_type = 'department' AND d.id = ot.entity_id)
LEFT JOIN bpm_users u ON d.head_user_id = u.id;

-- 부서별 구성원 현황 뷰
CREATE VIEW bpm_department_members AS
SELECT 
    d.id as department_id,
    d.company_id,
    d.department_name,
    COUNT(cu.user_id) as total_members,
    SUM(CASE WHEN cu.role_type = 'admin' THEN 1 ELSE 0 END) as admin_count,
    SUM(CASE WHEN cu.role_type = 'process_owner' THEN 1 ELSE 0 END) as process_owner_count,
    SUM(CASE WHEN cu.role_type = 'member' THEN 1 ELSE 0 END) as member_count,
    dd.headcount_limit,
    dd.current_headcount,
    u.name as head_name,
    u.email as head_email
FROM bpm_departments d
LEFT JOIN bpm_company_users cu ON (
    d.company_id = cu.company_id AND 
    cu.department = d.department_name AND 
    cu.is_active = TRUE
)
LEFT JOIN bpm_department_details dd ON d.id = dd.department_id
LEFT JOIN bpm_users u ON d.head_user_id = u.id
GROUP BY d.id, d.company_id, d.department_name, dd.headcount_limit, dd.current_headcount, u.name, u.email;

-- ===========================
-- 7. 조직관리 프로시저
-- ===========================

-- 조직도 자동 레이아웃 프로시저
DELIMITER //
CREATE PROCEDURE bpm_auto_layout_org_chart(
    IN p_company_id CHAR(36),
    IN p_layout_type ENUM('tree', 'matrix', 'circular', 'hierarchical')
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_entity_id CHAR(36);
    DECLARE v_entity_type ENUM('company', 'department');
    DECLARE v_level INT;
    DECLARE v_x_pos DECIMAL(10,2);
    DECLARE v_y_pos DECIMAL(10,2);
    DECLARE v_level_count INT DEFAULT 0;
    
    DECLARE level_cursor CURSOR FOR
        SELECT entity_id, entity_type, level
        FROM bpm_org_hierarchy
        WHERE company_id = p_company_id
        ORDER BY level, entity_id;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- 기존 위치 정보 삭제
    DELETE FROM bpm_org_chart_positions WHERE company_id = p_company_id;
    
    OPEN level_cursor;
    
    position_loop: LOOP
        FETCH level_cursor INTO v_entity_id, v_entity_type, v_level;
        IF done THEN
            LEAVE position_loop;
        END IF;
        
        -- 레벨별 X 위치 계산
        SELECT COUNT(*) INTO v_level_count
        FROM bpm_org_hierarchy
        WHERE company_id = p_company_id AND level = v_level AND entity_id <= v_entity_id;
        
        -- 위치 계산 (트리 레이아웃)
        IF p_layout_type = 'tree' THEN
            SET v_x_pos = v_level_count * 200;
            SET v_y_pos = v_level * 120;
        ELSEIF p_layout_type = 'hierarchical' THEN
            SET v_x_pos = v_level_count * 180;
            SET v_y_pos = v_level * 100;
        ELSE
            SET v_x_pos = v_level_count * 160;
            SET v_y_pos = v_level * 110;
        END IF;
        
        -- 위치 정보 삽입
        INSERT INTO bpm_org_chart_positions 
        (company_id, entity_type, entity_id, x_position, y_position, level, order_in_level)
        VALUES 
        (p_company_id, v_entity_type, v_entity_id, v_x_pos, v_y_pos, v_level, v_level_count);
        
    END LOOP;
    
    CLOSE level_cursor;
END //
DELIMITER ;

-- 부서 이동 프로시저
DELIMITER //
CREATE PROCEDURE bpm_move_department(
    IN p_department_id CHAR(36),
    IN p_new_parent_id CHAR(36),
    IN p_user_id CHAR(36)
)
BEGIN
    DECLARE v_company_id CHAR(36);
    DECLARE v_old_parent_id CHAR(36);
    DECLARE v_dept_name VARCHAR(255);
    
    -- 현재 부서 정보 조회
    SELECT company_id, parent_department_id, department_name
    INTO v_company_id, v_old_parent_id, v_dept_name
    FROM bpm_departments
    WHERE id = p_department_id;
    
    -- 부서 이동 실행
    UPDATE bpm_departments
    SET parent_department_id = p_new_parent_id,
        updated_at = NOW()
    WHERE id = p_department_id;
    
    -- 히스토리 기록
    INSERT INTO bpm_org_change_history
    (company_id, entity_type, entity_id, change_type, field_name, old_value, new_value, changed_by)
    VALUES
    (v_company_id, 'department', p_department_id, 'moved', 'parent_department_id', 
     v_old_parent_id, p_new_parent_id, p_user_id);
     
    -- 조직도 레이아웃 자동 업데이트
    CALL bpm_auto_layout_org_chart(v_company_id, 'hierarchical');
    
END //
DELIMITER ;

-- ===========================
-- 8. 인덱스 최적화
-- ===========================

-- 기존 테이블 인덱스 추가
CREATE INDEX idx_companies_type_status ON bpm_companies (company_type, status);
CREATE INDEX idx_departments_company_level ON bpm_departments (company_id, level);
CREATE INDEX idx_departments_head_user ON bpm_departments (head_user_id);

-- 복합 인덱스 추가
CREATE INDEX idx_org_positions_lookup ON bpm_org_chart_positions (company_id, entity_type, level);
CREATE INDEX idx_change_requests_company_status ON bpm_org_change_requests (company_id, status, expires_at);

-- ===========================
-- 9. 기본 데이터 및 설정
-- ===========================

-- 조직관리 시스템 설정
INSERT INTO bpm_system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('org_chart_auto_layout', 'true', 'boolean', '조직도 자동 레이아웃 활성화', FALSE),
('org_change_approval_required', 'true', 'boolean', '조직 변경 승인 필요', FALSE),
('org_chart_default_template', 'hierarchical', 'string', '기본 조직도 템플릿', TRUE),
('dept_head_assignment_approval', 'admin', 'string', '부서장 지정 승인 권한', FALSE),
('max_org_levels', '10', 'number', '최대 조직 레벨', FALSE);

-- 기본 조직도 템플릿
INSERT INTO bpm_org_chart_templates 
(template_name, description, layout_type, default_node_style, connection_style, color_scheme, is_public, created_by)
VALUES
('기본 계층형', '표준 계층형 조직도 템플릿', 'hierarchical', 
 '{"width": 150, "height": 80, "borderRadius": 8, "fontSize": 14}',
 '{"type": "straight", "color": "#ddd", "width": 2}',
 '{"primary": "#ff6b6b", "secondary": "#fff5f5", "text": "#333"}',
 TRUE, (SELECT id FROM bpm_users LIMIT 1)),
('트리형', '트리 구조 조직도 템플릿', 'tree',
 '{"width": 140, "height": 70, "borderRadius": 6, "fontSize": 13}',
 '{"type": "curved", "color": "#ccc", "width": 1}',
 '{"primary": "#ff6b6b", "secondary": "#fff5f5", "text": "#333"}',
 TRUE, (SELECT id FROM bpm_users LIMIT 1));

-- ===========================
-- 10. 조직관리 전용 권한 확장
-- ===========================

-- 조직관리 모듈별 권한 매트릭스 (JSON 형태로 저장)
INSERT INTO bpm_system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('org_module_permissions', '{
  "founder": {
    "companies": ["view", "create", "edit", "delete"],
    "departments": ["view", "create", "edit", "delete", "move"],
    "org_chart": ["view", "edit", "template"],
    "approvals": ["view", "approve", "reject", "bypass"]
  },
  "admin": {
    "companies": ["view", "edit"],
    "departments": ["view", "create", "edit", "move"],
    "org_chart": ["view", "edit"],
    "approvals": ["view", "approve", "reject"]
  },
  "process_owner": {
    "companies": ["view"],
    "departments": ["view", "create_sub"],
    "org_chart": ["view"],
    "approvals": ["view", "request"]
  },
  "member": {
    "companies": ["view"],
    "departments": ["view"],
    "org_chart": ["view"],
    "approvals": ["view"]
  }
}', 'json', '조직관리 모듈 권한 매트릭스', FALSE);