<?php
// FILE: /app/models/TenantSubscription.php

class TenantSubscription extends Model {
    protected $table = 'tenant_subscriptions';

    public function getActivePlan($tenantId) {
        $sql = "SELECT ts.*, p.*
                FROM {$this->table} ts
                INNER JOIN plans p ON ts.plan_id = p.id
                WHERE ts.tenant_id = :tenant_id
                AND ts.status = 'active'
                ORDER BY ts.created_at DESC
                LIMIT 1";

        return $this->db->fetchOne($sql, ['tenant_id' => $tenantId]);
    }

    public function subscribe($tenantId, $planId, $billingCycle = 'monthly') {
        // Cancel any existing active subscriptions
        $this->db->execute(
            "UPDATE {$this->table} SET status = 'canceled', updated_at = NOW() WHERE tenant_id = :tenant_id AND status = 'active'",
            ['tenant_id' => $tenantId]
        );

        // Create new subscription
        $startDate = date('Y-m-d');
        $renewalDate = $billingCycle === 'yearly'
            ? date('Y-m-d', strtotime('+1 year'))
            : date('Y-m-d', strtotime('+1 month'));

        return $this->create([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'start_date' => $startDate,
            'renewal_date' => $renewalDate
        ]);
    }
}
