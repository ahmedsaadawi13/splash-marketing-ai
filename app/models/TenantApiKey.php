<?php
// FILE: /app/models/TenantApiKey.php

class TenantApiKey extends Model {
    protected $table = 'tenant_api_keys';

    public function generateKey($tenantId, $label) {
        $apiKey = 'sk_' . bin2hex(random_bytes(32));

        return [
            'id' => $this->create([
                'tenant_id' => $tenantId,
                'api_key' => hash('sha256', $apiKey),
                'label' => $label,
                'is_active' => 1
            ]),
            'api_key' => $apiKey // Return plain key only once
        ];
    }

    public function validateKey($apiKey) {
        $hashedKey = hash('sha256', $apiKey);

        $sql = "SELECT tk.*, t.id as tenant_id, t.status as tenant_status
                FROM {$this->table} tk
                INNER JOIN tenants t ON tk.tenant_id = t.id
                WHERE tk.api_key = :api_key AND tk.is_active = 1
                LIMIT 1";

        $result = $this->db->fetchOne($sql, ['api_key' => $hashedKey]);

        return $result && $result['tenant_status'] === 'active' ? $result : null;
    }

    public function deactivate($id, $tenantId) {
        return $this->update($id, ['is_active' => 0], $tenantId);
    }
}
