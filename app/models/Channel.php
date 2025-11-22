<?php
// FILE: /app/models/Channel.php

class Channel extends Model {
    protected $table = 'channels';

    public function getActive($tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id AND is_active = 1
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, ['tenant_id' => $tenantId]);
    }

    public function getByType($type, $tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id AND type = :type AND is_active = 1
                LIMIT 1";

        return $this->db->fetchOne($sql, [
            'tenant_id' => $tenantId,
            'type' => $type
        ]);
    }
}
