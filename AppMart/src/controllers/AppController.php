<?php
/**
 * AppMart Application Controller
 * C:\xampp\htdocs\AppMart\src\controllers\AppController.php
 * Create at 2508041600 Ver1.00
 */

namespace controllers;

require_once __DIR__ . '/AuthController.php';

class AppController {
    
    // List all applications
    public function index() {
        global $pdo;
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = config('app.pagination.per_page', 12);
        $offset = ($page - 1) * $perPage;
        
        $search = trim($_GET['search'] ?? '');
        $category = (int)($_GET['category'] ?? 0);
        $priceFilter = $_GET['price'] ?? 'all'; // all, free, paid
        $sortBy = $_GET['sort'] ?? 'popular'; // popular, newest, price_low, price_high, rating
        
        // Build WHERE clause
        $whereConditions = ["a.status = 'approved'"];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(a.title LIKE ? OR a.description LIKE ? OR a.short_description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($category > 0) {
            $whereConditions[] = "a.category_id = ?";
            $params[] = $category;
        }
        
        if ($priceFilter === 'free') {
            $whereConditions[] = "a.price = 0";
        } elseif ($priceFilter === 'paid') {
            $whereConditions[] = "a.price > 0";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Build ORDER BY clause
        $orderBy = match($sortBy) {
            'newest' => 'a.published_at DESC',
            'price_low' => 'a.price ASC',
            'price_high' => 'a.price DESC',
            'rating' => 'a.rating_average DESC, a.rating_count DESC',
            'popular' => 'a.download_count DESC',
            default => 'a.download_count DESC'
        };
        
        try {
            // Get total count
            $countQuery = "
                SELECT COUNT(*) 
                FROM applications a 
                WHERE {$whereClause}
            ";
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalApps = $countStmt->fetchColumn();
            
            // Get applications
            $appsQuery = "
                SELECT a.*, u.username as owner_username, c.name as category_name, c.slug as category_slug
                FROM applications a 
                LEFT JOIN users u ON a.owner_id = u.id 
                LEFT JOIN categories c ON a.category_id = c.id 
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}
            ";
            $appsStmt = $pdo->prepare($appsQuery);
            $appsStmt->execute($params);
            $apps = $appsStmt->fetchAll();
            
            // Get categories for filter
            $categoriesStmt = $pdo->prepare("
                SELECT c.*, COUNT(a.id) as app_count 
                FROM categories c 
                LEFT JOIN applications a ON c.id = a.category_id AND a.status = 'approved'
                WHERE c.is_active = 1 
                GROUP BY c.id 
                ORDER BY c.sort_order ASC
            ");
            $categoriesStmt->execute();
            $categories = $categoriesStmt->fetchAll();
            
            $totalPages = ceil($totalApps / $perPage);
            
            echo view('app/index', [
                'title' => 'Browse Apps - AppMart',
                'apps' => $apps,
                'categories' => $categories,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_items' => $totalApps,
                    'per_page' => $perPage
                ],
                'filters' => [
                    'search' => $search,
                    'category' => $category,
                    'price' => $priceFilter,
                    'sort' => $sortBy
                ]
            ]);
            
        } catch (Exception $e) {
            if (config('app.debug')) {
                echo "<h1>Database Error</h1><p>" . $e->getMessage() . "</p>";
            } else {
                echo view('layouts/error', [
                    'title' => 'Service Unavailable',
                    'message' => 'Unable to load applications. Please try again later.',
                    'code' => 503
                ]);
            }
        }
    }
    
    // Show single application
    public function show() {
        global $pdo;
        
        $appId = (int)($_GET['id'] ?? 0);
        $slug = $_GET['slug'] ?? '';
        
        if (!$appId && !$slug) {
            http_response_code(404);
            echo view('layouts/error', [
                'title' => '404 - App Not Found',
                'message' => 'The requested application could not be found.',
                'code' => 404
            ]);
            return;
        }
        
        try {
            // Get application details
            $whereClause = $appId ? "a.id = ?" : "a.slug = ?";
            $param = $appId ?: $slug;
            
            $appQuery = "
                SELECT a.*, 
                       u.username as owner_username, 
                       u.first_name as owner_first_name,
                       u.last_name as owner_last_name,
                       u.bio as owner_bio,
                       u.website as owner_website,
                       u.github_username as owner_github,
                       u.created_at as owner_created_at,
                       c.name as category_name,
                       c.slug as category_slug
                FROM applications a 
                LEFT JOIN users u ON a.owner_id = u.id 
                LEFT JOIN categories c ON a.category_id = c.id 
                WHERE {$whereClause} AND a.status = 'approved'
            ";
            
            $appStmt = $pdo->prepare($appQuery);
            $appStmt->execute([$param]);
            $app = $appStmt->fetch();
            
            if (!$app) {
                http_response_code(404);
                echo view('layouts/error', [
                    'title' => '404 - App Not Found',
                    'message' => 'The requested application could not be found.',
                    'code' => 404
                ]);
                return;
            }
            
            // Get reviews
            $reviewsQuery = "
                SELECT r.*, u.username, u.first_name, u.last_name
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.application_id = ? AND r.status = 'approved'
                ORDER BY r.created_at DESC
                LIMIT 10
            ";
            $reviewsStmt = $pdo->prepare($reviewsQuery);
            $reviewsStmt->execute([$app['id']]);
            $reviews = $reviewsStmt->fetchAll();
            
            // Get related apps
            $relatedQuery = "
                SELECT a.*, u.username as owner_username, c.name as category_name
                FROM applications a 
                LEFT JOIN users u ON a.owner_id = u.id 
                LEFT JOIN categories c ON a.category_id = c.id 
                WHERE a.category_id = ? AND a.id != ? AND a.status = 'approved'
                ORDER BY a.download_count DESC
                LIMIT 6
            ";
            $relatedStmt = $pdo->prepare($relatedQuery);
            $relatedStmt->execute([$app['category_id'], $app['id']]);
            $relatedApps = $relatedStmt->fetchAll();
            
            // Check if user has purchased this app
            $hasPurchased = false;
            if (AuthController::isAuthenticated()) {
                $purchaseStmt = $pdo->prepare("
                    SELECT COUNT(*) FROM purchases 
                    WHERE user_id = ? AND application_id = ? AND status = 'completed'
                ");
                $purchaseStmt->execute([$_SESSION['user_id'], $app['id']]);
                $hasPurchased = $purchaseStmt->fetchColumn() > 0;
            }
            
            // Decode JSON fields
            $app['tech_stack'] = json_decode($app['tech_stack'] ?? '[]', true);
            $app['screenshots'] = json_decode($app['screenshots'] ?? '[]', true);
            $app['tags'] = json_decode($app['tags'] ?? '[]', true);
            
            echo view('app/show', [
                'title' => $app['title'] . ' - AppMart',
                'app' => $app,
                'reviews' => $reviews,
                'related_apps' => $relatedApps,
                'has_purchased' => $hasPurchased
            ]);
            
        } catch (Exception $e) {
            if (config('app.debug')) {
                echo "<h1>Database Error</h1><p>" . $e->getMessage() . "</p>";
            } else {
                echo view('layouts/error', [
                    'title' => 'Service Unavailable',
                    'message' => 'Unable to load application details. Please try again later.',
                    'code' => 503
                ]);
            }
        }
    }
    
    // Show create app form (developers only)
    public function create() {
        AuthController::requireAuth();
        
        $user = AuthController::getUser();
        if ($user['role'] !== 'developer' && $user['role'] !== 'admin') {
            http_response_code(403);
            echo view('layouts/error', [
                'title' => '403 - Access Denied',
                'message' => 'Only developers can upload applications.',
                'code' => 403
            ]);
            return;
        }
        
        global $pdo;
        
        // Get categories
        $categoriesStmt = $pdo->prepare("
            SELECT * FROM categories 
            WHERE is_active = 1 
            ORDER BY parent_id ASC, sort_order ASC
        ");
        $categoriesStmt->execute();
        $categories = $categoriesStmt->fetchAll();
        
        echo view('app/create', [
            'title' => 'Upload New App - AppMart',
            'categories' => $categories
        ]);
    }
    
    // Store new application
    public function store() {
        AuthController::requireAuth();
        
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/apps/create');
            return;
        }
        
        $user = AuthController::getUser();
        if ($user['role'] !== 'developer' && $user['role'] !== 'admin') {
            http_response_code(403);
            echo "Access denied";
            return;
        }
        
        // Validate and process form data
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $version = trim($_POST['version'] ?? '1.0.0');
        $databaseType = trim($_POST['database_type'] ?? '');
        $demoUrl = trim($_POST['demo_url'] ?? '');
        $githubUrl = trim($_POST['github_url'] ?? '');
        $techStack = array_filter(array_map('trim', explode(',', $_POST['tech_stack'] ?? '')));
        $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
        
        // Validation
        $errors = [];
        
        if (empty($title)) {
            $errors[] = 'Title is required';
        }
        
        if (empty($description)) {
            $errors[] = 'Description is required';
        }
        
        if ($categoryId <= 0) {
            $errors[] = 'Category is required';
        }
        
        if ($price < 0) {
            $errors[] = 'Price cannot be negative';
        }
        
        // File upload validation
        if (!isset($_FILES['app_file']) || $_FILES['app_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Application file is required';
        } else {
            $file = $_FILES['app_file'];
            $allowedTypes = config('app.allowed_file_types');
            $maxSize = config('app.max_upload_size');
            
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedTypes)) {
                $errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes);
            }
            
            if ($file['size'] > $maxSize) {
                $errors[] = 'File size exceeds maximum limit of ' . ($maxSize / 1024 / 1024) . 'MB';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['app_errors'] = $errors;
            $_SESSION['app_old_input'] = $_POST;
            redirect('/apps/create');
            return;
        }
        
        try {
            // Generate unique filename
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
            $slug = trim($slug, '-');
            
            // Check slug uniqueness
            $slugStmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE slug = ?");
            $slugStmt->execute([$slug]);
            if ($slugStmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
            
            // Handle file upload
            $uploadDir = config('app.upload_path') . 'apps/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = $slug . '-v' . $version . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Failed to upload file');
            }
            
            // Calculate file hash
            $fileHash = hash_file('sha256', $filePath);
            
            // Insert application
            $insertStmt = $pdo->prepare("
                INSERT INTO applications (
                    title, slug, description, short_description, version, 
                    tech_stack, database_type, demo_url, github_url,
                    file_path, file_size, file_hash,
                    price, status, owner_id, category_id, tags,
                    created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            
            $insertStmt->execute([
                $title,
                $slug,
                $description,
                $shortDescription,
                $version,
                json_encode($techStack),
                $databaseType,
                $demoUrl,
                $githubUrl,
                '/uploads/apps/' . $fileName,
                $file['size'],
                $fileHash,
                $price,
                'pending', // Requires admin approval
                $user['id'],
                $categoryId,
                json_encode($tags)
            ]);
            
            $_SESSION['app_success'] = 'Application uploaded successfully! It will be reviewed by our team.';
            redirect('/dashboard');
            
        } catch (Exception $e) {
            // Clean up uploaded file if database insert fails
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
            
            if (config('app.debug')) {
                $_SESSION['app_errors'] = ['Database error: ' . $e->getMessage()];
            } else {
                $_SESSION['app_errors'] = ['Upload failed. Please try again.'];
            }
            redirect('/apps/create');
        }
    }
    
    // Download application (for purchased apps)
    public function download() {
        AuthController::requireAuth();
        
        global $pdo;
        
        $appId = (int)($_GET['id'] ?? 0);
        $user = AuthController::getUser();
        
        if (!$appId) {
            http_response_code(404);
            echo "Application not found";
            return;
        }
        
        try {
            // Check if user has purchased the app or if it's free
            $appStmt = $pdo->prepare("
                SELECT a.*, p.id as purchase_id
                FROM applications a
                LEFT JOIN purchases p ON (a.id = p.application_id AND p.user_id = ? AND p.status = 'completed')
                WHERE a.id = ? AND a.status = 'approved'
            ");
            $appStmt->execute([$user['id'], $appId]);
            $app = $appStmt->fetch();
            
            if (!$app) {
                http_response_code(404);
                echo "Application not found";
                return;
            }
            
            // Check if user can download (free app or purchased)
            if ($app['price'] > 0 && !$app['purchase_id']) {
                http_response_code(403);
                echo "You must purchase this application first";
                return;
            }
            
            // Increment download count
            $updateStmt = $pdo->prepare("UPDATE applications SET download_count = download_count + 1 WHERE id = ?");
            $updateStmt->execute([$appId]);
            
            // Update purchase download tracking
            if ($app['purchase_id']) {
                $purchaseUpdateStmt = $pdo->prepare("
                    UPDATE purchases 
                    SET download_count = download_count + 1, last_download_at = NOW() 
                    WHERE id = ?
                ");
                $purchaseUpdateStmt->execute([$app['purchase_id']]);
            }
            
            // Serve file download
            $filePath = __DIR__ . '/../../' . ltrim($app['file_path'], '/');
            
            if (!file_exists($filePath)) {
                http_response_code(404);
                echo "File not found";
                return;
            }
            
            // Set headers for download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($app['file_path']) . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache, must-revalidate');
            
            // Output file
            readfile($filePath);
            
        } catch (Exception $e) {
            http_response_code(500);
            if (config('app.debug')) {
                echo "Error: " . $e->getMessage();
            } else {
                echo "Download failed. Please try again.";
            }
        }
    }
}