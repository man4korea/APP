<?php
// C:\xampp\htdocs\AppMart\src\services\FileUploadService.php
// Create at 2508051300 Ver1.00

class FileUploadService
{
    private $config;
    private $allowedMimeTypes;
    private $allowedExtensions;
    private $maxFileSize;
    private $uploadPath;
    private $securityChecks;
    
    public function __construct()
    {
        $this->config = [
            'max_file_size' => getenv('MAX_UPLOAD_SIZE') ?: 52428800, // 50MB default
            'upload_path' => getenv('UPLOAD_PATH') ?: 'uploads/',
            'allowed_extensions' => explode(',', getenv('ALLOWED_FILE_TYPES') ?: 'zip,rar,tar.gz'),
            'virus_scan_enabled' => getenv('VIRUS_SCAN_ENABLED') ?: false,
            'quarantine_path' => getenv('QUARANTINE_PATH') ?: 'quarantine/'
        ];
        
        // Allowed MIME types for security
        $this->allowedMimeTypes = [
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/vnd.rar', 'application/x-rar-compressed'],
            'tar.gz' => ['application/gzip', 'application/x-gzip', 'application/x-tar'],
            'tar' => ['application/x-tar'],
            '7z' => ['application/x-7z-compressed']
        ];
        
        $this->maxFileSize = $this->config['max_file_size'];
        $this->uploadPath = rtrim($this->config['upload_path'], '/') . '/';
        $this->allowedExtensions = $this->config['allowed_extensions'];
        
        // Ensure upload directories exist
        $this->ensureDirectories();
    }
    
    /**
     * Upload and validate file with comprehensive security checks
     */
    public function uploadFile($fileInput, $userId, $type = 'app')
    {
        try {
            // Initial validation
            $validation = $this->validateUpload($fileInput);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            
            $file = $fileInput;
            
            // Security checks
            $securityCheck = $this->performSecurityChecks($file);
            if (!$securityCheck['safe']) {
                // Move to quarantine
                $this->quarantineFile($file, $securityCheck['reason']);
                return ['success' => false, 'message' => 'File failed security checks: ' . $securityCheck['reason']];
            }
            
            // Generate secure filename
            $filename = $this->generateSecureFilename($file['name'], $userId, $type);
            $filePath = $this->uploadPath . $filename;
            
            // Move file to secure location
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ['success' => false, 'message' => 'Failed to move uploaded file'];
            }
            
            // Post-upload security verification
            $postUploadCheck = $this->verifyUploadedFile($filePath);
            if (!$postUploadCheck['safe']) {
                // Remove unsafe file
                unlink($filePath);
                return ['success' => false, 'message' => 'File removed due to security concerns'];
            }
            
            // Log successful upload
            $this->logFileUpload($userId, $filename, $file['size'], 'success');
            
            return [
                'success' => true,
                'filename' => $filename,
                'size' => $file['size'],
                'path' => $filePath,
                'original_name' => $file['name']
            ];
            
        } catch (Exception $e) {
            error_log("파일 업로드 오류: " . $e->getMessage());
            return ['success' => false, 'message' => 'Upload failed due to server error'];
        }
    }
    
    /**
     * Validate basic upload requirements
     */
    private function validateUpload($file)
    {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'No file uploaded or invalid upload'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($file['error']);
            return ['valid' => false, 'message' => $errorMessage];
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $maxSizeMB = round($this->maxFileSize / 1024 / 1024, 2);
            return ['valid' => false, 'message' => "File too large. Maximum size: {$maxSizeMB}MB"];
        }
        
        if ($file['size'] == 0) {
            return ['valid' => false, 'message' => 'Empty file not allowed'];
        }
        
        // Check file extension
        $extension = $this->getFileExtension($file['name']);
        if (!in_array($extension, $this->allowedExtensions)) {
            $allowed = implode(', ', $this->allowedExtensions);
            return ['valid' => false, 'message' => "Invalid file type. Allowed: {$allowed}"];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Perform comprehensive security checks
     */
    private function performSecurityChecks($file)
    {
        $checks = [];
        
        // 1. MIME type validation
        $mimeCheck = $this->validateMimeType($file);
        if (!$mimeCheck['valid']) {
            return ['safe' => false, 'reason' => $mimeCheck['reason']];
        }
        
        // 2. File signature validation (magic numbers)
        $signatureCheck = $this->validateFileSignature($file['tmp_name']);
        if (!$signatureCheck['valid']) {
            return ['safe' => false, 'reason' => $signatureCheck['reason']];
        }
        
        // 3. Filename security check
        $filenameCheck = $this->validateFilename($file['name']);
        if (!$filenameCheck['valid']) {
            return ['safe' => false, 'reason' => $filenameCheck['reason']];
        }
        
        // 4. Content scanning (basic)
        $contentCheck = $this->scanFileContent($file['tmp_name']);
        if (!$contentCheck['safe']) {
            return ['safe' => false, 'reason' => $contentCheck['reason']];
        }
        
        // 5. Virus scan (if enabled)
        if ($this->config['virus_scan_enabled']) {
            $virusCheck = $this->scanForViruses($file['tmp_name']);
            if (!$virusCheck['clean']) {
                return ['safe' => false, 'reason' => 'Virus detected: ' . $virusCheck['threat']];
            }
        }
        
        return ['safe' => true, 'checks' => $checks];
    }
    
    /**
     * Validate MIME type against allowed types
     */
    private function validateMimeType($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $extension = $this->getFileExtension($file['name']);
        
        if (!isset($this->allowedMimeTypes[$extension])) {
            return ['valid' => false, 'reason' => 'Unknown file extension'];
        }
        
        if (!in_array($mimeType, $this->allowedMimeTypes[$extension])) {
            return ['valid' => false, 'reason' => "MIME type mismatch. Expected: " . 
                implode(', ', $this->allowedMimeTypes[$extension]) . ", Got: {$mimeType}"];
        }
        
        return ['valid' => true, 'mime' => $mimeType];
    }
    
    /**
     * Validate file signature (magic numbers)
     */
    private function validateFileSignature($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return ['valid' => false, 'reason' => 'Cannot read file for signature validation'];
        }
        
        $header = fread($handle, 10);
        fclose($handle);
        
        // Known file signatures
        $signatures = [
            'zip' => [
                "\x50\x4B\x03\x04", // Standard ZIP
                "\x50\x4B\x05\x06", // Empty ZIP
                "\x50\x4B\x07\x08"  // Spanned ZIP
            ],
            'rar' => [
                "\x52\x61\x72\x21\x1A\x07\x00", // RAR v1.5+
                "\x52\x61\x72\x21\x1A\x07\x01\x00" // RAR v5.0+
            ],
            'gzip' => [
                "\x1F\x8B\x08" // GZIP
            ],
            '7z' => [
                "\x37\x7A\xBC\xAF\x27\x1C" // 7-Zip
            ]
        ];
        
        $validSignature = false;
        foreach ($signatures as $type => $sigs) {
            foreach ($sigs as $sig) {
                if (substr($header, 0, strlen($sig)) === $sig) {
                    $validSignature = true;
                    break 2;
                }
            }
        }
        
        if (!$validSignature) {
            return ['valid' => false, 'reason' => 'Invalid file signature - file may be corrupted or fake'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate filename for security
     */
    private function validateFilename($filename)
    {
        // Check for dangerous patterns
        $dangerousPatterns = [
            '/\.\./', // Path traversal
            '/[<>:"\/\\\\|?*]/', // Dangerous characters
            '/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$/i', // Windows reserved names
            '/^\s*$/', // Empty or whitespace only
            '/\x00/', // Null bytes
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return ['valid' => false, 'reason' => 'Filename contains dangerous characters or patterns'];
            }
        }
        
        // Check filename length
        if (strlen($filename) > 255) {
            return ['valid' => false, 'reason' => 'Filename too long'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Scan file content for suspicious patterns
     */
    private function scanFileContent($filePath)
    {
        // Read first part of file for suspicious content
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return ['safe' => false, 'reason' => 'Cannot read file for content scanning'];
        }
        
        $content = fread($handle, 8192); // Read first 8KB
        fclose($handle);
        
        // Suspicious patterns (basic)
        $suspiciousPatterns = [
            '/(<\?php|<script|javascript:|vbscript:|onload=|onerror=)/i',
            '/(<iframe|<object|<embed|<form)/i',
            '/(eval\s*\(|exec\s*\(|system\s*\(|shell_exec)/i',
            '/(\$_GET|\$_POST|\$_REQUEST|\$_COOKIE)/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return ['safe' => false, 'reason' => 'Suspicious content detected in file'];
            }
        }
        
        return ['safe' => true];
    }
    
    /**
     * Basic virus scanning (placeholder for external scanner)
     */
    private function scanForViruses($filePath)
    {
        // This would typically integrate with ClamAV or similar
        // For now, return clean
        return ['clean' => true, 'threat' => null];
    }
    
    /**
     * Generate secure filename
     */
    private function generateSecureFilename($originalName, $userId, $type)
    {
        $extension = $this->getFileExtension($originalName);
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        
        return sprintf(
            '%s_%d_%d_%s.%s',
            $type,
            $userId,
            $timestamp,
            $random,
            $extension
        );
    }
    
    /**
     * Get file extension safely
     */
    private function getFileExtension($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Handle compound extensions
        if ($extension === 'gz' && substr($filename, -7) === '.tar.gz') {
            return 'tar.gz';
        }
        
        return $extension;
    }
    
    /**
     * Verify uploaded file post-upload
     */
    private function verifyUploadedFile($filePath)
    {
        if (!file_exists($filePath)) {
            return ['safe' => false, 'reason' => 'File does not exist after upload'];
        }
        
        // Check file permissions
        if (!is_readable($filePath)) {
            return ['safe' => false, 'reason' => 'File is not readable'];
        }
        
        // Additional verification could go here
        
        return ['safe' => true];
    }
    
    /**
     * Move suspicious file to quarantine
     */
    private function quarantineFile($file, $reason)
    {
        $quarantinePath = $this->config['quarantine_path'];
        if (!is_dir($quarantinePath)) {
            mkdir($quarantinePath, 0750, true);
        }
        
        $quarantineFile = $quarantinePath . 'quarantine_' . time() . '_' . bin2hex(random_bytes(4));
        
        if (move_uploaded_file($file['tmp_name'], $quarantineFile)) {
            error_log("파일 격리됨: {$file['name']} -> {$quarantineFile}, 사유: {$reason}");
            
            // Log to database if available
            $this->logSecurityIncident($file['name'], $reason, $quarantineFile);
        }
    }
    
    /**
     * Ensure required directories exist
     */
    private function ensureDirectories()
    {
        $directories = [
            $this->uploadPath,
            $this->config['quarantine_path']
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0750, true);
                
                // Create .htaccess for security
                $htaccessPath = $dir . '.htaccess';
                if (!file_exists($htaccessPath)) {
                    file_put_contents($htaccessPath, "Order Deny,Allow\nDeny from all\n");
                }
            }
        }
    }
    
    /**
     * Log file upload
     */
    private function logFileUpload($userId, $filename, $size, $status)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO file_uploads (user_id, filename, original_name, file_size, status, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            // This would need a file_uploads table to be created
        } catch (Exception $e) {
            error_log("파일 업로드 로그 실패: " . $e->getMessage());
        }
    }
    
    /**
     * Log security incident
     */
    private function logSecurityIncident($filename, $reason, $quarantinePath)
    {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO security_incidents (filename, reason, quarantine_path, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            // This would need a security_incidents table to be created
        } catch (Exception $e) {
            error_log("보안 사고 로그 실패: " . $e->getMessage());
        }
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds PHP upload_max_filesize limit',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by PHP extension'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
    
    /**
     * Clean up old files
     */
    public function cleanupOldFiles($daysOld = 30)
    {
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        
        $files = glob($this->uploadPath . '*');
        $cleanedCount = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $cleanedCount++;
                }
            }
        }
        
        return $cleanedCount;
    }
}