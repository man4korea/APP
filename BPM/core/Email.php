<?php
// 📁 C:\xampp\htdocs\BPM\core\Email.php
// Create at 2508041120 Ver1.00

namespace BPM\Core;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * 이메일 발송 클래스
 * SMTP 설정을 통한 안전한 이메일 발송
 * 템플릿 기반 이메일 생성 지원
 */
class Email
{
    private static ?Email $instance = null;
    private array $config;
    private PHPMailer $mailer;

    private function __construct()
    {
        $this->loadConfig();
        $this->setupMailer();
    }

    public static function getInstance(): Email
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 이메일 설정 로드
     */
    private function loadConfig(): void
    {
        $this->config = [
            'smtp_host' => $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com',
            'smtp_port' => $_ENV['SMTP_PORT'] ?? 587,
            'smtp_username' => $_ENV['SMTP_USERNAME'] ?? '',
            'smtp_password' => $_ENV['SMTP_PASSWORD'] ?? '',
            'smtp_encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
            'from_email' => $_ENV['FROM_EMAIL'] ?? 'noreply@bpm.com',
            'from_name' => $_ENV['FROM_NAME'] ?? 'BPM System',
            'debug_mode' => $_ENV['EMAIL_DEBUG'] ?? false
        ];
    }

    /**
     * PHPMailer 설정
     */
    private function setupMailer(): void
    {
        $this->mailer = new PHPMailer(true);

        try {
            // SMTP 설정
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp_host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp_username'];
            $this->mailer->Password = $this->config['smtp_password'];
            $this->mailer->SMTPSecure = $this->config['smtp_encryption'];
            $this->mailer->Port = $this->config['smtp_port'];

            // 기본 발신자 설정
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);

            // 디버그 모드
            if ($this->config['debug_mode']) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }

            // 한글 지원
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';

        } catch (Exception $e) {
            error_log("Email Setup Error: " . $e->getMessage());
            throw new Exception("Email configuration failed: " . $e->getMessage());
        }
    }

    /**
     * 단순 텍스트 이메일 발송
     */
    public function sendPlainEmail(string $to, string $subject, string $body, string $toName = null): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAllRecipients();
            
            $this->mailer->addAddress($to, $toName ?? $to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->isHTML(false);

            return $this->mailer->send();

        } catch (Exception $e) {
            error_log("sendPlainEmail Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * HTML 이메일 발송
     */
    public function sendHtmlEmail(string $to, string $subject, string $htmlBody, string $textBody = null, string $toName = null): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAllRecipients();
            
            $this->mailer->addAddress($to, $toName ?? $to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody ?? strip_tags($htmlBody);
            $this->mailer->isHTML(true);

            return $this->mailer->send();

        } catch (Exception $e) {
            error_log("sendHtmlEmail Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 회사 초대 이메일 발송
     */
    public function sendCompanyInvitation(string $email, string $companyName, string $inviterName, string $inviteToken): bool
    {
        $subject = "🏢 {$companyName}에서 초대되었습니다 - BPM 시스템";
        
        $inviteUrl = $this->getBaseUrl() . "/invite.php?token={$inviteToken}";
        
        $htmlBody = $this->getInvitationTemplate($companyName, $inviterName, $inviteUrl);
        $textBody = "안녕하세요!\n\n{$inviterName}님이 {$companyName} BPM 시스템에 초대하였습니다.\n\n다음 링크를 클릭하여 가입을 완료하세요:\n{$inviteUrl}\n\n이 초대 링크는 7일 후 만료됩니다.\n\nBPM 시스템 드림";

        return $this->sendHtmlEmail($email, $subject, $htmlBody, $textBody);
    }

    /**
     * 비밀번호 재설정 이메일 발송
     */
    public function sendPasswordReset(string $email, string $userName, string $resetToken): bool
    {
        $subject = "🔐 BPM 시스템 비밀번호 재설정";
        
        $resetUrl = $this->getBaseUrl() . "/reset-password.php?token={$resetToken}";
        
        $htmlBody = $this->getPasswordResetTemplate($userName, $resetUrl);
        $textBody = "안녕하세요 {$userName}님!\n\nBPM 시스템 비밀번호 재설정을 요청하셨습니다.\n\n다음 링크를 클릭하여 새 비밀번호를 설정하세요:\n{$resetUrl}\n\n이 재설정 링크는 30분 후 만료됩니다.\n\n만약 요청하지 않으셨다면 이 이메일을 무시하세요.\n\nBPM 시스템 드림";

        return $this->sendHtmlEmail($email, $subject, $htmlBody, $textBody);
    }

    /**
     * 계정 활성화 이메일 발송
     */
    public function sendAccountActivation(string $email, string $userName, string $activationToken): bool
    {
        $subject = "✅ BPM 시스템 계정 활성화";
        
        $activationUrl = $this->getBaseUrl() . "/activate.php?token={$activationToken}";
        
        $htmlBody = $this->getActivationTemplate($userName, $activationUrl);
        $textBody = "안녕하세요 {$userName}님!\n\nBPM 시스템 가입을 환영합니다.\n\n다음 링크를 클릭하여 계정을 활성화하세요:\n{$activationUrl}\n\n이 활성화 링크는 24시간 후 만료됩니다.\n\nBPM 시스템 드림";

        return $this->sendHtmlEmail($email, $subject, $htmlBody, $textBody);
    }

    /**
     * 권한 변경 알림 이메일 발송
     */
    public function sendRoleChangeNotification(string $email, string $userName, string $companyName, string $oldRole, string $newRole, string $changedBy): bool
    {
        $subject = "🔄 권한 변경 알림 - {$companyName}";
        
        $roleNames = [
            'founder' => '창립자',
            'admin' => '관리자', 
            'process_owner' => '프로세스 담당자',
            'member' => '일반 구성원'
        ];
        
        $oldRoleName = $roleNames[$oldRole] ?? $oldRole;
        $newRoleName = $roleNames[$newRole] ?? $newRole; 
        
        $htmlBody = $this->getRoleChangeTemplate($userName, $companyName, $oldRoleName, $newRoleName, $changedBy);
        $textBody = "안녕하세요 {$userName}님!\n\n{$companyName}에서 권한이 변경되었습니다.\n\n변경 내용:\n- 이전 권한: {$oldRoleName}\n- 새 권한: {$newRoleName}\n- 변경자: {$changedBy}\n\n새로운 권한으로 시스템에 접속하여 변경된 기능을 확인하세요.\n\nBPM 시스템 드림";

        return $this->sendHtmlEmail($email, $subject, $htmlBody, $textBody);
    }

    /**
     * 초대 이메일 템플릿
     */
    private function getInvitationTemplate(string $companyName, string $inviterName, string $inviteUrl): string
    {
        return "
        <!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>회사 초대</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏢 회사 초대</h1>
                    <p>BPM 시스템에 오신 것을 환영합니다!</p>
                </div>
                <div class='content'>
                    <h2>안녕하세요!</h2>
                    <p><strong>{$inviterName}</strong>님이 <strong>{$companyName}</strong> BPM 시스템에 초대하였습니다.</p>
                    
                    <p>BPM(Business Process Management) 시스템을 통해 효율적인 업무 프로세스 관리를 경험하세요:</p>
                    <ul>
                        <li>📊 체계적인 업무 프로세스 관리</li>
                        <li>👥 팀원과의 원활한 협업</li>
                        <li>📈 업무 효율성 향상</li>
                        <li>🔒 안전한 회사별 데이터 관리</li>
                    </ul>
                    
                    <div style='text-align: center;'>
                        <a href='{$inviteUrl}' class='btn'>초대 수락하고 가입하기</a>
                    </div>
                    
                    <p><small>⚠️ 이 초대 링크는 <strong>7일 후</strong> 만료됩니다. 빠른 시일 내에 가입을 완료해주세요.</small></p>
                </div>
                <div class='footer'>
                    <p>BPM Total Business Process Management System<br>
                    이 이메일은 자동으로 발송되었습니다.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * 비밀번호 재설정 템플릿
     */
    private function getPasswordResetTemplate(string $userName, string $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>비밀번호 재설정</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #e74c3c; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 비밀번호 재설정</h1>
                    <p>BPM 시스템</p>
                </div>
                <div class='content'>
                    <h2>안녕하세요 {$userName}님!</h2>
                    <p>BPM 시스템 비밀번호 재설정을 요청하셨습니다.</p>
                    
                    <p>아래 버튼을 클릭하여 새로운 비밀번호를 설정하세요:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$resetUrl}' class='btn'>새 비밀번호 설정하기</a>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ 보안 알림</strong>
                        <ul>
                            <li>이 재설정 링크는 <strong>30분 후</strong> 만료됩니다</li>
                            <li>만약 비밀번호 재설정을 요청하지 않으셨다면 이 이메일을 무시하세요</li>
                            <li>의심스러운 활동이 감지되면 즉시 관리자에게 문의하세요</li>
                        </ul>
                    </div>
                </div>
                <div class='footer'>
                    <p>BPM Total Business Process Management System<br>
                    이 이메일은 자동으로 발송되었습니다.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * 계정 활성화 템플릿
     */
    private function getActivationTemplate(string $userName, string $activationUrl): string
    {
        return "
        <!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>계정 활성화</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #2ecc71; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ 계정 활성화</h1>
                    <p>BPM 시스템 가입을 환영합니다!</p>
                </div>
                <div class='content'>
                    <h2>안녕하세요 {$userName}님!</h2>
                    <p>BPM 시스템 가입을 진심으로 환영합니다.</p>
                    
                    <p>계정을 활성화하여 모든 기능을 사용하세요:</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$activationUrl}' class='btn'>계정 활성화하기</a>
                    </div>
                    
                    <p><small>⚠️ 이 활성화 링크는 <strong>24시간 후</strong> 만료됩니다.</small></p>
                </div>
                <div class='footer'>
                    <p>BPM Total Business Process Management System<br>
                    이 이메일은 자동으로 발송되었습니다.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * 권한 변경 알림 템플릿
     */
    private function getRoleChangeTemplate(string $userName, string $companyName, string $oldRole, string $newRole, string $changedBy): string
    {
        return "
        <!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>권한 변경 알림</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .change-box { background: white; border: 2px solid #f39c12; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔄 권한 변경 알림</h1>
                    <p>{$companyName}</p>
                </div>
                <div class='content'>
                    <h2>안녕하세요 {$userName}님!</h2>
                    <p>{$companyName}에서 귀하의 권한이 변경되었습니다.</p>
                    
                    <div class='change-box'>
                        <h3>권한 변경 내용</h3>
                        <p><strong>이전 권한:</strong> {$oldRole}</p>
                        <p><strong>새 권한:</strong> <span style='color: #f39c12;'>{$newRole}</span></p>
                        <p><strong>변경자:</strong> {$changedBy}</p>
                        <p><strong>변경 시간:</strong> " . date('Y-m-d H:i:s') . "</p>
                    </div>
                    
                    <p>새로운 권한으로 시스템에 접속하여 변경된 기능을 확인하세요.</p>
                </div>
                <div class='footer'>
                    <p>BPM Total Business Process Management System<br>
                    이 이메일은 자동으로 발송되었습니다.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * 베이스 URL 가져오기
     */
    private function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}/BPM";
    }

    /**
     * 이메일 전송 테스트
     */
    public function testConnection(): array
    {
        try {
            // SMTP 연결 테스트
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            
            return [
                'success' => true,
                'message' => 'SMTP connection successful'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 이메일 발송 로그 기록
     */
    private function logEmailSent(string $to, string $subject, bool $success, string $error = null): void
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'success' => $success,
            'error' => $error
        ];
        
        error_log("Email Log: " . json_encode($logData));
    }
}
?>