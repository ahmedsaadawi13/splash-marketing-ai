<?php
// FILE: /app/models/AutomationExecution.php

class AutomationExecution extends Model {
    protected $table = 'automation_executions';

    public function startExecution($flowId, $contactId, $tenantId) {
        return $this->create([
            'tenant_id' => $tenantId,
            'flow_id' => $flowId,
            'contact_id' => $contactId,
            'status' => 'active',
            'current_step_order' => 0,
            'last_step_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateStep($executionId, $stepOrder, $tenantId) {
        return $this->update($executionId, [
            'current_step_order' => $stepOrder,
            'last_step_at' => date('Y-m-d H:i:s')
        ], $tenantId);
    }

    public function complete($executionId, $tenantId) {
        return $this->update($executionId, ['status' => 'completed'], $tenantId);
    }

    public function cancel($executionId, $tenantId) {
        return $this->update($executionId, ['status' => 'canceled'], $tenantId);
    }

    public function getActiveExecutions($tenantId, $limit = 100) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id
                AND status = 'active'
                ORDER BY last_step_at ASC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
