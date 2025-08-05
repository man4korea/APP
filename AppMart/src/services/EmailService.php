<?php
// C:\xampp\htdocs\AppMart\src\services\EmailService.php
// Create at 2508051130 Ver1.00

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $config;
    
    public function __construct()
    {
        $this->config = [
            'smtp_host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
            'smtp_port' => getenv('SMTP_PORT') ?: 587,
            'smtp_username' => getenv('SMTP_USERNAME') ?: '',
            'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
            'from_email' => getenv('FROM_EMAIL') ?: 'noreply@appmart.local',
            'from_name' => getenv('FROM_NAME') ?: 'AppMart',
            'smtp_secure' => getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS
        ];
        
        $this->initializeMailer();
    }
    
    private function initializeMailer()
    {
        $this->mailer = new PHPMailer(true);
        
        try {
            // SMTP 설정
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp_username'];
            $this->mailer->Password = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = $this->config['smtp_secure'];
            $this->mailer->Port = $this->config['smtp_port'];
            
            // 발신자 설정
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);
            
            // 인코딩 설정
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';
            
        } catch (Exception $e) {
            error_log("EmailService 초기화 오류: " . $e->getMessage());
        }
    }
    
    public function sendWelcomeEmail($userEmail, $userName, $verificationToken = null)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userName);
            
            $this->mailer->Subject = '🎉 AppMart에 오신 것을 환영합니다!';
            
            $verificationLink = $verificationToken ? 
                url('/auth/verify-email?token=' . $verificationToken) : null;
            
            $htmlBody = $this->getWelcomeEmailTemplate($userName, $verificationLink);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            
            // 텍스트 버전
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $htmlBody));
            
            $result = $this->mailer->send();
            
            if ($result) {
                $this->logEmail($userEmail, 'welcome', 'sent');
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("환영 이메일 발송 오류: " . $e->getMessage());
            $this->logEmail($userEmail, 'welcome', 'failed', $e->getMessage());
            return false;
        }
    }
    
    public function sendPasswordResetEmail($userEmail, $userName, $resetToken)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userName);
            
            $this->mailer->Subject = '🔐 AppMart 비밀번호 재설정';
            
            $resetLink = url('/auth/reset-password?token=' . $resetToken);
            
            $htmlBody = $this->getPasswordResetEmailTemplate($userName, $resetLink);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $htmlBody));
            
            $result = $this->mailer->send();
            
            if ($result) {
                $this->logEmail($userEmail, 'password_reset', 'sent');
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("비밀번호 재설정 이메일 발송 오류: " . $e->getMessage());
            $this->logEmail($userEmail, 'password_reset', 'failed', $e->getMessage());
            return false;
        }
    }
    
    public function sendAppStatusNotification($userEmail, $userName, $appTitle, $status, $reason = null)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userName);
            
            $statusText = $status === 'approved' ? '승인' : '거부';
            $emoji = $status === 'approved' ? '✅' : '❌';
            
            $this->mailer->Subject = "{$emoji} 앱 검토 결과: {$appTitle}";
            
            $htmlBody = $this->getAppStatusEmailTemplate($userName, $appTitle, $status, $reason);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $htmlBody));
            
            $result = $this->mailer->send();
            
            if ($result) {
                $this->logEmail($userEmail, 'app_status', 'sent');
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("앱 상태 알림 이메일 발송 오류: " . $e->getMessage());
            $this->logEmail($userEmail, 'app_status', 'failed', $e->getMessage());
            return false;
        }
    }
    
    public function sendPurchaseConfirmation($userEmail, $userName, $appTitle, $amount, $orderId)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($userEmail, $userName);
            
            $this->mailer->Subject = '💰 구매 완료: ' . $appTitle;
            
            $htmlBody = $this->getPurchaseConfirmationTemplate($userName, $appTitle, $amount, $orderId);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $htmlBody;
            
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $htmlBody));
            
            $result = $this->mailer->send();
            
            if ($result) {
                $this->logEmail($userEmail, 'purchase', 'sent');
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("구매 확인 이메일 발송 오류: " . $e->getMessage());
            $this->logEmail($userEmail, 'purchase', 'failed', $e->getMessage());
            return false;
        }
    }
    
    private function getWelcomeEmailTemplate($userName, $verificationLink = null)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>AppMart에 오신 것을 환영합니다</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 2em;">🎉 환영합니다!</h1>
                <p style="margin: 10px 0 0 0; font-size: 1.1em;">AppMart에 가입해 주셔서 감사합니다</p>
            </div>
            
            <div style="background: #f8fafc; padding: 25px; border-radius: 8px; margin-bottom: 25px;">
                <h2 style="color: #1f2937; margin-top: 0;">안녕하세요, ' . htmlspecialchars($userName) . '님!</h2>
                
                <p>AppMart 커뮤니티에 가입해 주셔서 진심으로 감사합니다. 이제 다음과 같은 기능을 이용하실 수 있습니다:</p>
                
                <ul style="padding-left: 20px;">
                    <li>🔍 <strong>앱 탐색</strong>: 다양한 카테고리의 혁신적인 앱들을 둘러보세요</li>
                    <li>💻 <strong>앱 개발</strong>: 나만의 앱을 업로드하고 전 세계와 공유하세요</li>
                    <li>⭐ <strong>리뷰 작성</strong>: 다른 사용자들과 앱 경험을 공유하세요</li>
                    <li>🛒 <strong>안전한 구매</strong>: 보안이 보장된 결제 시스템으로 안전하게 구매하세요</li>
                </ul>
            </div>';
        
        if ($verificationLink) {
            $html .= '
            <div style="background: #fef3c7; border: 1px solid #f59e0b; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <h3 style="color: #92400e; margin-top: 0;">📧 이메일 인증이 필요합니다</h3>
                <p style="margin-bottom: 20px;">계정을 활성화하려면 아래 버튼을 클릭하여 이메일 주소를 인증해 주세요:</p>
                <div style="text-align: center;">
                    <a href="' . $verificationLink . '" style="display: inline-block; background: #10b981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">✅ 이메일 인증하기</a>
                </div>
                <p style="font-size: 0.9em; color: #6b7280; margin-top: 15px;">링크가 작동하지 않으면 다음 주소를 복사하여 브라우저에 붙여넣으세요:<br><span style="word-break: break-all;">' . $verificationLink . '</span></p>
            </div>';
        }
        
        $html .= '
            <div style="background: #e5e7eb; padding: 20px; border-radius: 8px; text-align: center;">
                <h3 style="color: #374151; margin-top: 0;">🚀 시작해 보세요!</h3>
                <p style="margin-bottom: 20px;">지금 바로 AppMart를 탐험해 보세요.</p>
                <a href="' . url('/') . '" style="display: inline-block; background: #3b82f6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold;">AppMart 둘러보기</a>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.9em;">
                <p>질문이 있으시면 언제든지 <a href="mailto:support@appmart.local" style="color: #3b82f6;">support@appmart.local</a>로 연락주세요.</p>
                <p>© 2025 AppMart. All rights reserved.</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    private function getPasswordResetEmailTemplate($userName, $resetLink)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>비밀번호 재설정</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; background: #fee2e2; color: #991b1b; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 2em;">🔐 비밀번호 재설정</h1>
            </div>
            
            <div style="background: #f8fafc; padding: 25px; border-radius: 8px; margin-bottom: 25px;">
                <h2 style="color: #1f2937; margin-top: 0;">안녕하세요, ' . htmlspecialchars($userName) . '님</h2>
                
                <p>AppMart 계정의 비밀번호 재설정을 요청하셨습니다.</p>
                
                <p>아래 버튼을 클릭하여 새로운 비밀번호를 설정해 주세요:</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $resetLink . '" style="display: inline-block; background: #ef4444; color: white; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">🔑 비밀번호 재설정</a>
                </div>
                
                <div style="background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 6px; margin-top: 20px;">
                    <p style="margin: 0; color: #92400e;"><strong>⚠️ 보안 알림:</strong></p>
                    <ul style="color: #92400e; margin: 10px 0; padding-left: 20px;">
                        <li>이 링크는 24시간 후에 만료됩니다</li>
                        <li>비밀번호 재설정을 요청하지 않으셨다면 이 이메일을 무시하세요</li>
                        <li>링크를 다른 사람과 공유하지 마세요</li>
                    </ul>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.9em;">
                <p>링크가 작동하지 않으면 다음 주소를 복사하여 브라우저에 붙여넣으세요:</p>
                <p style="word-break: break-all; background: #f3f4f6; padding: 10px; border-radius: 4px;">' . $resetLink . '</p>
                <p style="margin-top: 20px;">© 2025 AppMart. All rights reserved.</p>
            </div>
        </body>
        </html>';
    }
    
    private function getAppStatusEmailTemplate($userName, $appTitle, $status, $reason = null)
    {
        $isApproved = $status === 'approved';
        $statusText = $isApproved ? '승인되었습니다' : '거부되었습니다';
        $emoji = $isApproved ? '✅' : '❌';
        $bgColor = $isApproved ? '#dcfce7' : '#fee2e2';
        $textColor = $isApproved ? '#166534' : '#991b1b';
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>앱 검토 결과</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; background: ' . $bgColor . '; color: ' . $textColor . '; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 2em;">' . $emoji . ' 앱 검토 완료</h1>
            </div>
            
            <div style="background: #f8fafc; padding: 25px; border-radius: 8px; margin-bottom: 25px;">
                <h2 style="color: #1f2937; margin-top: 0;">안녕하세요, ' . htmlspecialchars($userName) . '님</h2>
                
                <p>제출해 주신 앱 <strong>"' . htmlspecialchars($appTitle) . '"</strong>의 검토가 완료되었습니다.</p>
                
                <div style="background: ' . $bgColor . '; border: 1px solid ' . ($isApproved ? '#bbf7d0' : '#fecaca') . '; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: ' . $textColor . '; margin-top: 0;">검토 결과: ' . $statusText . '</h3>';
        
        if ($isApproved) {
            $html .= '
                    <p style="color: ' . $textColor . ';">축하합니다! 귀하의 앱이 AppMart의 품질 기준을 충족하여 승인되었습니다.</p>
                    <p style="color: ' . $textColor . ';">이제 다른 사용자들이 귀하의 앱을 검색하고 구매할 수 있습니다.</p>';
        } else {
            $html .= '
                    <p style="color: ' . $textColor . ';">죄송합니다. 귀하의 앱이 현재 AppMart의 품질 기준을 충족하지 않아 거부되었습니다.</p>';
            
            if ($reason) {
                $html .= '
                    <div style="background: white; padding: 15px; border-radius: 6px; margin-top: 15px;">
                        <strong>거부 사유:</strong><br>
                        ' . htmlspecialchars($reason) . '
                    </div>';
            }
            
            $html .= '
                    <p style="color: ' . $textColor . '; margin-top: 15px;">수정 후 다시 제출해 주세요.</p>';
        }
        
        $html .= '
                </div>
            </div>';
        
        if ($isApproved) {
            $html .= '
            <div style="text-align: center; background: #e5e7eb; padding: 20px; border-radius: 8px;">
                <h3 style="color: #374151; margin-top: 0;">🎉 다음 단계</h3>
                <p style="margin-bottom: 20px;">개발자 대시보드에서 앱 통계와 수익을 확인해 보세요.</p>
                <a href="' . url('/developer/dashboard') . '" style="display: inline-block; background: #3b82f6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold;">대시보드 보기</a>
            </div>';
        } else {
            $html .= '
            <div style="text-align: center; background: #e5e7eb; padding: 20px; border-radius: 8px;">
                <h3 style="color: #374151; margin-top: 0;">🔄 다시 시도</h3>
                <p style="margin-bottom: 20px;">피드백을 반영하여 앱을 수정한 후 다시 제출해 주세요.</p>
                <a href="' . url('/apps/create') . '" style="display: inline-block; background: #10b981; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold;">앱 수정하기</a>
            </div>';
        }
        
        $html .= '
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.9em;">
                <p>질문이 있으시면 <a href="mailto:support@appmart.local" style="color: #3b82f6;">support@appmart.local</a>로 연락주세요.</p>
                <p>© 2025 AppMart. All rights reserved.</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    private function getPurchaseConfirmationTemplate($userName, $appTitle, $amount, $orderId)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>구매 확인</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; background: #dcfce7; color: #166534; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                <h1 style="margin: 0; font-size: 2em;">💰 구매 완료</h1>
                <p style="margin: 10px 0 0 0;">결제가 성공적으로 처리되었습니다</p>
            </div>
            
            <div style="background: #f8fafc; padding: 25px; border-radius: 8px; margin-bottom: 25px;">
                <h2 style="color: #1f2937; margin-top: 0;">안녕하세요, ' . htmlspecialchars($userName) . '님</h2>
                
                <p><strong>"' . htmlspecialchars($appTitle) . '"</strong> 앱을 구매해 주셔서 감사합니다!</p>
                
                <div style="background: white; border: 1px solid #e5e7eb; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="color: #374151; margin-top: 0;">📋 구매 내역</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; color: #6b7280;">앱 이름:</td>
                            <td style="padding: 8px 0; font-weight: bold;">' . htmlspecialchars($appTitle) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #6b7280;">결제 금액:</td>
                            <td style="padding: 8px 0; font-weight: bold; color: #10b981;">$' . number_format($amount, 2) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #6b7280;">주문 번호:</td>
                            <td style="padding: 8px 0; font-family: monospace; background: #f3f4f6; padding: 4px 8px; border-radius: 4px;">' . htmlspecialchars($orderId) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: #6b7280;">구매 일시:</td>
                            <td style="padding: 8px 0;">' . date('Y년 m월 d일 H:i') . '</td>
                        </tr>
                    </table>
                </div>
                
                <div style="background: #dbeafe; border: 1px solid #3b82f6; padding: 15px; border-radius: 6px;">
                    <p style="margin: 0; color: #1e40af;"><strong>🎉 이제 앱을 다운로드하실 수 있습니다!</strong></p>
                    <p style="margin: 10px 0 0 0; color: #1e40af;">구매한 앱은 마이페이지의 "구매 내역"에서 언제든지 다시 다운로드하실 수 있습니다.</p>
                </div>
            </div>
            
            <div style="text-align: center; background: #e5e7eb; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <h3 style="color: #374151; margin-top: 0;">📱 앱 다운로드</h3>
                <p style="margin-bottom: 20px;">지금 바로 구매한 앱을 다운로드해 보세요!</p>
                <a href="' . url('/my/purchases') . '" style="display: inline-block; background: #10b981; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold;">다운로드하기</a>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.9em;">
                <p>구매와 관련된 문의사항이 있으시면 <a href="mailto:support@appmart.local" style="color: #3b82f6;">support@appmart.local</a>로 연락주세요.</p>
                <p>© 2025 AppMart. All rights reserved.</p>
            </div>
        </body>
        </html>';
    }
    
    private function logEmail($recipient, $type, $status, $error = null)
    {
        try {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("
                INSERT INTO email_logs (recipient, email_type, status, error_message, sent_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$recipient, $type, $status, $error]);
            
        } catch (Exception $e) {
            error_log("이메일 로그 저장 오류: " . $e->getMessage());
        }
    }
    
    public function testConnection()
    {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            return true;
        } catch (Exception $e) {
            error_log("SMTP 연결 테스트 실패: " . $e->getMessage());
            return false;
        }
    }
}