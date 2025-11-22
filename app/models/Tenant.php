<?php
// FILE: /app/models/Tenant.php

class Tenant extends Model {
    protected $table = 'tenants';
    protected $tenantScoped = false;

    public function findByDomain($domain) {
        $sql = "SELECT * FROM {$this->table} WHERE domain = :domain LIMIT 1";
        return $this->db->fetchOne($sql, ['domain' => $domain]);
    }

    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function updateStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }
}
