<?php
/**
 * AppMart Home Controller
 * C:\xampp\htdocs\AppMart\src\controllers\HomeController.php
 * Create at 2508041600 Ver1.00
 */

namespace controllers;

class HomeController {
    
    public function index() {
        global $pdo;
        
        try {
            // Get featured applications
            $featuredQuery = "
                SELECT a.*, u.username as owner_username, c.name as category_name 
                FROM applications a 
                LEFT JOIN users u ON a.owner_id = u.id 
                LEFT JOIN categories c ON a.category_id = c.id 
                WHERE a.status = 'approved' AND a.featured = 1 
                ORDER BY a.download_count DESC 
                LIMIT 6
            ";
            $featuredStmt = $pdo->prepare($featuredQuery);
            $featuredStmt->execute();
            $featuredApps = $featuredStmt->fetchAll();
            
            // Get recent applications
            $recentQuery = "
                SELECT a.*, u.username as owner_username, c.name as category_name 
                FROM applications a 
                LEFT JOIN users u ON a.owner_id = u.id 
                LEFT JOIN categories c ON a.category_id = c.id 
                WHERE a.status = 'approved' 
                ORDER BY a.published_at DESC 
                LIMIT 8
            ";
            $recentStmt = $pdo->prepare($recentQuery);
            $recentStmt->execute();
            $recentApps = $recentStmt->fetchAll();
            
            // Get popular categories
            $categoriesQuery = "
                SELECT c.*, COUNT(a.id) as app_count 
                FROM categories c 
                LEFT JOIN applications a ON c.id = a.category_id AND a.status = 'approved'
                WHERE c.parent_id IS NULL AND c.is_active = 1
                GROUP BY c.id 
                ORDER BY c.sort_order ASC 
                LIMIT 8
            ";
            $categoriesStmt = $pdo->prepare($categoriesQuery);
            $categoriesStmt->execute();
            $categories = $categoriesStmt->fetchAll();
            
            // Get platform statistics
            $statsQuery = "
                SELECT 
                    (SELECT COUNT(*) FROM applications WHERE status = 'approved') as total_apps,
                    (SELECT COUNT(*) FROM users WHERE role = 'developer' AND status = 'active') as total_developers,
                    (SELECT COUNT(*) FROM purchases WHERE status = 'completed') as total_downloads,
                    (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users
            ";
            $statsStmt = $pdo->prepare($statsQuery);
            $statsStmt->execute();
            $stats = $statsStmt->fetch();
            
            echo view('home/index', [
                'title' => 'AppMart - AI-Powered Web App Marketplace',
                'featured_apps' => $featuredApps,
                'recent_apps' => $recentApps,
                'categories' => $categories,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            if (config('app.debug')) {
                echo "<h1>Database Error</h1><p>" . $e->getMessage() . "</p>";
            } else {
                echo view('layouts/error', [
                    'title' => 'Service Unavailable',
                    'message' => 'The service is temporarily unavailable. Please try again later.',
                    'code' => 503
                ]);
            }
        }
    }
    
    public function about() {
        echo view('home/about', [
            'title' => 'About AppMart'
        ]);
    }
    
    public function contact() {
        echo view('home/contact', [
            'title' => 'Contact Us'
        ]);
    }
    
    public function terms() {
        echo view('home/terms', [
            'title' => 'Terms of Service'
        ]);
    }
    
    public function privacy() {
        echo view('home/privacy', [
            'title' => 'Privacy Policy'
        ]);
    }
}