# MCP 환경설정 및 API 키 관리 지침

## 1. API 키는 .env 파일에만 저장하세요
- 모든 민감한 정보(OPENAI, NOTION, YOUTUBE 등)는 프로젝트 루트의 `.env` 파일에 작성합니다.
- 예시:
  ```env
  OPENAI_API_KEY=sk-xxxxxxx
  NOTION_API_KEY=secret_xxxxx
  YOUTUBE_API_KEY=AIzaSyxxxxxx
  ```

## 2. mcp.json의 env 항목에는 환경변수 참조만 사용하세요
- 실제 값 대신 `${환경변수명}` 형태로 작성합니다.
- 예시:
  ```json
  "env": {
    "OPENAI_API_KEY": "${OPENAI_API_KEY}"
  }
  ```

## 3. .env 파일은 git에 커밋하지 마세요
- `.gitignore`에 `.env`가 반드시 포함되어야 합니다.

## 4. 환경변수 적용 방법
- Cursor 또는 MCP 런처는 .env 파일을 자동으로 읽어 환경변수를 주입합니다.
- 별도 설정이 필요하다면 README에 추가 안내를 작성하세요.

## 5. 협업 시 주의사항
- .env.example 파일을 만들어 키 이름만 공유하세요.
- 예시:
  ```env
  OPENAI_API_KEY=
  NOTION_API_KEY=
  YOUTUBE_API_KEY=
  ``` 