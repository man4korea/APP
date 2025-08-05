<?php
// C:\xampp\htdocs\AppMart\src\views\app\create.php
// Create at 2508051030 Ver1.00
$content = ob_start();
?>

<section style="padding: 2rem 0; background: #f8fafc;">
    <div class="container">
        <!-- Page Header -->
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <a href="<?php echo url('/dashboard'); ?>" style="color: #6b7280; text-decoration: none; font-size: 1.2rem;">←</a>
                <h1 style="font-size: 2rem; font-weight: bold; color: #1f2937; margin: 0;">Upload New Application</h1>
            </div>
            <p style="color: #6b7280; margin: 0;">Share your application with the AppMart community</p>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['upload_success'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid #bbf7d0;">
                <?php echo htmlspecialchars($_SESSION['upload_success']); ?>
                <?php unset($_SESSION['upload_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['upload_errors'])): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; border: 1px solid #fecaca;">
                <ul style="margin: 0; padding-left: 1rem;">
                    <?php foreach ($_SESSION['upload_errors'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php unset($_SESSION['upload_errors']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo url('/apps/store'); ?>" enctype="multipart/form-data" id="appUploadForm">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- Main Form -->
                <div>
                    <!-- Basic Information -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3>📝 Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="title" class="form-label">Application Name *</label>
                                <input 
                                    type="text" 
                                    id="title" 
                                    name="title" 
                                    class="form-input" 
                                    required
                                    placeholder="Enter your application name"
                                    value="<?php echo htmlspecialchars($old['title'] ?? ''); ?>"
                                    maxlength="100"
                                >
                                <small style="color: #6b7280; font-size: 0.8rem;">This will be displayed in the marketplace</small>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="category_id" class="form-label">Category *</label>
                                <select id="category_id" name="category_id" class="form-input" required>
                                    <option value="">Select a category</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($old['category_id']) && $old['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="short_description" class="form-label">Short Description *</label>
                                <textarea 
                                    id="short_description" 
                                    name="short_description" 
                                    class="form-input" 
                                    required
                                    rows="2"
                                    placeholder="Brief description (max 200 characters)"
                                    maxlength="200"
                                    style="resize: vertical;"
                                ><?php echo htmlspecialchars($old['short_description'] ?? ''); ?></textarea>
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #6b7280; margin-top: 0.25rem;">
                                    <span>This appears in search results and app listings</span>
                                    <span id="shortDescCounter">0/200</span>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="description" class="form-label">Full Description *</label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    class="form-input" 
                                    required
                                    rows="8"
                                    placeholder="Detailed description of your application, features, how to use it, etc."
                                    style="resize: vertical; min-height: 200px;"
                                ><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>
                                <small style="color: #6b7280; font-size: 0.8rem;">You can use line breaks for formatting</small>
                            </div>

                            <div class="grid grid-cols-2" style="gap: 1rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="version" class="form-label">Version *</label>
                                    <input 
                                        type="text" 
                                        id="version" 
                                        name="version" 
                                        class="form-input" 
                                        required
                                        placeholder="1.0.0"
                                        value="<?php echo htmlspecialchars($old['version'] ?? '1.0.0'); ?>"
                                        pattern="[0-9]+\.[0-9]+\.[0-9]+"
                                        title="Version format: x.y.z (e.g., 1.0.0)"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="price" class="form-label">Price (USD) *</label>
                                    <div style="position: relative;">
                                        <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #6b7280;">$</span>
                                        <input 
                                            type="number" 
                                            id="price" 
                                            name="price" 
                                            class="form-input" 
                                            required
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            style="padding-left: 1.75rem;"
                                            value="<?php echo htmlspecialchars($old['price'] ?? '0.00'); ?>"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="tags" class="form-label">Tags</label>
                                <input 
                                    type="text" 
                                    id="tags" 
                                    name="tags" 
                                    class="form-input" 
                                    placeholder="react, javascript, api, tool (comma separated)"
                                    value="<?php echo htmlspecialchars($old['tags'] ?? ''); ?>"
                                >
                                <small style="color: #6b7280; font-size: 0.8rem;">Separate multiple tags with commas. Max 10 tags.</small>
                            </div>
                        </div>
                    </div>

                    <!-- File Uploads -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3>📁 Files & Media</h3>
                        </div>
                        <div class="card-body">
                            <!-- Application File -->
                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label for="app_file" class="form-label">Application File *</label>
                                <div 
                                    id="appFileDropZone" 
                                    style="border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#f0f9ff';"
                                    onmouseout="this.style.borderColor='#d1d5db'; this.style.background='transparent';"
                                >
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📦</div>
                                    <p style="margin-bottom: 0.5rem; color: #374151; font-weight: 500;">
                                        Drop your application file here or click to browse
                                    </p>
                                    <p style="font-size: 0.8rem; color: #6b7280; margin: 0;">
                                        Supported: .zip, .tar.gz, .rar (Max 50MB)
                                    </p>
                                    <input 
                                        type="file" 
                                        id="app_file" 
                                        name="app_file" 
                                        accept=".zip,.tar.gz,.rar"
                                        required
                                        style="display: none;"
                                    >
                                </div>
                                <div id="appFileInfo" style="margin-top: 0.5rem; display: none;">
                                    <div style="background: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 0.375rem; padding: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="color: #2563eb;">📎</span>
                                        <span id="appFileName" style="font-weight: 500; color: #1e40af;"></span>
                                        <span id="appFileSize" style="color: #6b7280; font-size: 0.8rem;"></span>
                                        <button type="button" onclick="removeAppFile()" style="margin-left: auto; color: #ef4444; background: none; border: none; cursor: pointer;">✕</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Thumbnail -->
                            <div class="form-group" style="margin-bottom: 2rem;">
                                <label for="thumbnail" class="form-label">Application Icon *</label>
                                <div 
                                    id="thumbnailDropZone" 
                                    style="border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#f0f9ff';"
                                    onmouseout="this.style.borderColor='#d1d5db'; this.style.background='transparent';"
                                >
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🖼️</div>
                                    <p style="margin-bottom: 0.5rem; color: #374151; font-weight: 500;">
                                        Drop your app icon here or click to browse
                                    </p>
                                    <p style="font-size: 0.8rem; color: #6b7280; margin: 0;">
                                        PNG, JPG, GIF (Max 2MB, recommended 512x512px)
                                    </p>
                                    <input 
                                        type="file" 
                                        id="thumbnail" 
                                        name="thumbnail" 
                                        accept="image/*"
                                        required
                                        style="display: none;"
                                    >
                                </div>
                                <div id="thumbnailPreview" style="margin-top: 1rem; display: none;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <img id="thumbnailImage" src="" alt="Thumbnail preview" style="width: 80px; height: 80px; object-fit: cover; border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                                        <div>
                                            <div style="font-weight: 500; color: #374151;" id="thumbnailName"></div>
                                            <div style="font-size: 0.8rem; color: #6b7280;" id="thumbnailSize"></div>
                                            <button type="button" onclick="removeThumbnail()" class="btn btn-outline" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; margin-top: 0.25rem;">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Screenshots -->
                            <div class="form-group">
                                <label for="screenshots" class="form-label">Screenshots (Optional)</label>
                                <div 
                                    id="screenshotsDropZone" 
                                    style="border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#f0f9ff';"
                                    onmouseout="this.style.borderColor='#d1d5db'; this.style.background='transparent';"
                                >
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📸</div>
                                    <p style="margin-bottom: 0.5rem; color: #374151; font-weight: 500;">
                                        Drop screenshot images here or click to browse
                                    </p>
                                    <p style="font-size: 0.8rem; color: #6b7280; margin: 0;">
                                        PNG, JPG (Max 2MB each, up to 5 images)
                                    </p>
                                    <input 
                                        type="file" 
                                        id="screenshots" 
                                        name="screenshots[]" 
                                        accept="image/*"
                                        multiple
                                        style="display: none;"
                                    >
                                </div>
                                <div id="screenshotsPreview" style="margin-top: 1rem; display: none;">
                                    <!-- Screenshots will be previewed here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Details -->
                    <div class="card">
                        <div class="card-header">
                            <h3>⚙️ Technical Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2" style="gap: 1rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="demo_url" class="form-label">Demo URL</label>
                                    <input 
                                        type="url" 
                                        id="demo_url" 
                                        name="demo_url" 
                                        class="form-input" 
                                        placeholder="https://demo.yourapp.com"
                                        value="<?php echo htmlspecialchars($old['demo_url'] ?? ''); ?>"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="github_url" class="form-label">GitHub Repository</label>
                                    <input 
                                        type="url" 
                                        id="github_url" 
                                        name="github_url" 
                                        class="form-input" 
                                        placeholder="https://github.com/username/repository"
                                        value="<?php echo htmlspecialchars($old['github_url'] ?? ''); ?>"
                                    >
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label for="requirements" class="form-label">System Requirements</label>
                                <textarea 
                                    id="requirements" 
                                    name="requirements" 
                                    class="form-input" 
                                    rows="4"
                                    placeholder="List any system requirements, dependencies, or installation instructions..."
                                    style="resize: vertical;"
                                ><?php echo htmlspecialchars($old['requirements'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">License & Terms</label>
                                <div style="background: #f8fafc; padding: 1rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                                    <label style="display: flex; align-items: start; gap: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" name="accept_terms" required style="margin-top: 0.125rem;">
                                        <span style="font-size: 0.9rem; line-height: 1.4;">
                                            I confirm that I own the rights to this application and agree to the 
                                            <a href="/terms" target="_blank" style="color: #3b82f6; text-decoration: underline;">Terms of Service</a> 
                                            and 
                                            <a href="/developer-agreement" target="_blank" style="color: #3b82f6; text-decoration: underline;">Developer Agreement</a>.
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div>
                    <!-- Upload Progress -->
                    <div class="card" style="margin-bottom: 2rem; display: none;" id="uploadProgress">
                        <div class="card-header">
                            <h3>📤 Upload Progress</h3>
                        </div>
                        <div class="card-body">
                            <div style="background: #f3f4f6; height: 20px; border-radius: 10px; overflow: hidden; margin-bottom: 1rem;">
                                <div id="progressBar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #3b82f6, #10b981); transition: width 0.3s;"></div>
                            </div>
                            <div id="progressText" style="text-align: center; font-size: 0.9rem; color: #6b7280;">Preparing upload...</div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3>🚀 Publish Application</h3>
                        </div>
                        <div class="card-body">
                            <div style="margin-bottom: 1.5rem;">
                                <label class="form-label">Publish Status</label>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="status" value="draft" checked>
                                        <span style="font-size: 0.9rem;">📝 Save as Draft</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                        <input type="radio" name="status" value="pending">
                                        <span style="font-size: 0.9rem;">📋 Submit for Review</span>
                                    </label>
                                </div>
                                <small style="color: #6b7280; font-size: 0.8rem; display: block; margin-top: 0.5rem;">
                                    Drafts can be edited later. Submitted apps will be reviewed before publication.
                                </small>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%;">
                                    📤 Upload Application
                                </button>
                                <a href="<?php echo url('/dashboard'); ?>" class="btn btn-outline" style="width: 100%; text-align: center;">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div class="card">
                        <div class="card-header">
                            <h3>📋 Guidelines</h3>
                        </div>
                        <div class="card-body">
                            <div style="font-size: 0.8rem; line-height: 1.4; color: #6b7280;">
                                <div style="margin-bottom: 1rem;">
                                    <strong style="color: #374151;">Before uploading:</strong>
                                    <ul style="margin: 0.5rem 0; padding-left: 1rem;">
                                        <li>Test your application thoroughly</li>
                                        <li>Ensure files are virus-free</li>
                                        <li>Include clear documentation</li>
                                        <li>Use high-quality screenshots</li>
                                    </ul>
                                </div>
                                
                                <div style="margin-bottom: 1rem;">
                                    <strong style="color: #374151;">Review process:</strong>
                                    <ul style="margin: 0.5rem 0; padding-left: 1rem;">
                                        <li>Apps are reviewed within 3-5 days</li>
                                        <li>You'll be notified of the status</li>
                                        <li>Feedback will be provided if rejected</li>
                                    </ul>
                                </div>

                                <div>
                                    <a href="/guidelines" target="_blank" style="color: #3b82f6; text-decoration: underline;">
                                        View Full Guidelines →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
// Form validation and file handling
document.addEventListener('DOMContentLoaded', function() {
    // Character counters
    const shortDesc = document.getElementById('short_description');
    const shortDescCounter = document.getElementById('shortDescCounter');
    
    function updateCounter() {
        const count = shortDesc.value.length;
        shortDescCounter.textContent = count + '/200';
        if (count > 180) {
            shortDescCounter.style.color = '#ef4444';
        } else {
            shortDescCounter.style.color = '#6b7280';
        }
    }
    
    shortDesc.addEventListener('input', updateCounter);
    updateCounter();

    // File upload handlers
    setupFileUpload('appFileDropZone', 'app_file', 'appFileInfo', handleAppFile);
    setupFileUpload('thumbnailDropZone', 'thumbnail', 'thumbnailPreview', handleThumbnail);
    setupFileUpload('screenshotsDropZone', 'screenshots', 'screenshotsPreview', handleScreenshots);

    // Form submission
    document.getElementById('appUploadForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const progressCard = document.getElementById('uploadProgress');
        
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Uploading...';
        progressCard.style.display = 'block';
        
        // Simulate progress (in real implementation, use XMLHttpRequest for actual progress)
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress > 95) progress = 95;
            
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('progressText').textContent = 'Uploading... ' + Math.round(progress) + '%';
            
            if (progress >= 95) {
                clearInterval(interval);
                document.getElementById('progressText').textContent = 'Processing...';
            }
        }, 200);
    });
});

function setupFileUpload(dropZoneId, inputId, previewId, handler) {
    const dropZone = document.getElementById(dropZoneId);
    const input = document.getElementById(inputId);
    
    dropZone.addEventListener('click', () => input.click());
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#3b82f6';
        dropZone.style.background = '#f0f9ff';
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.borderColor = '#d1d5db';
        dropZone.style.background = 'transparent';
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#d1d5db';
        dropZone.style.background = 'transparent';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            input.files = files;
            handler(files);
        }
    });
    
    input.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handler(e.target.files);
        }
    });
}

function handleAppFile(files) {
    const file = files[0];
    const info = document.getElementById('appFileInfo');
    const nameSpan = document.getElementById('appFileName');
    const sizeSpan = document.getElementById('appFileSize');
    
    nameSpan.textContent = file.name;
    sizeSpan.textContent = formatFileSize(file.size);
    info.style.display = 'block';
}

function handleThumbnail(files) {
    const file = files[0];
    const preview = document.getElementById('thumbnailPreview');
    const img = document.getElementById('thumbnailImage');
    const nameSpan = document.getElementById('thumbnailName');
    const sizeSpan = document.getElementById('thumbnailSize');
    
    const reader = new FileReader();
    reader.onload = function(e) {
        img.src = e.target.result;
        nameSpan.textContent = file.name;
        sizeSpan.textContent = formatFileSize(file.size);
        preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function handleScreenshots(files) {
    const preview = document.getElementById('screenshotsPreview');
    preview.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.style.cssText = 'display: inline-block; margin: 0.5rem; position: relative;';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Screenshot ${index + 1}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 0.5rem; border: 2px solid #e5e7eb;">
                <button type="button" onclick="this.parentElement.remove()" style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 12px; cursor: pointer;">×</button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
    
    if (files.length > 0) {
        preview.style.display = 'block';
    }
}

function removeAppFile() {
    document.getElementById('app_file').value = '';
    document.getElementById('appFileInfo').style.display = 'none';
}

function removeThumbnail() {
    document.getElementById('thumbnail').value = '';
    document.getElementById('thumbnailPreview').style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>