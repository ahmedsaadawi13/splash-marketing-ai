<?php
// FILE: /app/core/Auth.php

class Auth {
    private static $instance = null;
    private $session;
    private $user = null;
    private $loginAttempts = [];

    private function __construct() {
        $this->session = Session::getInstance();
        $this->loadUser();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadUser() {
        $userId = $this->session->get('user_id');
        if ($userId) {
            $db = Database::getInstance();
            $sql = "SELECT u.*, t.name as tenant_name, t.status as tenant_status
                    FROM users u
                    LEFT JOIN tenants t ON u.tenant_id = t.id
                    WHERE u.id = :id AND u.is_active = 1
                    LIMIT 1";
            $this->user = $db->fetchOne($sql, ['id' => $userId]);
        }
    }

    public function attempt($email, $password, $remember = false) {
        // Check login throttling
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($this->isThrottled($ip)) {
            return [
                'success' => false,
                'message' => 'Too many failed login attempts. Please try again in 15 minutes.'
            ];
        }

        $db = Database::getInstance();
        $sql = "SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1";
        $user = $db->fetchOne($sql, ['email' => $email]);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($ip);
            return [
                'success' => false,
                'message' => 'Invalid email or password.'
            ];
        }

        // Check if tenant is active (except for platform_admin)
        if ($user['role'] !== 'platform_admin') {
            $tenantSql = "SELECT status FROM tenants WHERE id = :tenant_id LIMIT 1";
            $tenant = $db->fetchOne($tenantSql, ['tenant_id' => $user['tenant_id']]);

            if (!$tenant || $tenant['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Your account is currently inactive. Please contact support.'
                ];
            }
        }

        // Clear failed attempts
        $this->clearFailedAttempts($ip);

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Set session data
        $this->session->set('user_id', $user['id']);
        $this->session->set('tenant_id', $user['tenant_id']);
        $this->session->set('role', $user['role']);

        // Update last login
        $updateSql = "UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id";
        $db->execute($updateSql, ['ip' => $ip, 'id' => $user['id']]);

        // Log activity
        $this->logActivity($user['id'], $user['tenant_id'], 'user', $user['id'], 'login', 'User logged in');

        $this->user = $user;

        return [
            'success' => true,
            'user' => $user
        ];
    }

    public function logout() {
        if ($this->check()) {
            $this->logActivity(
                $this->user['id'],
                $this->user['tenant_id'],
                'user',
                $this->user['id'],
                'logout',
                'User logged out'
            );
        }

        $this->session->destroy();
        $this->user = null;
    }

    public function check() {
        return $this->user !== null;
    }

    public function user() {
        return $this->user;
    }

    public function id() {
        return $this->user ? $this->user['id'] : null;
    }

    public function tenantId() {
        return $this->user ? $this->user['tenant_id'] : null;
    }

    public function role() {
        return $this->user ? $this->user['role'] : null;
    }

    public function hasRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        return in_array($this->role(), $roles);
    }

    public function isPlatformAdmin() {
        return $this->role() === 'platform_admin';
    }

    public function isTenantAdmin() {
        return $this->role() === 'tenant_admin';
    }

    private function isThrottled($ip) {
        // Clean old attempts (older than 15 minutes)
        $this->cleanOldAttempts();

        if (!isset($this->loginAttempts[$ip])) {
            return false;
        }

        return count($this->loginAttempts[$ip]) >= 5;
    }

    private function recordFailedAttempt($ip) {
        if (!isset($this->loginAttempts[$ip])) {
            $this->loginAttempts[$ip] = [];
        }
        $this->loginAttempts[$ip][] = time();
    }

    private function clearFailedAttempts($ip) {
        if (isset($this->loginAttempts[$ip])) {
            unset($this->loginAttempts[$ip]);
        }
    }

    private function cleanOldAttempts() {
        $threshold = time() - (15 * 60); // 15 minutes
        foreach ($this->loginAttempts as $ip => $attempts) {
            $this->loginAttempts[$ip] = array_filter($attempts, function($time) use ($threshold) {
                return $time > $threshold;
            });
            if (empty($this->loginAttempts[$ip])) {
                unset($this->loginAttempts[$ip]);
            }
        }
    }

    private function logActivity($userId, $tenantId, $entityType, $entityId, $action, $description) {
        $db = Database::getInstance();
        $sql = "INSERT INTO activity_logs (tenant_id, user_id, entity_type, entity_id, action, description, ip_address, user_agent, created_at)
                VALUES (:tenant_id, :user_id, :entity_type, :entity_id, :action, :description, :ip_address, :user_agent, NOW())";

        $db->execute($sql, [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}
