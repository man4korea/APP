-- 📁 C:\xampp\htdocs\BPM\sql\demo-data.sql
-- Create at 2508031015 Ver1.00

-- BPM 시스템 데모 데이터 삽입
-- 개발 및 테스트 목적으로 사용

-- ===========================
-- 1. 데모 회사 데이터
-- ===========================

-- EASYCORP 회사 데이터
INSERT INTO bpm_companies (
    id, company_name, tax_number, business_type, company_type,
    representative_name, representative_phone, admin_email, admin_phone,
    address, postal_code, establishment_date,
    phone, email, website, status, settings
) VALUES (
    'easycorp-uuid-2025-demo-company1',
    'EASYCORP',
    '123-45-67890',
    'IT서비스업',
    'headquarters',
    '김이지',
    '02-1234-5678',
    'admin@easycorp.com',
    '02-1234-5679',
    '서울특별시 강남구 테헤란로 123',
    '06142',
    '2020-01-01',
    '02-1234-5678',
    'contact@easycorp.com',
    'https://easycorp.com',
    'active',
    '{"admin_can_assign_admin": true, "approval_required": false, "max_processes": 1000}'
);

-- ===========================
-- 2. 데모 사용자 데이터
-- ===========================

-- 관리자 사용자 (admin@easycorp.com / admin123)
INSERT INTO bpm_users (
    id, email, username, name, password,
    first_name, last_name, phone, status,
    email_verified, email_verified_at, login_count
) VALUES (
    'admin-user-uuid-2025-demo-001',
    'admin@easycorp.com',
    'admin',
    'EASYCORP 관리자',
    '$argon2id$v=19$m=65536,t=4,p=3$c29tZXNhbHQ$hash_will_be_here',  -- admin123을 해시한 값 (실제로는 PHP에서 생성)
    '관리자',
    '김',
    '010-1234-5678',
    'active',
    TRUE,
    NOW(),
    0
);

-- 일반 사용자 (user@easycorp.com / user123)
INSERT INTO bpm_users (
    id, email, username, name, password,
    first_name, last_name, phone, status,
    email_verified, email_verified_at, login_count
) VALUES (
    'normal-user-uuid-2025-demo-002',
    'user@easycorp.com',
    'user',
    '이지원',
    '$argon2id$v=19$m=65536,t=4,p=3$c29tZXNhbHQ$user_hash_here',  -- user123을 해시한 값
    '지원',
    '이',
    '010-2345-6789',
    'active',
    TRUE,
    NOW(),
    0
);

-- Process Owner 사용자 (owner@easycorp.com / owner123)
INSERT INTO bpm_users (
    id, email, username, name, password,
    first_name, last_name, phone, status,
    email_verified, email_verified_at, login_count
) VALUES (
    'owner-user-uuid-2025-demo-003',
    'owner@easycorp.com',
    'owner',
    '박프로세스',
    '$argon2id$v=19$m=65536,t=4,p=3$c29tZXNhbHQ$owner_hash_here',  -- owner123을 해시한 값
    '프로세스',
    '박',
    '010-3456-7890',
    'active',
    TRUE,
    NOW(),
    0
);

-- ===========================
-- 3. 회사별 사용자 역할 할당
-- ===========================

-- 관리자 역할
INSERT INTO bpm_company_users (
    id, company_id, user_id, role_type, department, job_title,
    employee_id, status, assigned_at, is_active
) VALUES (
    'company-user-admin-001',
    'easycorp-uuid-2025-demo-company1',
    'admin-user-uuid-2025-demo-001',
    'admin',
    'IT팀',
    '시스템 관리자',
    'EC001',
    'active',
    NOW(),
    TRUE
);

-- 일반 사용자 역할
INSERT INTO bpm_company_users (
    id, company_id, user_id, role_type, department, job_title,
    employee_id, status, assigned_at, is_active
) VALUES (
    'company-user-member-002',
    'easycorp-uuid-2025-demo-company1',
    'normal-user-uuid-2025-demo-002',
    'member',
    '경영지원팀',
    '사원',
    'EC002',
    'active',
    NOW(),
    TRUE
);

-- Process Owner 역할
INSERT INTO bpm_company_users (
    id, company_id, user_id, role_type, department, job_title,
    employee_id, status, assigned_at, is_active
) VALUES (
    'company-user-owner-003',
    'easycorp-uuid-2025-demo-company1',
    'owner-user-uuid-2025-demo-003',
    'process_owner',
    '기획팀',
    '팀장',
    'EC003',
    'active',
    NOW(),
    TRUE
);

-- ===========================
-- 4. 부서 데이터
-- ===========================

-- IT팀
INSERT INTO bpm_departments (
    id, company_id, department_name, description, department_code,
    level, head_user_id, created_by
) VALUES (
    'dept-it-001',
    'easycorp-uuid-2025-demo-company1',
    'IT팀',
    '시스템 개발 및 운영',
    'IT001',
    0,
    'admin-user-uuid-2025-demo-001',
    'admin-user-uuid-2025-demo-001'
);

-- 경영지원팀
INSERT INTO bpm_departments (
    id, company_id, department_name, description, department_code,
    level, created_by
) VALUES (
    'dept-support-002',
    'easycorp-uuid-2025-demo-company1',
    '경영지원팀',
    '인사, 총무, 회계 업무',
    'SUP001',
    0,
    'admin-user-uuid-2025-demo-001'
);

-- 기획팀
INSERT INTO bpm_departments (
    id, company_id, department_name, description, department_code,
    level, head_user_id, created_by
) VALUES (
    'dept-planning-003',
    'easycorp-uuid-2025-demo-company1',
    '기획팀',
    '사업 기획 및 전략 수립',
    'PLN001',
    0,
    'owner-user-uuid-2025-demo-003',
    'admin-user-uuid-2025-demo-001'
);

-- ===========================
-- 설치 후 사용자 비밀번호 해시 업데이트 필요
-- ===========================

-- 실제 설치 시 다음 PHP 코드로 비밀번호 해시 생성:
-- 
-- $adminPassword = password_hash('admin123', PASSWORD_ARGON2ID, [
--     'memory_cost' => 65536,
--     'time_cost' => 4,
--     'threads' => 3
-- ]);
--
-- $userPassword = password_hash('user123', PASSWORD_ARGON2ID, [
--     'memory_cost' => 65536,
--     'time_cost' => 4,
--     'threads' => 3
-- ]);
--
-- $ownerPassword = password_hash('owner123', PASSWORD_ARGON2ID, [
--     'memory_cost' => 65536,
--     'time_cost' => 4,
--     'threads' => 3
-- ]);
--
-- 그 후 UPDATE 쿼리로 실제 해시값 반영

-- ===========================
-- 데모 계정 정보 요약
-- ===========================

-- 1. 관리자: admin@easycorp.com / admin123 (관리자 권한)
-- 2. 일반 사용자: user@easycorp.com / user123 (구성원 권한)  
-- 3. 프로세스 담당자: owner@easycorp.com / owner123 (프로세스 소유자 권한)
--
-- 모든 계정은 EASYCORP 회사에 소속됨