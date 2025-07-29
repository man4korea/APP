#!/bin/bash

# Windows .env 파일을 WSL에서 직접 공유하는 스크립트
# Windows 경로: C:\Users\man4k\OneDrive\문서\APP\.env
# WSL 경로: /mnt/c/Users/man4k/OneDrive/문서/APP/.env

echo "======================================"
echo "  Windows .env 파일 WSL 공유 설정"
echo "======================================"
echo ""

# Windows .env 파일 경로
WINDOWS_ENV_PATH="/mnt/c/Users/man4k/OneDrive/문서/APP/.env"
WSL_HOME_ENV="$HOME/.env"
WSL_PROJECT_ENV=".env"

echo "🔍 Windows .env 파일 확인 중..."

# Windows .env 파일 존재 확인
if [ -f "$WINDOWS_ENV_PATH" ]; then
    echo "✅ Windows .env 파일 발견: $WINDOWS_ENV_PATH"
    
    # 파일 크기 및 수정 시간 확인
    file_size=$(stat -c%s "$WINDOWS_ENV_PATH")
    file_time=$(stat -c%y "$WINDOWS_ENV_PATH")
    echo "   파일 크기: $file_size bytes"
    echo "   수정 시간: $file_time"
    
    # API 키 개수 확인 (보안상 내용은 표시하지 않음)
    api_count=$(grep -c "API_KEY\|TOKEN" "$WINDOWS_ENV_PATH" 2>/dev/null || echo "0")
    echo "   API 키/토큰 개수: $api_count개"
    
else
    echo "❌ Windows .env 파일을 찾을 수 없습니다: $WINDOWS_ENV_PATH"
    echo "   파일이 존재하는지 확인해주세요."
    exit 1
fi

echo ""
echo "🔗 WSL 환경 변수 공유 설정..."

# 1. 현재 디렉토리에 심볼릭 링크 생성 (이미 있다면 건너뛰기)
if [ -L "$WSL_PROJECT_ENV" ]; then
    echo "✅ 프로젝트 .env 심볼릭 링크 이미 존재"
elif [ -f "$WSL_PROJECT_ENV" ]; then
    echo "⚠️  기존 .env 파일이 존재합니다. 백업을 생성합니다..."
    mv "$WSL_PROJECT_ENV" "${WSL_PROJECT_ENV}.backup.$(date +%Y%m%d_%H%M%S)"
    ln -sf "$WINDOWS_ENV_PATH" "$WSL_PROJECT_ENV"
    echo "✅ Windows .env 파일에 대한 심볼릭 링크 생성 완료"
else
    ln -sf "$WINDOWS_ENV_PATH" "$WSL_PROJECT_ENV"
    echo "✅ Windows .env 파일에 대한 심볼릭 링크 생성 완료"
fi

# 2. 홈 디렉토리에도 심볼릭 링크 생성 (글로벌 접근용)
if [ -L "$WSL_HOME_ENV" ]; then
    echo "✅ 홈 디렉토리 .env 심볼릭 링크 이미 존재"
elif [ -f "$WSL_HOME_ENV" ]; then
    echo "⚠️  홈 디렉토리에 기존 .env 파일이 존재합니다. 백업을 생성합니다..."
    mv "$WSL_HOME_ENV" "${WSL_HOME_ENV}.backup.$(date +%Y%m%d_%H%M%S)"
    ln -sf "$WINDOWS_ENV_PATH" "$WSL_HOME_ENV"
    echo "✅ 홈 디렉토리에 Windows .env 심볼릭 링크 생성 완료"
else
    ln -sf "$WINDOWS_ENV_PATH" "$WSL_HOME_ENV"
    echo "✅ 홈 디렉토리에 Windows .env 심볼릭 링크 생성 완료"
fi

echo ""
echo "🧪 환경 변수 로드 테스트..."

# Windows .env 파일에서 환경 변수 직접 로드
source "$WINDOWS_ENV_PATH"

# 주요 환경 변수 확인
env_vars=("GITHUB_USERNAME" "GIT_USER_NAME" "PROJECT_PATH" "DATA_DIR")
echo ""
echo "📋 기본 환경 변수:"
for var in "${env_vars[@]}"; do
    if [[ -n "${!var}" ]]; then
        echo "✅ $var: ${!var}"
    else
        echo "⚠️  $var: 설정되지 않음"
    fi
done

# API 키 확인 (값은 표시하지 않음)
api_keys=("OPENAI_API_KEY" "ANTHROPIC_API_KEY" "GITHUB_TOKEN" "GOOGLE_API_KEY" "NOTION_API_KEY" "YOUTUBE_API_KEY")
echo ""
echo "🔑 API 키 상태:"
for key in "${api_keys[@]}"; do
    if [[ -n "${!key}" ]]; then
        # 키의 앞 4자리와 뒷 4자리만 표시
        masked_key="${!key:0:4}...${!key: -4}"
        echo "✅ $key: $masked_key"
    else
        echo "❌ $key: 설정되지 않음"
    fi
done

echo ""
echo "🔧 WSL 환경 설정 업데이트..."

# .bashrc에 자동 로드 설정 추가 (중복 방지)
bashrc_line="source $WINDOWS_ENV_PATH 2>/dev/null || true"
if ! grep -q "source.*$WINDOWS_ENV_PATH" "$HOME/.bashrc" 2>/dev/null; then
    echo "" >> "$HOME/.bashrc"
    echo "# Windows .env 파일 자동 로드" >> "$HOME/.bashrc"
    echo "$bashrc_line" >> "$HOME/.bashrc"
    echo "✅ .bashrc에 자동 환경 변수 로드 설정 추가"
else
    echo "✅ .bashrc에 환경 변수 로드 설정 이미 존재"
fi

# .profile에도 추가 (다른 쉘에서도 작동하도록)
if [ -f "$HOME/.profile" ]; then
    if ! grep -q "source.*$WINDOWS_ENV_PATH" "$HOME/.profile" 2>/dev/null; then
        echo "" >> "$HOME/.profile"
        echo "# Windows .env 파일 자동 로드" >> "$HOME/.profile"
        echo "$bashrc_line" >> "$HOME/.profile"
        echo "✅ .profile에 자동 환경 변수 로드 설정 추가"
    else
        echo "✅ .profile에 환경 변수 로드 설정 이미 존재"
    fi
fi

echo ""
echo "📝 load_env_wsl.sh 스크립트 업데이트..."

# load_env_wsl.sh 스크립트를 Windows .env 파일을 직접 사용하도록 수정
cat > load_env_wsl.sh << 'EOF'
#!/bin/bash

# Windows .env 파일을 WSL에서 직접 로드하는 스크립트
# Windows 파일 경로: C:\Users\man4k\OneDrive\문서\APP\.env
# WSL 파일 경로: /mnt/c/Users/man4k/OneDrive/문서/APP/.env

WINDOWS_ENV_PATH="/mnt/c/Users/man4k/OneDrive/문서/APP/.env"

echo "======================================"
echo "  Windows .env 파일 직접 로드"
echo "======================================"
echo ""

# Windows .env 파일 존재 확인
if [ ! -f "$WINDOWS_ENV_PATH" ]; then
    echo "❌ Windows .env 파일을 찾을 수 없습니다: $WINDOWS_ENV_PATH"
    echo "   Windows에서 .env 파일이 존재하는지 확인해주세요."
    return 1 2>/dev/null || exit 1
fi

echo "✅ Windows .env 파일 발견: $WINDOWS_ENV_PATH"
echo "📁 파일 크기: $(stat -c%s "$WINDOWS_ENV_PATH") bytes"
echo "🕒 수정 시간: $(stat -c%y "$WINDOWS_ENV_PATH" | cut -d. -f1)"
echo ""

# Windows .env 파일에서 환경 변수 로드
echo "🔄 환경 변수 로드 중..."
while IFS= read -r line || [[ -n "$line" ]]; do
    # 주석이나 빈 줄 제외
    if [[ $line =~ ^[[:space:]]*# ]] || [[ -z "${line// }" ]]; then
        continue
    fi
    
    # 환경 변수 형식인지 확인 (KEY=VALUE)
    if [[ $line =~ ^[A-Za-z_][A-Za-z0-9_]*= ]]; then
        # Windows 경로를 WSL 경로로 변환
        if [[ $line =~ C:\\\\.*\\\\.*\\\\APP ]]; then
            line=$(echo "$line" | sed 's|C:\\\\Users\\\\man4k\\\\OneDrive\\\\문서\\\\APP|/mnt/c/Users/man4k/OneDrive/문서/APP|g')
            line=$(echo "$line" | sed 's|\\\\|/|g')
        fi
        
        # 환경 변수 설정
        export "$line"
        
        # 중요한 변수만 로그 출력
        if [[ $line =~ ^(GITHUB_|GIT_|PROJECT_|DATA_DIR|NODE_ENV) ]]; then
            echo "✅ $line"
        elif [[ $line =~ .*API_KEY.*=.* ]] || [[ $line =~ .*TOKEN.*=.* ]]; then
            var_name=$(echo "$line" | cut -d'=' -f1)
            echo "✅ $var_name=***[보안상 숨김]***"
        fi
    fi
done < "$WINDOWS_ENV_PATH"

echo ""
echo "📊 로드 완료 요약:"
echo "   📁 프로젝트 경로: ${PROJECT_PATH:-설정되지 않음}"
echo "   👤 GitHub 사용자: ${GITHUB_USERNAME:-설정되지 않음}"
echo "   📧 Git 이메일: ${GIT_USER_EMAIL:-설정되지 않음}"
echo "   🏗️  데이터 디렉토리: ${DATA_DIR:-설정되지 않음}"

# API 키 개수 확인
api_count=0
for key in OPENAI_API_KEY ANTHROPIC_API_KEY GITHUB_TOKEN GOOGLE_API_KEY NOTION_API_KEY YOUTUBE_API_KEY GEMINI_API_KEY; do
    if [[ -n "${!key}" ]]; then
        ((api_count++))
    fi
done
echo "   🔑 API 키 설정: $api_count개"

echo ""
echo "✨ Windows .env 파일이 WSL에서 성공적으로 로드되었습니다!"

# Git 설정 자동 적용
if [[ -n "$GIT_USER_NAME" ]] && [[ -n "$GIT_USER_EMAIL" ]]; then
    git config --global user.name "$GIT_USER_NAME" 2>/dev/null
    git config --global user.email "$GIT_USER_EMAIL" 2>/dev/null
    echo "✅ Git 사용자 정보 자동 설정 완료"
fi

# Claude Desktop 설정 파일 경로 설정
if [ -f "claude_desktop_config_wsl.json" ]; then
    mkdir -p ~/.config/claude-desktop
    cp claude_desktop_config_wsl.json ~/.config/claude-desktop/claude_desktop_config.json 2>/dev/null
    echo "✅ Claude Desktop 설정 파일 업데이트 완료"
fi
EOF

chmod +x load_env_wsl.sh
echo "✅ load_env_wsl.sh 스크립트 업데이트 완료"

echo ""
echo "======================================"
echo "  🎉 Windows .env 파일 WSL 공유 완료!"
echo "======================================"
echo ""
echo "📋 설정 완료 내용:"
echo "   1. ✅ Windows .env 파일에 대한 심볼릭 링크 생성"
echo "   2. ✅ WSL 홈 디렉토리에도 링크 생성 (~/.env)"
echo "   3. ✅ .bashrc/.profile에 자동 로드 설정 추가"
echo "   4. ✅ load_env_wsl.sh 스크립트 최적화"
echo "   5. ✅ 환경 변수 로드 및 확인 완료"
echo ""
echo "🚀 사용 방법:"
echo "   # 환경 변수 수동 로드"
echo "   source load_env_wsl.sh"
echo ""
echo "   # 새 터미널 세션에서는 자동 로드됨"
echo "   # (.bashrc에 설정 추가됨)"
echo ""
echo "   # Windows에서 .env 파일 수정 시 WSL에서 즉시 반영됨"
echo ""
echo "🔄 다음 단계:"
echo "1. 새 터미널 열기 (환경 변수 자동 로드 확인)"
echo "2. Claude Code 실행: ./start_claude_code_wsl.sh"
echo "3. Windows에서 .env 파일 수정 시 WSL에서 바로 사용 가능"
echo ""
echo "⚠️  참고: Windows에서 .env 파일을 수정하면 WSL에서 즉시 반영됩니다!"
