#!/bin/bash

# WSL 환경용 MCP 서버 환경 변수 로드 스크립트
# 사용법: source load_env_wsl.sh

# .env 파일에서 환경 변수 로드
if [ -f ".env" ]; then
    echo "Loading environment variables from .env file..."
    
    # .env 파일의 각 줄을 읽어서 환경 변수로 설정
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
            
            # API 키가 포함된 변수만 로그 출력 (보안상 값은 마스킹)
            if [[ $line =~ .*API_KEY.*=.* ]] || [[ $line =~ .*TOKEN.*=.* ]]; then
                var_name=$(echo "$line" | cut -d'=' -f1)
                echo "Loaded: $var_name=***"
            elif [[ $line =~ ^(GITHUB_|GIT_|PROJECT_|DATA_DIR|TEMPLATES_USE|ENABLE_GUI) ]]; then
                echo "Loaded: $line"
            fi
        fi
    done < ".env"
    
    echo "Environment variables loaded successfully!"
    echo ""
    
    # 주요 환경 변수 확인
    echo "=== Key Environment Variables ==="
    echo "NODE_ENV: ${NODE_ENV:-not set}"
    echo "DATA_DIR: ${DATA_DIR:-not set}"
    echo "PROJECT_PATH: ${PROJECT_PATH:-not set}"
    echo "GITHUB_USERNAME: ${GITHUB_USERNAME:-not set}"
    echo "GIT_USER_NAME: ${GIT_USER_NAME:-not set}"
    echo ""
    
    # API 키들이 설정되었는지 확인 (값은 표시하지 않음)
    echo "=== API Keys Status ==="
    [[ -n "$NOTION_API_KEY" ]] && echo "✓ NOTION_API_KEY: Set" || echo "✗ NOTION_API_KEY: Not set"
    [[ -n "$YOUTUBE_API_KEY" ]] && echo "✓ YOUTUBE_API_KEY: Set" || echo "✗ YOUTUBE_API_KEY: Not set"
    [[ -n "$OPENAI_API_KEY" ]] && echo "✓ OPENAI_API_KEY: Set" || echo "✗ OPENAI_API_KEY: Not set"
    [[ -n "$ANTHROPIC_API_KEY" ]] && echo "✓ ANTHROPIC_API_KEY: Set" || echo "✗ ANTHROPIC_API_KEY: Not set"
    [[ -n "$GOOGLE_API_KEY" ]] && echo "✓ GOOGLE_API_KEY: Set" || echo "✗ GOOGLE_API_KEY: Not set"
    [[ -n "$GEMINI_API_KEY" ]] && echo "✓ GEMINI_API_KEY: Set" || echo "✗ GEMINI_API_KEY: Not set"
    [[ -n "$GITHUB_TOKEN" ]] && echo "✓ GITHUB_TOKEN: Set" || echo "✗ GITHUB_TOKEN: Not set"
    echo ""
    
else
    echo "Error: .env file not found in current directory"
    echo "Please make sure you're in the correct directory and .env file exists"
    return 1
fi

# Git 설정 확인 및 적용
if [[ -n "$GIT_USER_NAME" ]] && [[ -n "$GIT_USER_EMAIL" ]]; then
    echo "=== Git Configuration ==="
    git config --global user.name "$GIT_USER_NAME" 2>/dev/null
    git config --global user.email "$GIT_USER_EMAIL" 2>/dev/null
    echo "Git user: $(git config --global user.name 2>/dev/null || echo 'Not configured')"
    echo "Git email: $(git config --global user.email 2>/dev/null || echo 'Not configured')"
    echo ""
fi

# Claude Desktop 설정 파일 복사 (WSL 버전)
if [ -f "claude_desktop_config_wsl.json" ]; then
    echo "=== Claude Desktop Configuration ==="
    mkdir -p ~/.config/claude-desktop
    cp claude_desktop_config_wsl.json ~/.config/claude-desktop/claude_desktop_config.json
    echo "✓ WSL용 Claude Desktop 설정 파일이 복사되었습니다."
    echo "Location: ~/.config/claude-desktop/claude_desktop_config.json"
    echo ""
fi

echo "=== Setup Complete! ==="
echo "You can now run:"
echo "  claude-code          # Claude Code 실행"
echo "  git status           # Git 상태 확인"
echo "  npm run start        # 프로젝트 시작 (package.json에 정의된 경우)"
