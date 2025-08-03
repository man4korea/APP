<?php
// 📁 C:\xampp\htdocs\BPM\core\Cache.php
// Create at 2508031410 Ver1.00

namespace BPM\Core;

use Redis;
use RedisException;

/**
 * BPM 다층 캐싱 시스템
 * L1: APCu (Application Cache)
 * L2: Redis (Distributed Cache)
 * L3: MySQL (Persistent Fallback)
 */
class Cache
{
    private static ?Cache $instance = null;
    private ?Redis $redis = null;
    private bool $apcu_enabled = false;
    private bool $redis_enabled = false;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'apcu_hits' => 0,
        'redis_hits' => 0,
        'db_hits' => 0
    ];
    
    private string $tenant_prefix;
    
    private function __construct()
    {
        $this->initializeCache();
        $this->tenant_prefix = 'tenant:' . $this->getCurrentTenantId() . ':';
    }
    
    public static function getInstance(): Cache
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 캐시 시스템 초기화
     */
    private function initializeCache(): void
    {
        // APCu 지원 확인
        $this->apcu_enabled = extension_loaded('apcu') && apcu_enabled();
        
        // Redis 연결 시도
        try {
            $this->redis = new Redis();
            $this->redis->connect(
                $_ENV['REDIS_HOST'] ?? 'localhost',
                (int)($_ENV['REDIS_PORT'] ?? 6379)
            );
            
            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $this->redis->auth($_ENV['REDIS_PASSWORD']);
            }
            
            $database = (int)($_ENV['REDIS_DATABASE'] ?? 0);
            $this->redis->select($database);
            
            // Redis 연결 테스트
            $this->redis->ping();
            $this->redis_enabled = true;
            
            BPMLogger::info('Redis 캐시 연결 성공', [
                'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
                'database' => $database
            ]);
            
        } catch (RedisException $e) {
            $this->redis_enabled = false;
            BPMLogger::warning('Redis 캐시 연결 실패, 데이터베이스 캐시로 폴백', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 캐시에서 값 조회
     */
    public function get(string $key, $default = null)
    {
        $fullKey = $this->tenant_prefix . $key;
        
        // L1: APCu 캐시 확인
        if ($this->apcu_enabled) {
            $value = apcu_fetch($fullKey, $success);
            if ($success) {
                $this->stats['hits']++;
                $this->stats['apcu_hits']++;
                return $this->unserializeValue($value);
            }
        }
        
        // L2: Redis 캐시 확인
        if ($this->redis_enabled) {
            try {
                $value = $this->redis->get($fullKey);
                if ($value !== false) {
                    $this->stats['hits']++;
                    $this->stats['redis_hits']++;
                    
                    // L1 캐시에도 저장 (5분)
                    if ($this->apcu_enabled) {
                        apcu_store($fullKey, $value, 300);
                    }
                    
                    return $this->unserializeValue($value);
                }
            } catch (RedisException $e) {
                BPMLogger::error('Redis 캐시 조회 실패', [
                    'key' => $fullKey,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // L3: 데이터베이스 캐시 확인
        $value = $this->getFromDatabase($fullKey);
        if ($value !== null) {
            $this->stats['hits']++;
            $this->stats['db_hits']++;
            
            // 상위 캐시 레벨에 저장
            $this->setInUpperLevels($fullKey, $value, 3600);
            
            return $value;
        }
        
        $this->stats['misses']++;
        return $default;
    }
    
    /**
     * 캐시에 값 저장
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $fullKey = $this->tenant_prefix . $key;
        $serializedValue = $this->serializeValue($value);
        $success = true;
        
        // L1: APCu 캐시 저장
        if ($this->apcu_enabled) {
            $apcu_ttl = min($ttl, 3600); // APCu는 최대 1시간
            if (!apcu_store($fullKey, $serializedValue, $apcu_ttl)) {
                $success = false;
                BPMLogger::warning('APCu 캐시 저장 실패', ['key' => $fullKey]);
            }
        }
        
        // L2: Redis 캐시 저장
        if ($this->redis_enabled) {
            try {
                if (!$this->redis->setex($fullKey, $ttl, $serializedValue)) {
                    $success = false;
                }
            } catch (RedisException $e) {
                $success = false;
                BPMLogger::error('Redis 캐시 저장 실패', [
                    'key' => $fullKey,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // L3: 데이터베이스 캐시 저장
        if (!$this->setInDatabase($fullKey, $value, $ttl)) {
            $success = false;
        }
        
        return $success;
    }
    
    /**
     * 캐시에서 값 삭제
     */
    public function delete(string $key): bool
    {
        $fullKey = $this->tenant_prefix . $key;
        $success = true;
        
        // L1: APCu 캐시 삭제
        if ($this->apcu_enabled) {
            apcu_delete($fullKey);
        }
        
        // L2: Redis 캐시 삭제
        if ($this->redis_enabled) {
            try {
                $this->redis->del($fullKey);
            } catch (RedisException $e) {
                $success = false;
                BPMLogger::error('Redis 캐시 삭제 실패', [
                    'key' => $fullKey,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // L3: 데이터베이스 캐시 삭제
        if (!$this->deleteFromDatabase($fullKey)) {
            $success = false;
        }
        
        return $success;
    }
    
    /**
     * 패턴으로 캐시 삭제
     */
    public function deleteByPattern(string $pattern): int
    {
        $fullPattern = $this->tenant_prefix . $pattern;
        $deletedCount = 0;
        
        // Redis에서 패턴 매칭으로 삭제
        if ($this->redis_enabled) {
            try {
                $keys = $this->redis->keys($fullPattern);
                if (!empty($keys)) {
                    $deletedCount = $this->redis->del($keys);
                }
            } catch (RedisException $e) {
                BPMLogger::error('Redis 패턴 삭제 실패', [
                    'pattern' => $fullPattern,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * 캐시 통계 조회
     */
    public function getStats(): array
    {
        $redisInfo = [];
        if ($this->redis_enabled) {
            try {
                $info = $this->redis->info();
                $redisInfo = [
                    'memory_used' => $info['used_memory_human'] ?? 'N/A',
                    'connected_clients' => $info['connected_clients'] ?? 0,
                    'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $info['keyspace_misses'] ?? 0
                ];
            } catch (RedisException $e) {
                $redisInfo = ['error' => $e->getMessage()];
            }
        }
        
        return [
            'local_stats' => $this->stats,
            'apcu_enabled' => $this->apcu_enabled,
            'redis_enabled' => $this->redis_enabled,
            'redis_info' => $redisInfo,
            'tenant_prefix' => $this->tenant_prefix
        ];
    }
    
    /**
     * 테넌트별 캐시 전체 삭제
     */
    public function flushTenantCache(): int
    {
        return $this->deleteByPattern('*');
    }
    
    /**
     * 태그 기반 캐시 저장
     */
    public function setWithTags(string $key, $value, array $tags, int $ttl = 3600): bool
    {
        $success = $this->set($key, $value, $ttl);
        
        if ($success) {
            // 태그별 키 목록 저장
            foreach ($tags as $tag) {
                $tagKey = "tag:{$tag}";
                $taggedKeys = $this->get($tagKey, []);
                $taggedKeys[] = $key;
                $this->set($tagKey, array_unique($taggedKeys), $ttl);
            }
        }
        
        return $success;
    }
    
    /**
     * 태그로 캐시 삭제
     */
    public function deleteByTag(string $tag): int
    {
        $tagKey = "tag:{$tag}";
        $taggedKeys = $this->get($tagKey, []);
        $deletedCount = 0;
        
        foreach ($taggedKeys as $key) {
            if ($this->delete($key)) {
                $deletedCount++;
            }
        }
        
        // 태그 키도 삭제
        $this->delete($tagKey);
        
        return $deletedCount;
    }
    
    /**
     * Remember 패턴 구현
     */
    public function remember(string $key, callable $callback, int $ttl = 3600)
    {
        $value = $this->get($key);
        
        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * 값 직렬화
     */
    private function serializeValue($value): string
    {
        return serialize($value);
    }
    
    /**
     * 값 비직렬화
     */
    private function unserializeValue(string $value)
    {
        return unserialize($value);
    }
    
    /**
     * 상위 캐시 레벨에 저장
     */
    private function setInUpperLevels(string $key, $value, int $ttl): void
    {
        $serializedValue = $this->serializeValue($value);
        
        // Redis에 저장
        if ($this->redis_enabled) {
            try {
                $this->redis->setex($key, $ttl, $serializedValue);
            } catch (RedisException $e) {
                BPMLogger::error('Redis 상위 레벨 저장 실패', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // APCu에 저장 (짧은 TTL)
        if ($this->apcu_enabled) {
            $apcu_ttl = min($ttl, 300); // 최대 5분
            apcu_store($key, $serializedValue, $apcu_ttl);
        }
    }
    
    /**
     * 데이터베이스에서 캐시 조회
     */
    private function getFromDatabase(string $key)
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT value FROM cache 
                WHERE `key` = ? AND expiration > UNIX_TIMESTAMP()
            ");
            $stmt->execute([$key]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result) {
                return $this->unserializeValue($result['value']);
            }
            
        } catch (\Exception $e) {
            BPMLogger::error('데이터베이스 캐시 조회 실패', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    /**
     * 데이터베이스에 캐시 저장
     */
    private function setInDatabase(string $key, $value, int $ttl): bool
    {
        try {
            $db = Database::getInstance();
            $serializedValue = $this->serializeValue($value);
            $expiration = time() + $ttl;
            
            $stmt = $db->prepare("
                INSERT INTO cache (`key`, value, expiration) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                value = VALUES(value), 
                expiration = VALUES(expiration)
            ");
            
            return $stmt->execute([$key, $serializedValue, $expiration]);
            
        } catch (\Exception $e) {
            BPMLogger::error('데이터베이스 캐시 저장 실패', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 데이터베이스에서 캐시 삭제
     */
    private function deleteFromDatabase(string $key): bool
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM cache WHERE `key` = ?");
            return $stmt->execute([$key]);
            
        } catch (\Exception $e) {
            BPMLogger::error('데이터베이스 캐시 삭제 실패', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * 현재 테넌트 ID 조회
     */
    private function getCurrentTenantId(): string
    {
        // 세션에서 테넌트 ID 조회
        return $_SESSION['tenant_id'] ?? 'default';
    }
    
    /**
     * 만료된 캐시 정리 (크론 작업용)
     */
    public static function cleanupExpiredCache(): int
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM cache WHERE expiration <= UNIX_TIMESTAMP()");
            $stmt->execute();
            
            $deletedCount = $stmt->rowCount();
            
            BPMLogger::info('만료된 캐시 정리 완료', [
                'deleted_count' => $deletedCount
            ]);
            
            return $deletedCount;
            
        } catch (\Exception $e) {
            BPMLogger::error('캐시 정리 실패', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}

/**
 * 특수 목적 캐시 매니저들
 */
class CacheManager
{
    /**
     * 사용자 세션 캐시
     */
    public static function getUserSession(string $userId): ?array
    {
        $cache = Cache::getInstance();
        return $cache->get("user_session:{$userId}");
    }
    
    public static function setUserSession(string $userId, array $sessionData, int $ttl = 3600): bool
    {
        $cache = Cache::getInstance();
        return $cache->set("user_session:{$userId}", $sessionData, $ttl);
    }
    
    /**
     * 조직 정보 캐시
     */
    public static function getOrganization(string $orgId): ?array
    {
        $cache = Cache::getInstance();
        return $cache->remember("organization:{$orgId}", function() use ($orgId) {
            // 데이터베이스에서 조직 정보 조회
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM organizations WHERE id = ?");
            $stmt->execute([$orgId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }, 7200); // 2시간
    }
    
    /**
     * 권한 정보 캐시
     */
    public static function getUserPermissions(string $userId): array
    {
        $cache = Cache::getInstance();
        return $cache->remember("user_permissions:{$userId}", function() use ($userId) {
            // 데이터베이스에서 사용자 권한 조회
            $db = Database::getInstance();
            $stmt = $db->prepare("
                SELECT tu.role, tu.permissions 
                FROM tenant_users tu 
                WHERE tu.user_id = ? AND tu.status = 'active'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }, 1800); // 30분
    }
    
    /**
     * API 응답 캐시
     */
    public static function cacheApiResponse(string $endpoint, array $params, $response, int $ttl = 300): bool
    {
        $cache = Cache::getInstance();
        $key = "api:" . md5($endpoint . serialize($params));
        return $cache->setWithTags($key, $response, ['api', $endpoint], $ttl);
    }
    
    /**
     * 설정 정보 캐시
     */
    public static function getTenantConfig(string $tenantId): array
    {
        $cache = Cache::getInstance();
        return $cache->remember("tenant_config:{$tenantId}", function() use ($tenantId) {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT settings FROM tenants WHERE id = ?");
            $stmt->execute([$tenantId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return json_decode($result['settings'] ?? '{}', true);
        }, 3600); // 1시간
    }
}