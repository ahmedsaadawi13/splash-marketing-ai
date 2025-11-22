<?php
// FILE: /app/models/AutomationFlow.php

class AutomationFlow extends Model {
    protected $table = 'automation_flows';

    public function getActive($tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id AND is_active = 1
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, ['tenant_id' => $tenantId]);
    }

    public function getSteps($flowId, $tenantId) {
        $sql = "SELECT * FROM automation_steps
                WHERE flow_id = :flow_id AND tenant_id = :tenant_id
                ORDER BY step_order ASC";

        return $this->db->fetchAll($sql, [
            'flow_id' => $flowId,
            'tenant_id' => $tenantId
        ]);
    }

    public function getExecutions($flowId, $tenantId, $status = null) {
        $sql = "SELECT * FROM automation_executions
                WHERE flow_id = :flow_id AND tenant_id = :tenant_id";

        $params = [
            'flow_id' => $flowId,
            'tenant_id' => $tenantId
        ];

        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }
}
