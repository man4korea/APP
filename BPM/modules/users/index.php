<!-- 📁 C:\xampp\htdocs\BPM\modules\users\index.php -->
<!-- Create at 2508041150 Ver1.00 -->

<?php
require_once __DIR__ . '/../../includes/config.php';

// 로그인 상태 확인
$currentUser = $auth->getCurrentUser();
if (!$currentUser) {
    header('Location: /BPM/login.php');
    exit;
}

// 회사 컨텍스트 확인
$companyId = $tenant->getCurrentCompanyId();
if (!$companyId) {
    header('Location: /BPM/views/pages/company-register.php');
    exit;
}

// 구성원 관리 권한 확인
if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'view')) {
    $errorMessage = "구성원 관리 권한이 없습니다.";
}

$pageTitle = "구성원 관리";
$currentModule = "members";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - BPM</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/modules.css') ?>">
    
    <style>
        /* 🟠 구성원관리 모듈 색상 (#ff9f43 / #fff8f0) */
        .users-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #fff8f0;
            min-height: 100vh;
        }

        .users-header {
            background: linear-gradient(135deg, #ff9f43 0%, #e67e22 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(255, 159, 67, 0.3);
        }

        .users-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .users-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .users-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            gap: 20px;
        }

        .search-section {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .search-input {
            flex: 1;
            max-width: 400px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #ff9f43;
            box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.1);
        }

        .filter-select {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
        }

        .filter-select:focus {
            outline: none;
            border-color: #ff9f43;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff9f43 0%, #e67e22 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 159, 67, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #ff9f43;
            border: 2px solid #ff9f43;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #ff9f43;
            color: white;
        }

        .users-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #ff9f43;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ff9f43;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #4a5568;
            font-weight: 600;
        }

        .users-table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            background: #f8f9fa;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }

        .users-table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .users-table tr:hover {
            background: #fff8f0;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff9f43 0%, #e67e22 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-details h4 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #2d3748;
        }

        .user-details p {
            margin: 0;
            color: #718096;
            font-size: 0.875rem;
        }

        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-founder {
            background: #9f7aea;
            color: white;
        }

        .role-admin {
            background: #ff9f43;
            color: white;
        }

        .role-process_owner {
            background: #4299e1;
            color: white;
        }

        .role-member {
            background: #48bb78;
            color: white;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-inactive {
            background: #fed7d7;
            color: #742a2a;
        }

        .status-pending_approval {
            background: #feebc8;
            color: #744210;
        }

        .action-buttons-table {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.75rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-view {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-view:hover {
            background: #cbd5e0;
        }

        .btn-edit {
            background: #ff9f43;
            color: white;
        }

        .btn-edit:hover {
            background: #e67e22;
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .loading-state {
            text-align: center;
            padding: 60px 20px;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #ff9f43;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 20px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover {
            background: #fff8f0;
            border-color: #ff9f43;
        }

        .pagination button.active {
            background: #ff9f43;
            color: white;
            border-color: #ff9f43;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c53030;
        }

        @media (max-width: 768px) {
            .users-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-section {
                flex-direction: column;
            }
            
            .action-buttons {
                justify-content: center;
            }
            
            .users-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .users-table-container {
                overflow-x: auto;
            }
            
            .users-table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="users-container">
        <div class="users-header">
            <h1>👥 구성원 관리</h1>
            <p>팀원들의 정보를 관리하고 권한을 설정하세요</p>
        </div>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error">
                <strong>오류:</strong> <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php else: ?>
            <!-- 통계 카드 -->
            <div class="users-stats" id="usersStats">
                <!-- JavaScript로 동적 생성 -->
            </div>

            <!-- 컨트롤 영역 -->
            <div class="users-controls">
                <div class="search-section">
                    <input type="text" class="search-input" placeholder="이름, 이메일, 부서로 검색..." id="searchInput">
                    <select class="filter-select" id="roleFilter">
                        <option value="">전체 권한</option>
                        <option value="founder">창립자</option>
                        <option value="admin">관리자</option>
                        <option value="process_owner">프로세스 담당자</option>
                        <option value="member">일반 구성원</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="">전체 상태</option>
                        <option value="active">활성</option>
                        <option value="inactive">비활성</option>
                        <option value="pending_approval">승인 대기</option>
                    </select>
                </div>
                
                <div class="action-buttons">
                    <?php if ($permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'invite')): ?>
                        <a href="/BPM/views/pages/invite.php" class="btn-primary">
                            ✉️ 구성원 초대
                        </a>
                    <?php endif; ?>
                    <button class="btn-secondary" onclick="refreshData()">
                        🔄 새로고침
                    </button>
                </div>
            </div>

            <!-- 사용자 목록 -->
            <div class="users-table-container">
                <div class="table-header">
                    <div class="table-title">구성원 목록</div>
                    <div id="resultCount">-</div>
                </div>
                
                <div id="usersTableContainer">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p>구성원 목록을 불러오는 중...</p>
                    </div>
                </div>
                
                <div class="pagination" id="pagination" style="display: none;">
                    <!-- JavaScript로 동적 생성 -->
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script>
        let usersData = [];
        let filteredData = [];
        let currentPage = 1;
        const itemsPerPage = 10;

        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            loadUsersData();
            loadStats();
            setupEventListeners();
        });

        // 이벤트 리스너 설정
        function setupEventListeners() {
            // 검색 입력
            document.getElementById('searchInput').addEventListener('input', debounce(filterUsers, 300));
            
            // 필터 선택
            document.getElementById('roleFilter').addEventListener('change', filterUsers);
            document.getElementById('statusFilter').addEventListener('change', filterUsers);
        }

        // 사용자 데이터 로드
        async function loadUsersData() {
            try {
                const response = await fetch('/BPM/api/users.php/list');
                const result = await response.json();
                
                if (result.success) {
                    usersData = result.data;
                    filteredData = [...usersData];
                    renderUsersTable();
                } else {
                    showError('구성원 목록을 불러올 수 없습니다: ' + result.message);
                }
            } catch (error) {
                console.error('Failed to load users:', error);
                showError('구성원 목록을 불러오는 중 오류가 발생했습니다.');
            }
        }

        // 통계 데이터 로드
        async function loadStats() {
            try {
                const response = await fetch('/BPM/api/users.php/stats');
                const result = await response.json();
                
                if (result.success) {
                    renderStats(result.data);
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        }

        // 통계 렌더링
        function renderStats(stats) {
            const statsContainer = document.getElementById('usersStats');
            statsContainer.innerHTML = `
                <div class="stat-card">
                    <div class="stat-number">${stats.total_users || 0}</div>
                    <div class="stat-label">전체 구성원</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.active_users || 0}</div>
                    <div class="stat-label">활성 구성원</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.admin_count + stats.founder_count || 0}</div>
                    <div class="stat-label">관리자</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.pending_users || 0}</div>
                    <div class="stat-label">승인 대기</div>
                </div>
            `;
        }

        // 사용자 목록 렌더링
        function renderUsersTable() {
            const container = document.getElementById('usersTableContainer');
            const resultCount = document.getElementById('resultCount');
            
            if (filteredData.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">👥</div>
                        <h3>구성원이 없습니다</h3>
                        <p>아직 등록된 구성원이 없거나 검색 결과가 없습니다.</p>
                    </div>
                `;
                resultCount.textContent = '0명';
                document.getElementById('pagination').style.display = 'none';
                return;
            }

            // 페이지네이션 계산
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const currentData = filteredData.slice(startIndex, endIndex);

            // 테이블 렌더링
            container.innerHTML = `
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>구성원</th>
                            <th>권한</th>
                            <th>부서</th>
                            <th>상태</th>
                            <th>마지막 로그인</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${currentData.map(user => renderUserRow(user)).join('')}
                    </tbody>
                </table>
            `;

            // 결과 카운트 업데이트
            resultCount.textContent = `${filteredData.length}명`;

            // 페이지네이션 렌더링
            if (totalPages > 1) {
                renderPagination(totalPages);
                document.getElementById('pagination').style.display = 'flex';
            } else {
                document.getElementById('pagination').style.display = 'none';
            }
        }

        // 사용자 행 렌더링
        function renderUserRow(user) {
            const canEdit = <?= $permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'edit') ? 'true' : 'false' ?>;
            const canRemove = <?= $permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'remove') ? 'true' : 'false' ?>;
            
            return `
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">${getInitials(user.name || user.username)}</div>
                            <div class="user-details">
                                <h4>${user.name || user.username}</h4>
                                <p>${user.email}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge role-${user.role_type}">
                            ${getRoleName(user.role_type)}
                        </span>
                    </td>
                    <td>${user.department || '-'}</td>
                    <td>
                        <span class="status-badge status-${user.company_status}">
                            ${getStatusName(user.company_status)}
                        </span>
                    </td>
                    <td>${formatDate(user.last_login_at) || '없음'}</td>
                    <td>
                        <div class="action-buttons-table">
                            <a href="/BPM/views/pages/profile.php?user_id=${user.id}" class="btn-small btn-view">
                                보기
                            </a>
                            ${canEdit ? `
                                <button class="btn-small btn-edit" onclick="editUser('${user.id}')">
                                    편집
                                </button>
                            ` : ''}
                            ${canRemove && user.id !== '<?= $currentUser['id'] ?>' ? `
                                <button class="btn-small btn-danger" onclick="removeUser('${user.id}', '${user.name || user.username}')">
                                    제거
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }

        // 페이지네이션 렌더링
        function renderPagination(totalPages) {
            const pagination = document.getElementById('pagination');
            let buttons = [];

            // 이전 버튼
            buttons.push(`
                <button ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">
                    이전
                </button>
            `);

            // 페이지 번호 버튼들
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                buttons.push(`<button onclick="changePage(1)">1</button>`);
                if (startPage > 2) {
                    buttons.push(`<span>...</span>`);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                buttons.push(`
                    <button class="${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                        ${i}
                    </button>
                `);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    buttons.push(`<span>...</span>`);
                }
                buttons.push(`<button onclick="changePage(${totalPages})">${totalPages}</button>`);
            }

            // 다음 버튼
            buttons.push(`
                <button ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">
                    다음
                </button>
            `);

            pagination.innerHTML = buttons.join('');
        }

        // 페이지 변경
        function changePage(page) {
            currentPage = page;
            renderUsersTable();
        }

        // 사용자 필터링
        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            filteredData = usersData.filter(user => {
                const matchesSearch = !searchTerm || 
                    (user.name && user.name.toLowerCase().includes(searchTerm)) ||
                    user.email.toLowerCase().includes(searchTerm) ||
                    (user.department && user.department.toLowerCase().includes(searchTerm));

                const matchesRole = !roleFilter || user.role_type === roleFilter;
                const matchesStatus = !statusFilter || user.company_status === statusFilter;

                return matchesSearch && matchesRole && matchesStatus;
            });

            currentPage = 1;
            renderUsersTable();
        }

        // 사용자 편집
        function editUser(userId) {
            // 권한 변경 모달 또는 페이지로 이동 (추후 구현)
            alert('사용자 편집 기능은 곧 구현될 예정입니다.');
        }

        // 사용자 제거
        async function removeUser(userId, userName) {
            if (!confirm(`정말로 ${userName}님을 회사에서 제거하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.`)) {
                return;
            }

            try {
                const response = await fetch(`/BPM/api/users.php/remove?user_id=${userId}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    alert('구성원이 성공적으로 제거되었습니다.');
                    refreshData();
                } else {
                    alert('구성원 제거 중 오류가 발생했습니다: ' + result.message);
                }
            } catch (error) {
                console.error('Remove user error:', error);
                alert('구성원 제거 중 오류가 발생했습니다.');
            }
        }

        // 데이터 새로고침
        function refreshData() {
            loadUsersData();
            loadStats();
        }

        // 오류 표시
        function showError(message) {
            const container = document.getElementById('usersTableContainer');
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">⚠️</div>
                    <h3>데이터를 불러올 수 없습니다</h3>
                    <p>${message}</p>
                    <button class="btn-primary" onclick="refreshData()" style="margin-top: 15px;">
                        다시 시도
                    </button>
                </div>
            `;
        }

        // 유틸리티 함수들
        function getInitials(name) {
            if (!name) return '?';
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        }

        function getRoleName(roleType) {
            const roleNames = {
                'founder': '창립자',
                'admin': '관리자',
                'process_owner': '프로세스 담당자',
                'member': '일반 구성원'
            };
            return roleNames[roleType] || roleType;
        }

        function getStatusName(status) {
            const statusNames = {
                'active': '활성',
                'inactive': '비활성',
                'pending_approval': '승인 대기'
            };
            return statusNames[status] || status;
        }

        function formatDate(dateString) {
            if (!dateString) return null;
            const date = new Date(dateString);
            return date.toLocaleDateString('ko-KR', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</body>
</html>