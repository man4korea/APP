@echo off
REM AppMart 프로젝트 시작 및 Gemini 컨텍스트 로딩 배치 파일

REM 1. 작업 디렉토리로 이동
cd "C:\Users\man4k\OneDrive\문서\APP\AppMart"

REM 2. Gemini에게 주요 프로젝트 파일을 순서대로 읽도록 요청
echo [Gemini] 프로젝트 컨텍스트 로딩을 시작합니다...

REM 2.1. 협업 가이드 로딩
echo [Gemini] Loading: gemini.md
type gemini.md

REM 2.2. 제품 요구사항 명세서 로딩
echo [Gemini] Loading: app_mart_prd.md
type app_mart_prd.md

REM 2.3. 프로젝트 공통 개발 지침서 로딩
echo [Gemini] Loading: APP_MART_PROJECT_GUIDE.md
type APP_MART_PROJECT_GUIDE.md

REM 2.4. 프로젝트 개요 로딩
echo [Gemini] Loading: README.md
type README.md

REM 2.5. 작업 목록 로딩
echo [Gemini] Loading: SHRIMP_Tasks.md
type SHRIMP_Tasks.md


echo [Gemini] 모든 프로젝트 컨텍스트 로딩이 완료되었습니다.
echo [Gemini] 이제 'am-mvp-db-001' (데이터베이스 스키마 생성) 작업을 시작할 준비가 되었습니다.
