# 💳 AppMart 결제 시스템 구현 계획서

## 📋 개요
**작성일**: 2025-08-05  
**상태**: 계획 수립 완료 (구현 대기)  
**우선순위**: 단기 개선 작업 1순위  

---

## 🎯 선택된 솔루션: 아임포트(iamport)

### ✅ 선택 이유
- **한국 시장 최적화**: 토스페이, 카카오페이, 네이버페이, 신용카드 완전 지원
- **개발 친화성**: PHP SDK 및 상세한 문서 제공
- **경제성**: 스타트업 친화적 수수료 구조 (2.9% + VAT)
- **신속성**: 빠른 연동 및 테스트 환경 제공
- **안전성**: PCI DSS 준수, 강력한 보안 시스템

### 🔄 대안 솔루션
- **Plan B**: 토스페이먼츠 (기업 안정성 우선)
- **Plan C**: Stripe (향후 글로벌 확장 시)

---

## 🗺️ 3주 구현 로드맵

### **1주차: 기반 구축**
- [ ] 아임포트 개발자 계정 신청
- [ ] Composer 및 아임포트 PHP SDK 설치
- [ ] 결제 관련 데이터베이스 스키마 설계
- [ ] PaymentController, PaymentService 기본 구조 구현

### **2주차: 핵심 기능**
- [ ] 결제 요청 API 구현
- [ ] 웹훅 처리 및 결제 검증 로직
- [ ] 앱 상세 페이지 "구매하기" 버튼 연동
- [ ] 구매 완료 후 다운로드 권한 부여

### **3주차: 완성 및 최적화**
- [ ] 결제 UI/UX 최적화
- [ ] 결제 내역 관리 페이지
- [ ] 환불 처리 기능
- [ ] 전체 테스트 및 배포

---

## 🏗️ 기술 구현 계획

### 📊 데이터베이스 스키마 확장
```sql
-- 결제 정보 테이블
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    application_id INT NOT NULL,
    imp_uid VARCHAR(100) NOT NULL,        -- 아임포트 결제 고유ID
    merchant_uid VARCHAR(100) NOT NULL,   -- 가맹점 주문번호
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('ready', 'paid', 'cancelled', 'failed') DEFAULT 'ready',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (application_id) REFERENCES applications(id)
);

-- 환불 정보 테이블
CREATE TABLE refunds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    refund_amount DECIMAL(10,2) NOT NULL,
    reason TEXT,
    refunded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id)
);
```

### 🔧 PHP 구조 설계
```php
// 결제 관련 클래스 구조
src/
├── controllers/
│   ├── PaymentController.php    # 결제 요청/완료 처리
│   └── WebhookController.php    # 아임포트 웹훅 처리
├── services/
│   ├── PaymentService.php       # 결제 비즈니스 로직
│   └── IamportService.php       # 아임포트 API 연동
└── models/
    ├── Payment.php              # 결제 데이터 모델
    └── Refund.php               # 환불 데이터 모델
```

### 🔄 결제 플로우
1. 사용자가 앱 상세 페이지에서 "구매하기" 클릭
2. 결제 정보 생성 (merchant_uid 생성)
3. 아임포트 결제창 팝업 호출
4. 사용자 결제 완료
5. 아임포트 서버에서 웹훅으로 결제 완료 통지
6. 결제 검증 후 구매 내역 DB 저장
7. 사용자에게 다운로드 권한 부여

---

## 🛡️ 보안 고려사항

### 🔐 필수 보안 조치
- **웹훅 서명 검증**: 아임포트에서 전송되는 데이터 무결성 확인
- **결제 금액 검증**: 클라이언트 조작 방지를 위한 서버 측 금액 재확인
- **중복 결제 방지**: merchant_uid를 통한 중복 처리 차단
- **HTTPS 필수**: 결제 데이터 전송 시 SSL 암호화
- **토큰 기반 인증**: 결제 요청 시 CSRF 토큰 검증

### 📋 컴플라이언스
- **PCI DSS**: 아임포트를 통한 자동 준수
- **개인정보보호법**: 최소한의 결제 정보만 저장
- **전자상거래법**: 청약철회, 환불 규정 준수

---

## 💰 비용 구조

### 아임포트 수수료
- **신용카드**: 2.9% + VAT
- **간편결제**: 2.9% + VAT  
- **계좌이체**: 1.5% + VAT
- **가상계좌**: 2.9% + VAT

### 예상 월 거래량 (MVP 기준)
- **월 거래액**: $1,000 ~ $5,000
- **월 수수료**: $29 ~ $145
- **연간 예상 수수료**: $348 ~ $1,740

---

## 🚨 리스크 및 대응방안

### 주요 리스크
1. **아임포트 계정 승인 지연** (1-3영업일)
   - **대응**: 미리 계정 신청, 토스페이먼츠 백업 플랜
   
2. **웹호스팅 환경 제약**
   - **대응**: Dothome HTTPS 지원 확인, 필요시 호스팅 업그레이드
   
3. **결제 시스템 다운타임**
   - **대응**: 아임포트 상태 페이지 모니터링, 사용자 알림 시스템

4. **보안 이슈**
   - **대응**: 정기 보안 감사, 웹훅 서명 검증 필수

---

## 📈 성공 지표

### 기술적 KPI
- **결제 완료율**: >95%
- **웹훅 처리 성공률**: >99.9%
- **결제-권한부여 연동**: 100% 정확도
- **평균 결제 처리 시간**: <30초

### 비즈니스 KPI
- **결제 전환율**: >5% (앱 조회 → 결제)
- **결제 포기율**: <20%
- **환불률**: <5%
- **고객 결제 만족도**: >4.5/5

---

## 🔄 향후 확장 계획

### Phase 2 (3-6개월)
- **구독 결제 시스템**: 월/연 구독 앱 지원
- **다양한 결제 수단**: 페이코, 토스 등 추가
- **할인 쿠폰 시스템**: 프로모션 코드 지원

### Phase 3 (6-12개월)
- **글로벌 결제**: Stripe 연동으로 해외 진출
- **암호화폐 결제**: 비트코인, 이더리움 지원
- **B2B 결제**: 기업 대량 구매 지원

---

## 📞 연락처 및 리소스

### 아임포트 관련
- **개발자 문서**: https://docs.iamport.kr/
- **PHP SDK**: https://github.com/iamport/iamport-rest-client-php
- **고객지원**: support@iamport.kr

### 내부 담당자
- **프로젝트 매니저**: AppMart 개발팀
- **기술 책임자**: Claude Assistant
- **보안 검토**: 추후 지정

---

*문서 버전: 1.0*  
*최종 업데이트: 2025-08-05*  
*다음 검토 예정: 구현 시작 시점*