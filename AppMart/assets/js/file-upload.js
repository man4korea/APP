// C:\xampp\htdocs\AppMart\assets\js\file-upload.js
// Create at 2508051400 Ver1.00

class SecureFileUpload {
    constructor(options) {
        this.options = {
            maxFileSize: 52428800, // 50MB default
            allowedTypes: ['zip', 'rar', 'tar.gz', '7z'],
            chunkSize: 1024 * 1024, // 1MB chunks
            maxRetries: 3,
            retryDelay: 1000,
            ...options
        };
        
        this.currentFile = null;
        this.uploadProgress = 0;
        this.isUploading = false;
        this.retryCount = 0;
        
        this.initializeElements();
        this.bindEvents();
    }
    
    initializeElements() {
        this.dropZone = document.getElementById(this.options.dropZoneId || 'appFileDropZone');
        this.fileInput = document.getElementById(this.options.fileInputId || 'app_file');
        this.progressContainer = document.getElementById(this.options.progressId || 'uploadProgress');
        this.errorContainer = document.getElementById(this.options.errorId || 'uploadErrors');
        
        // Create progress elements if they don't exist
        if (!this.progressContainer) {
            this.createProgressElements();
        }
    }
    
    bindEvents() {
        if (this.dropZone) {
            this.dropZone.addEventListener('dragover', this.handleDragOver.bind(this));
            this.dropZone.addEventListener('dragleave', this.handleDragLeave.bind(this));
            this.dropZone.addEventListener('drop', this.handleDrop.bind(this));
            this.dropZone.addEventListener('click', () => this.fileInput?.click());
        }
        
        if (this.fileInput) {
            this.fileInput.addEventListener('change', this.handleFileSelect.bind(this));
        }
        
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            document.addEventListener(eventName, this.preventDefaults, false);
        });
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    handleDragOver(e) {
        this.preventDefaults(e);
        this.dropZone.style.borderColor = '#3b82f6';
        this.dropZone.style.background = '#f0f9ff';
    }
    
    handleDragLeave(e) {
        this.preventDefaults(e);
        this.dropZone.style.borderColor = '#d1d5db';
        this.dropZone.style.background = 'transparent';
    }
    
    handleDrop(e) {
        this.preventDefaults(e);
        this.handleDragLeave(e);
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            this.processFile(files[0]);
        }
    }
    
    handleFileSelect(e) {
        const files = e.target.files;
        if (files.length > 0) {
            this.processFile(files[0]);
        }
    }
    
    async processFile(file) {
        this.currentFile = file;
        this.clearErrors();
        
        // Validate file
        const validation = this.validateFile(file);
        if (!validation.valid) {
            this.showError(validation.errors);
            return;
        }
        
        // Show file info
        this.displayFileInfo(file);
        
        // Start security scan
        this.showProgress('Scanning file for security threats...', 0);
        
        try {
            const scanResult = await this.performSecurityScan(file);
            if (!scanResult.safe) {
                throw new Error(scanResult.message);
            }
            
            // Begin upload
            await this.startUpload(file);
            
        } catch (error) {
            this.showError(['Security scan failed: ' + error.message]);
            this.hideProgress();
        }
    }
    
    validateFile(file) {
        const errors = [];
        
        // Check file size
        if (file.size > this.options.maxFileSize) {
            const maxSizeMB = Math.round(this.options.maxFileSize / 1024 / 1024);
            errors.push(`File too large (${this.formatFileSize(file.size)}). Maximum: ${maxSizeMB}MB`);
        }
        
        if (file.size === 0) {
            errors.push('Empty files are not allowed');
        }
        
        // Check file type
        const extension = this.getFileExtension(file.name);
        if (!this.options.allowedTypes.includes(extension)) {
            errors.push(`Invalid file type: ${extension}. Allowed: ${this.options.allowedTypes.join(', ')}`);
        }
        
        // Check filename
        if (!this.isValidFilename(file.name)) {
            errors.push('Invalid filename. Please use only letters, numbers, dots, hyphens and underscores');
        }
        
        return {
            valid: errors.length === 0,
            errors: errors
        };
    }
    
    async performSecurityScan(file) {
        return new Promise((resolve) => {
            // Simulate security scanning
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 30;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                    
                    // Basic client-side checks
                    const fileReader = new FileReader();
                    fileReader.onload = (e) => {
                        const buffer = e.target.result;
                        const scanResult = this.performBasicScan(buffer);
                        resolve(scanResult);
                    };
                    
                    // Read first 8KB for basic scanning
                    fileReader.readAsArrayBuffer(file.slice(0, 8192));
                }
                
                this.showProgress('Scanning file for security threats...', Math.min(progress, 100));
            }, 200);
        });
    }
    
    performBasicScan(buffer) {
        const view = new Uint8Array(buffer);
        
        // Check for suspicious patterns (basic)
        const suspiciousPatterns = [
            [0x3C, 0x3F, 0x70, 0x68, 0x70], // <?php
            [0x3C, 0x73, 0x63, 0x72, 0x69, 0x70, 0x74], // <script
            [0x65, 0x76, 0x61, 0x6C, 0x28], // eval(
        ];
        
        for (const pattern of suspiciousPatterns) {
            if (this.containsPattern(view, pattern)) {
                return {
                    safe: false,
                    message: 'File contains potentially malicious content'
                };
            }
        }
        
        return { safe: true };
    }
    
    containsPattern(buffer, pattern) {
        for (let i = 0; i <= buffer.length - pattern.length; i++) {
            let match = true;
            for (let j = 0; j < pattern.length; j++) {
                if (buffer[i + j] !== pattern[j]) {
                    match = false;
                    break;
                }
            }
            if (match) return true;
        }
        return false;
    }
    
    async startUpload(file) {
        this.isUploading = true;
        this.retryCount = 0;
        
        try {
            await this.uploadWithRetry(file);
        } catch (error) {
            this.showError(['Upload failed: ' + error.message]);
            this.hideProgress();
        } finally {
            this.isUploading = false;
        }
    }
    
    async uploadWithRetry(file) {
        for (let attempt = 0; attempt <= this.options.maxRetries; attempt++) {
            try {
                if (attempt > 0) {
                    this.showProgress(`Retrying upload (${attempt}/${this.options.maxRetries})...`, 0);
                    await this.delay(this.options.retryDelay * attempt);
                }
                
                await this.performUpload(file);
                return; // Success
                
            } catch (error) {
                console.warn(`Upload attempt ${attempt + 1} failed:`, error);
                
                if (attempt === this.options.maxRetries) {
                    throw error; // Final attempt failed
                }
            }
        }
    }
    
    async performUpload(file) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('app_file', file);
            
            // Add other form data if available
            const form = document.getElementById('appUploadForm');
            if (form) {
                const formInputs = form.querySelectorAll('input:not([type="file"]), textarea, select');
                formInputs.forEach(input => {
                    if (input.name && input.value) {
                        formData.append(input.name, input.value);
                    }
                });
            }
            
            const xhr = new XMLHttpRequest();
            
            // Upload progress
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    this.showProgress(`Uploading... ${this.formatFileSize(e.loaded)} of ${this.formatFileSize(e.total)}`, percentComplete);
                }
            });
            
            // Upload completion
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            this.showSuccess('File uploaded successfully!');
                            resolve(response);
                        } else {
                            reject(new Error(response.message || 'Upload failed'));
                        }
                    } catch (e) {
                        // Non-JSON response, assume success if status 200
                        this.showSuccess('File uploaded successfully!');
                        resolve({ success: true });
                    }
                } else {
                    reject(new Error(`Upload failed with status ${xhr.status}`));
                }
            });
            
            // Upload error
            xhr.addEventListener('error', () => {
                reject(new Error('Network error during upload'));
            });
            
            // Upload timeout
            xhr.addEventListener('timeout', () => {
                reject(new Error('Upload timeout - please try again'));
            });
            
            // Set timeout (10 minutes)
            xhr.timeout = 600000;
            
            // Start upload
            xhr.open('POST', form?.action || '/apps/store');
            xhr.send(formData);
        });
    }
    
    displayFileInfo(file) {
        const fileInfo = document.getElementById('appFileInfo');
        const fileName = document.getElementById('appFileName');
        const fileSize = document.getElementById('appFileSize');
        
        if (fileInfo && fileName && fileSize) {
            fileName.textContent = file.name;
            fileSize.textContent = `(${this.formatFileSize(file.size)})`;
            fileInfo.style.display = 'block';
        }
    }
    
    createProgressElements() {
        if (!this.dropZone) return;
        
        const progressHtml = `
            <div id="uploadProgress" style="display: none; margin-top: 1rem;">
                <div style="background: #f3f4f6; border-radius: 0.5rem; padding: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span id="progressText">Preparing upload...</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div style="background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                        <div id="progressBar" style="background: linear-gradient(90deg, #3b82f6, #1d4ed8); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                </div>
            </div>
            
            <div id="uploadErrors" style="display: none; margin-top: 1rem;">
                <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: 0.5rem;">
                    <div style="font-weight: 600; margin-bottom: 0.5rem;">❌ Upload Error</div>
                    <ul id="errorList" style="margin: 0; padding-left: 1.5rem;"></ul>
                </div>
            </div>
            
            <div id="uploadSuccess" style="display: none; margin-top: 1rem;">
                <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 0.5rem;">
                    <div style="font-weight: 600;">✅ <span id="successMessage">Upload completed successfully!</span></div>
                </div>
            </div>
        `;
        
        this.dropZone.insertAdjacentHTML('afterend', progressHtml);
        
        // Update element references
        this.progressContainer = document.getElementById('uploadProgress');
        this.errorContainer = document.getElementById('uploadErrors');
        this.successContainer = document.getElementById('uploadSuccess');
    }
    
    showProgress(text, percent) {
        if (!this.progressContainer) return;
        
        const progressText = document.getElementById('progressText');
        const progressPercent = document.getElementById('progressPercent');
        const progressBar = document.getElementById('progressBar');
        
        if (progressText) progressText.textContent = text;
        if (progressPercent) progressPercent.textContent = `${Math.round(percent)}%`;
        if (progressBar) progressBar.style.width = `${percent}%`;
        
        this.progressContainer.style.display = 'block';
        this.hideError();
        this.hideSuccess();
    }
    
    hideProgress() {
        if (this.progressContainer) {
            this.progressContainer.style.display = 'none';
        }
    }
    
    showError(errors) {
        if (!this.errorContainer) return;
        
        const errorList = document.getElementById('errorList');
        if (errorList) {
            errorList.innerHTML = errors.map(error => `<li>${error}</li>`).join('');
        }
        
        this.errorContainer.style.display = 'block';
        this.hideProgress();
        this.hideSuccess();
    }
    
    hideError() {
        if (this.errorContainer) {
            this.errorContainer.style.display = 'none';
        }
    }
    
    showSuccess(message) {
        if (!this.successContainer) return;
        
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.textContent = message;
        }
        
        this.successContainer.style.display = 'block';
        this.hideProgress();
        this.hideError();
    }
    
    hideSuccess() {
        if (this.successContainer) {
            this.successContainer.style.display = 'none';
        }
    }
    
    clearErrors() {
        this.hideError();
        this.hideSuccess();
    }
    
    // Utility methods
    getFileExtension(filename) {
        const parts = filename.toLowerCase().split('.');
        if (parts.length < 2) return '';
        
        // Handle compound extensions
        if (parts.length >= 3 && parts[parts.length - 2] === 'tar') {
            return parts.slice(-2).join('.');
        }
        
        return parts[parts.length - 1];
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    isValidFilename(filename) {
        // Allow letters, numbers, dots, hyphens, underscores, spaces
        const validPattern = /^[a-zA-Z0-9._\-\s]+$/;
        return validPattern.test(filename) && filename.length <= 255;
    }
    
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    // Public methods
    reset() {
        this.currentFile = null;
        this.uploadProgress = 0;
        this.isUploading = false;
        this.retryCount = 0;
        
        this.hideProgress();
        this.hideError();
        this.hideSuccess();
        
        if (this.fileInput) {
            this.fileInput.value = '';
        }
        
        const fileInfo = document.getElementById('appFileInfo');
        if (fileInfo) {
            fileInfo.style.display = 'none';
        }
    }
    
    getUploadStatus() {
        return {
            isUploading: this.isUploading,
            progress: this.uploadProgress,
            currentFile: this.currentFile?.name,
            retryCount: this.retryCount
        };
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize on pages with file upload
    if (document.getElementById('appFileDropZone')) {
        window.fileUpload = new SecureFileUpload({
            dropZoneId: 'appFileDropZone',
            fileInputId: 'app_file',
            maxFileSize: 52428800, // 50MB
            allowedTypes: ['zip', 'rar', 'tar.gz', '7z']
        });
    }
});

// Global functions for backward compatibility
function removeAppFile() {
    if (window.fileUpload) {
        window.fileUpload.reset();
    }
}