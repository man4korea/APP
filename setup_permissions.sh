#!/bin/bash

# WSL 환경 스크립트 실행 권한 설정
# 모든 스크립트 파일에 실행 권한 부여

echo "=== WSL 스크립트 실행 권한 설정 ==="
echo ""

# 실행 권한을 부여할 스크립트 파일들
scripts=(
    "load_env_wsl.sh"
    "setup_git_collaboration.sh"
    "setup_mcp_wsl.sh"
    "start_claude_code_wsl.sh"
    "complete_wsl_setup.sh"
    "verify_wsl_setup.sh"
    "setup_permissions.sh"
)

# 각 스크립트에 실행 권한 부여
for script in "${scripts[@]}"; do
    if [ -f "$script" ]; then
        chmod +x "$script"
        echo "✅ $script - 실행 권한 부여 완료"
    else
        echo "⚠️  $script - 파일이 존재하지 않음"
    fi
done

echo ""
echo "=== 권한 설정 확인 ==="
echo ""

# 권한 설정 확인
for script in "${scripts[@]}"; do
    if [ -f "$script" ]; then
        if [ -x "$script" ]; then
            echo "✅ $script - 실행 가능"
        else
            echo "❌ $script - 실행 불가"
        fi
    fi
done

echo ""
echo "=== 권한 설정 완료! ==="
echo ""
echo "이제 다음 명령들을 실행할 수 있습니다:"
echo ""
echo "1. 전체 설정 실행:"
echo "   ./complete_wsl_setup.sh"
echo ""
echo "2. 환경 변수 로드:"
echo "   source load_env_wsl.sh"
echo ""
echo "3. Git 협업 설정:"
echo "   ./setup_git_collaboration.sh"
echo ""
echo "4. Claude Code 실행:"
echo "   ./start_claude_code_wsl.sh"
echo ""
echo "5. 설정 검증:"
echo "   ./verify_wsl_setup.sh"
