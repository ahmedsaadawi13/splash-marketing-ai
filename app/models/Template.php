<?php
// FILE: /app/models/Template.php

class Template extends Model {
    protected $table = 'templates';

    public function getByChannel($channel, $tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id AND channel = :channel
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, [
            'tenant_id' => $tenantId,
            'channel' => $channel
        ]);
    }
}
