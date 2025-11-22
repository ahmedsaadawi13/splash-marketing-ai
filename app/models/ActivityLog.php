<?php
// FILE: /app/models/ActivityLog.php

class ActivityLog extends Model {
    protected $table = 'activity_logs';

    public function log($tenantId, $userId, $entityType, $entityId, $action, $description) {
        return $this->create([
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

    public function getRecent($tenantId, $limit = 50) {
        $sql = "SELECT al.*, u.first_name, u.last_name, u.email
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.tenant_id = :tenant_id
                ORDER BY al.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getByEntity($entityType, $entityId, $tenantId) {
        $sql = "SELECT al.*, u.first_name, u.last_name, u.email
                FROM {$this->table} al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.tenant_id = :tenant_id
                AND al.entity_type = :entity_type
                AND al.entity_id = :entity_id
                ORDER BY al.created_at DESC";

        return $this->db->fetchAll($sql, [
            'tenant_id' => $tenantId,
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);
    }
}
