<?php
/**
 * AppMart Database Migration Tool
 * C:\xampp\htdocs\AppMart\database\migrate.php
 * Create at 2508041600 Ver1.00
 */

require_once __DIR__ . '/../bootstrap.php';

class DatabaseMigrator {
    private $pdo;
    private $migrationsPath;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->migrationsPath = __DIR__ . '/migrations';
        $this->createMigrationsTable();
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->pdo->exec($sql);
    }
    
    public function migrate() {
        echo "🚀 Starting database migration...\n";
        
        $migrationFiles = glob($this->migrationsPath . '/*.sql');
        sort($migrationFiles);
        
        $executedMigrations = $this->getExecutedMigrations();
        $migrationsRun = 0;
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file);
            
            if (in_array($migrationName, $executedMigrations)) {
                echo "⏭️  Skipping {$migrationName} (already executed)\n";
                continue;
            }
            
            echo "⚡ Executing {$migrationName}...\n";
            
            try {
                $sql = file_get_contents($file);
                $this->pdo->exec($sql);
                $this->markMigrationAsExecuted($migrationName);
                $migrationsRun++;
                echo "✅ {$migrationName} completed successfully\n";
            } catch (PDOException $e) {
                echo "❌ Error executing {$migrationName}: " . $e->getMessage() . "\n";
                break;
            }
        }
        
        if ($migrationsRun === 0) {
            echo "✨ Database is already up to date!\n";
        } else {
            echo "\n🎉 Migration completed! {$migrationsRun} migrations executed.\n";
        }
    }
    
    public function seed() {
        echo "\n🌱 Starting database seeding...\n";
        
        $seedFiles = glob(__DIR__ . '/seeds/*.sql');
        sort($seedFiles);
        
        foreach ($seedFiles as $file) {
            $seedName = basename($file);
            echo "🌾 Executing {$seedName}...\n";
            
            try {
                $sql = file_get_contents($file);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $this->pdo->exec($statement);
                    }
                }
                
                echo "✅ {$seedName} completed successfully\n";
            } catch (PDOException $e) {
                echo "❌ Error executing {$seedName}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n🎉 Database seeding completed!\n";
    }
    
    public function reset() {
        echo "⚠️  Resetting database...\n";
        
        if (!$this->confirmAction("This will DROP ALL TABLES. Are you sure?")) {
            echo "❌ Reset cancelled.\n";
            return;
        }
        
        // Get all tables
        $stmt = $this->pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Disable foreign key checks
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Drop all tables
        foreach ($tables as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS `{$table}`");
            echo "🗑️  Dropped table: {$table}\n";
        }
        
        // Re-enable foreign key checks
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "✅ Database reset completed!\n";
    }
    
    public function status() {
        echo "📊 Migration Status:\n";
        echo "==================\n";
        
        $migrationFiles = glob($this->migrationsPath . '/*.sql');
        sort($migrationFiles);
        
        $executedMigrations = $this->getExecutedMigrations();
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file);
            $status = in_array($migrationName, $executedMigrations) ? '✅ Executed' : '⏳ Pending';
            echo "{$migrationName}: {$status}\n";
        }
        
        echo "\nTotal migrations: " . count($migrationFiles) . "\n";
        echo "Executed: " . count($executedMigrations) . "\n";
        echo "Pending: " . (count($migrationFiles) - count($executedMigrations)) . "\n";
    }
    
    private function getExecutedMigrations() {
        try {
            $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY executed_at");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function markMigrationAsExecuted($migrationName) {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$migrationName]);
    }
    
    private function confirmAction($message) {
        if (php_sapi_name() !== 'cli') {
            return true; // Auto-confirm for web interface
        }
        
        echo $message . " (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        return strtolower(trim($line)) === 'y';
    }
}

// CLI Usage
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? 'help';
    
    try {
        $migrator = new DatabaseMigrator($pdo);
        
        switch ($action) {
            case 'migrate':
                $migrator->migrate();
                break;
                
            case 'seed':
                $migrator->seed();
                break;
                
            case 'refresh':
                $migrator->migrate();
                $migrator->seed();
                break;
                
            case 'reset':
                $migrator->reset();
                break;
                
            case 'status':
                $migrator->status();
                break;
                
            default:
                echo "AppMart Database Migration Tool\n";
                echo "==============================\n";
                echo "Usage: php database/migrate.php [command]\n\n";
                echo "Commands:\n";
                echo "  migrate  - Run pending migrations\n";
                echo "  seed     - Run database seeders\n";
                echo "  refresh  - Run migrations and seeders\n";
                echo "  reset    - Drop all tables (DANGEROUS!)\n";
                echo "  status   - Show migration status\n";
                echo "  help     - Show this help message\n";
                break;
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    // Web interface for migration (admin only)
    $action = $_GET['action'] ?? 'status';
    
    // Simple authentication check (you should implement proper admin authentication)
    if (!config('app.debug')) {
        die('Access denied');
    }
    
    echo "<h1>AppMart Database Migration Tool</h1>";
    
    try {
        $migrator = new DatabaseMigrator($pdo);
        
        switch ($action) {
            case 'migrate':
                echo "<h2>Running Migrations</h2><pre>";
                ob_start();
                $migrator->migrate();
                echo ob_get_clean();
                echo "</pre>";
                break;
                
            case 'seed':
                echo "<h2>Running Seeders</h2><pre>";
                ob_start();
                $migrator->seed();
                echo ob_get_clean();
                echo "</pre>";
                break;
                
            case 'refresh':
                echo "<h2>Refreshing Database</h2><pre>";
                ob_start();
                $migrator->migrate();
                $migrator->seed();
                echo ob_get_clean();
                echo "</pre>";
                break;
                
            default:
                echo "<h2>Migration Status</h2><pre>";
                ob_start();
                $migrator->status();
                echo ob_get_clean();
                echo "</pre>";
                echo "<p>";
                echo "<a href='?action=migrate'>Run Migrations</a> | ";
                echo "<a href='?action=seed'>Run Seeders</a> | ";
                echo "<a href='?action=refresh'>Refresh Database</a>";
                echo "</p>";
                break;
        }
    } catch (Exception $e) {
        echo "<div style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}