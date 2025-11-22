<?php
// FILE: /app/models/User.php

class User extends Model {
    protected $table = 'users';

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        return $this->db->fetchOne($sql, ['email' => $email]);
    }

    public function createUser($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->create($data);
    }

    public function updatePassword($id, $newPassword, $tenantId = null) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword], $tenantId);
    }

    public function getByTenant($tenantId) {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, ['tenant_id' => $tenantId]);
    }

    public function deactivate($id, $tenantId = null) {
        return $this->update($id, ['is_active' => 0], $tenantId);
    }
}
