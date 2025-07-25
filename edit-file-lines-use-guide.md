# 📝 파일 수정 오류 방지 및 효율성 개선 지침

> **목적**: `edit_file_lines` 반복 오류 방지 및 파일 작업 효율성 극대화  
> **생성일**: 2025-01-27  
> **적용 대상**: 모든 파일 수정 작업

---

## 🚨 문제 분석: 왜 반복 오류가 발생하는가?

### 주요 원인 4가지
1. **부정확한 위치 파악**: 수정할 코드의 정확한 라인 번호 미확인
2. **문자열 불일치**: `old_str`이 실제 파일 내용과 정확히 일치하지 않음
3. **공백/들여쓰기 차이**: 보이지 않는 공백, 탭 문자 차이
4. **파일 상태 변화**: 이전 수정으로 인한 라인 번호 변경 미반영

### 반복 오류 패턴
```
❌ 잘못된 패턴:
edit_file_lines (실패) → edit_file_lines (재시도) → edit_file_lines (또 실패) → write_file (전체 재작성)

✅ 올바른 패턴:
read_file → get_file_lines → edit_file_lines(dryRun) → 확인 → approve_edit
```

---

## 🎯 파일 작업 우선순위 및 전략 (개선판)

### 1️⃣ 신규 파일 생성 (가장 안전)
```javascript
// 단일 파일
write_file({ path: "파일경로", content: "전체코드" })

// 대용량 파일 (섹션별 분할)
write_file({ path: "파일경로", content: "섹션1" })
write_file({ path: "파일경로", content: "섹션2", mode: "append" })
```

### 2️⃣ 전체 파일 교체 (중간 크기 파일, 200줄 이상)
```javascript
// artifacts → write_file 패턴 (권장)
read_file("기존파일") → artifacts(전체내용수정) → write_file("최종저장")
```

### 3️⃣ text_editor 활용 (50-200줄)
```javascript
// text_editor의 str_replace 활용
text_editor({ 
  command: "str_replace", 
  path: "파일경로", 
  old_str: "정확한기존코드", 
  new_str: "새로운코드" 
})
```

### 4️⃣ 소규모 수정 (50줄 미만)
```javascript
// 단순 수정은 메모리 작업 → write_file
read_file → 메모리에서 수정 → write_file
```

### 5️⃣ edit_file_lines (최후의 수단)
```javascript
// ⚠️ 반드시 이 순서로 진행!
read_file("파일") 
→ get_file_lines(수정위치 확인) 
→ edit_file_lines({ dryRun: true }) 
→ 결과 검토 및 확인
→ approve_edit()
```

---

## 🔧 edit_file_lines 사용 시 필수 체크리스트

### 작업 전 준비사항 ✅
- [ ] **1. 파일 전체 읽기**: `read_file`로 현재 파일 상태 완전 파악
- [ ] **2. 정확한 위치 확인**: `get_file_lines`로 수정할 라인 범위 정확히 파악
- [ ] **3. 문자열 매칭 확인**: 수정할 부분의 정확한 문자열 복사
- [ ] **4. 공백/들여쓰기 확인**: 탭, 스페이스, 줄바꿈 문자까지 정확히 일치

### 실행 시 필수 절차 ✅
- [ ] **1. dryRun 필수**: `{ dryRun: true }` 먼저 실행
- [ ] **2. 결과 검토**: dryRun 결과를 꼼꼼히 확인
- [ ] **3. 문제 발견 시 중단**: 예상과 다르면 즉시 중단하고 다른 방법 사용
- [ ] **4. 성공 시 승인**: `approve_edit`로 최종 적용

### 실패 시 대안 전략 ✅
- [ ] **1차 실패**: `text_editor` 사용 시도
- [ ] **2차 실패**: `artifacts` + `write_file` 패턴 사용
- [ ] **3차 실패**: 파일 분할 후 개별 작성

---

## 🚀 파일 작업 효율성 극대화 전략

### 크기별 최적 작업 방법

#### 📄 소형 파일 (50줄 이하)
```javascript
✅ 권장: read_file → 메모리 수정 → write_file
❌ 비권장: edit_file_lines (오버헤드 큼)
```

#### 📄 중형 파일 (50-200줄)
```javascript
✅ 권장: text_editor (str_replace)
✅ 대안: artifacts → write_file
❌ 피하기: edit_file_lines (오류 확률 높음)
```

#### 📄 대형 파일 (200줄 이상)
```javascript
✅ 권장: artifacts → write_file (전체 교체)
✅ 대안: 파일 분할 후 개별 처리
❌ 절대금지: edit_file_lines (거의 실패)
```

### 작업 복잡도별 전략

#### 🔹 단순 텍스트 변경
```javascript
text_editor({ command: "str_replace", ... })
```

#### 🔹 여러 위치 동시 수정
```javascript
artifacts → 전체 내용 수정 → write_file
```

#### 🔹 구조적 변경 (함수 추가/삭제)
```javascript
read_file → artifacts → write_file (전체 재작성)
```

#### 🔹 대량 코드 추가
```javascript
파일 분할 → write_file (섹션별)
```

---

## ⚠️ 절대 금지 패턴

### ❌ 금지 1: 무작정 재시도
```javascript
// 잘못된 예시
edit_file_lines (실패)
→ edit_file_lines (또 실패) 
→ edit_file_lines (계속 실패)
```

### ❌ 금지 2: 위치 확인 없는 수정
```javascript
// 잘못된 예시
edit_file_lines 바로 실행 (read_file, get_file_lines 생략)
```

### ❌ 금지 3: dryRun 생략
```javascript
// 잘못된 예시
edit_file_lines({ dryRun: false }) 바로 실행
```

### ❌ 금지 4: 대형 파일에 edit_file_lines 사용
```javascript
// 잘못된 예시
200줄 이상 파일에 edit_file_lines 사용
```

---

## 🎯 실전 워크플로우 예시

### 시나리오 1: 함수 하나 수정 (소형)
```javascript
1. read_file("target.js")
2. 메모리에서 함수 수정
3. write_file("target.js", 전체내용)
```

### 시나리오 2: 여러 위치 수정 (중형)
```javascript
1. read_file("target.js") 
2. artifacts 창에서 전체 코드 수정
3. write_file("target.js", artifacts내용)
```

### 시나리오 3: CSS 스타일 추가 (중형)
```javascript
1. text_editor({ 
   command: "str_replace",
   path: "styles.css",
   old_str: "/* 기존 마지막 스타일 */",
   new_str: "/* 기존 마지막 스타일 */\n\n/* 새 스타일 */"
})
```

### 시나리오 4: 대형 HTML 파일 수정
```javascript
1. read_file("page.html")
2. artifacts 창에서 전체 HTML 구조 수정
3. write_file("page.html", artifacts내용)
```

---

## 🔍 오류 발생 시 디버깅 가이드

### 1단계: 문제 파악
```javascript
// 현재 파일 상태 확인
read_file("문제파일")
get_file_info("문제파일") // 라인 수, 수정 시간 등 확인
```

### 2단계: 원인 분석
- **문자열 불일치**: 복사한 내용과 실제 파일 내용 비교
- **라인 번호 변경**: 이전 수정으로 인한 라인 번호 변화 확인
- **파일 인코딩**: 특수 문자, 인코딩 문제 확인

### 3단계: 대안 방법 선택
```javascript
// 우선순위별 대안
1순위: text_editor (str_replace)
2순위: artifacts + write_file
3순위: 파일 분할 후 재작성
```

### 4단계: 검증
```javascript
// 수정 후 반드시 확인
read_file("수정된파일") // 내용 확인
get_file_info("수정된파일") // 변경 시간 확인
```

---

## 📊 성공률 향상을 위한 팁

### 높은 성공률 방법 (95% 이상)
1. **write_file (신규 생성)**: 99% 성공률
2. **text_editor (str_replace)**: 95% 성공률  
3. **artifacts + write_file**: 98% 성공률

### 중간 성공률 방법 (70-80%)
1. **edit_file_lines (소형 파일)**: 80% 성공률
2. **edit_file_lines (중형 파일)**: 70% 성공률

### 낮은 성공률 방법 (50% 이하)
1. **edit_file_lines (대형 파일)**: 50% 성공률
2. **edit_file_lines (복잡한 수정)**: 40% 성공률

---

## 🎯 최적화된 작업 선택 가이드

### 파일 크기 + 수정 복잡도 매트릭스

| 파일 크기 \ 수정 복잡도 | 단순 수정 | 여러 위치 수정 | 구조적 변경 |
|----------------------|-----------|-------------|------------|
| **소형 (50줄 이하)** | text_editor | artifacts+write | write_file |
| **중형 (50-200줄)** | text_editor | artifacts+write | artifacts+write |
| **대형 (200줄+)** | artifacts+write | artifacts+write | 파일분할+write |

### 작업 시간 예상

| 방법 | 예상 시간 | 성공률 | 권장도 |
|------|----------|--------|--------|
| write_file | 1-2분 | 99% | ⭐⭐⭐⭐⭐ |
| text_editor | 2-3분 | 95% | ⭐⭐⭐⭐ |
| artifacts+write | 3-5분 | 98% | ⭐⭐⭐⭐ |
| edit_file_lines | 5-15분 | 60% | ⭐⭐ |

---

## 🚀 자동화 워크플로우 템플릿

### 템플릿 1: 안전한 파일 수정
```javascript
function safeFileEdit(filePath, modification) {
  // 1. 현재 상태 확인
  const currentContent = read_file(filePath);
  const fileInfo = get_file_info(filePath);
  
  // 2. 파일 크기에 따른 전략 선택
  if (fileInfo.lineCount < 50) {
    // 소형: 전체 재작성
    return write_file(filePath, modifiedContent);
  } else if (fileInfo.lineCount < 200) {
    // 중형: text_editor 우선
    return text_editor({ command: "str_replace", ... });
  } else {
    // 대형: artifacts 활용
    return artifactsAndWrite(filePath, modifiedContent);
  }
}
```

### 템플릿 2: 오류 복구 워크플로우
```javascript
function errorRecoveryWorkflow(filePath, targetModification) {
  try {
    // 1차 시도: text_editor
    return text_editor({ command: "str_replace", ... });
  } catch (error1) {
    try {
      // 2차 시도: artifacts + write_file
      return artifactsAndWrite(filePath, fullContent);
    } catch (error2) {
      // 3차 시도: 파일 분할
      return splitAndWrite(filePath, contentSections);
    }
  }
}
```

---

## 📋 작업 전 체크리스트 (최종)

### 필수 확인사항 ✅
- [ ] **파일 크기 확인**: 라인 수에 따른 최적 방법 선택
- [ ] **수정 복잡도 평가**: 단순/복합/구조적 변경 구분
- [ ] **디렉토리 존재 확인**: 파일 생성 시 상위 디렉토리 확인
- [ ] **백업 고려**: 중요 파일은 백업 후 작업
- [ ] **시간 여유 확보**: 충분한 시간을 두고 신중하게 작업

### 작업 중 준수사항 ✅
- [ ] **단계별 진행**: 성급한 진행보다 단계적 접근
- [ ] **결과 확인**: 각 단계마다 결과 검증
- [ ] **대안 준비**: 1차 방법 실패시 즉시 대안 사용
- [ ] **무한 재시도 금지**: 같은 방법 3회 이상 시도 금지

### 작업 후 검증사항 ✅
- [ ] **파일 내용 확인**: 의도한 대로 수정되었는지 확인
- [ ] **문법 검사**: HTML/CSS/JS 문법 오류 확인
- [ ] **기능 테스트**: 실제 브라우저에서 동작 확인
- [ ] **메모리 저장**: 작업 내용과 결과 저장

---

## 🎯 결론 및 핵심 원칙

### 🔑 핵심 원칙 3가지
1. **크기별 최적 방법 선택**: 파일 크기에 맞는 도구 사용
2. **안전성 우선**: dryRun, 단계별 검증 필수
3. **효율성 추구**: 성공률 높은 방법 우선 선택

### 🎯 성공 공식
```
적절한 도구 선택 + 충분한 사전 준비 + 단계별 검증 = 높은 성공률
```

### 📈 기대 효과
- **작업 시간 50% 단축**: 반복 시도 제거
- **성공률 90% 이상**: 적절한 도구 선택
- **스트레스 90% 감소**: 예측 가능한 결과

---

## 📞 추가 개선사항 제안

이 지침을 사용하면서 발견되는 추가 개선사항이나 새로운 패턴이 있으시면 메모리에 저장해주세요:

```bash
add-memory: "파일 수정 지침 개선사항: [구체적 내용 및 경험 사례]"
```

---

> **🎯 목표**: "파일 수정 작업에서 반복 오류 없이 한 번에 성공하여 개발 효율성을 극대화한다."

> **⚡ 핵심**: "적절한 도구를 선택하여 안전하고 효율적으로 파일을 수정한다."