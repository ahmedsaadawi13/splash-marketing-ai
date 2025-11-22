<?php
// FILE: /app/models/TenantUsage.php

class TenantUsage extends Model {
    protected $table = 'tenant_usage';

    public function getByTenantId($tenantId) {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id LIMIT 1";
        return $this->db->fetchOne($sql, ['tenant_id' => $tenantId]);
    }

    public function incrementContacts($tenantId, $count = 1) {
        $sql = "UPDATE {$this->table}
                SET contacts_count = contacts_count + :count,
                    updated_at = NOW()
                WHERE tenant_id = :tenant_id";

        return $this->db->execute($sql, [
            'count' => $count,
            'tenant_id' => $tenantId
        ]);
    }

    public function incrementSends($tenantId, $count = 1) {
        $sql = "UPDATE {$this->table}
                SET sends_this_month = sends_this_month + :count,
                    updated_at = NOW()
                WHERE tenant_id = :tenant_id";

        return $this->db->execute($sql, [
            'count' => $count,
            'tenant_id' => $tenantId
        ]);
    }

    public function incrementApiCalls($tenantId, $count = 1) {
        $sql = "UPDATE {$this->table}
                SET api_calls_this_month = api_calls_this_month + :count,
                    updated_at = NOW()
                WHERE tenant_id = :tenant_id";

        return $this->db->execute($sql, [
            'count' => $count,
            'tenant_id' => $tenantId
        ]);
    }

    public function resetMonthlyCounters($tenantId) {
        $sql = "UPDATE {$this->table}
                SET sends_this_month = 0,
                    ai_tokens_used_this_month = 0,
                    api_calls_this_month = 0,
                    updated_at = NOW()
                WHERE tenant_id = :tenant_id";

        return $this->db->execute($sql, ['tenant_id' => $tenantId]);
    }
}
