<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\views\register.php
// Create at 2508041122 Ver1.00

include_once __DIR__ . '/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">회원가입</div>
            <div class="card-body">
                <form action="/register" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">이메일 주소</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="nickname" class="form-label">닉네임</label>
                        <input type="text" class="form-control" id="nickname" name="nickname" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">비밀번호</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">비밀번호 확인</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">가입하기</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/layouts/footer.php';
?>
