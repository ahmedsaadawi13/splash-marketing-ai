<?php
// FILE: /app/models/AutomationStepLog.php

class AutomationStepLog extends Model {
    protected $table = 'automation_step_logs';

    public function logStep($executionId, $stepId, $stepType, $status, $details, $tenantId) {
        return $this->create([
            'tenant_id' => $tenantId,
            'execution_id' => $executionId,
            'step_id' => $stepId,
            'step_type' => $stepType,
            'status' => $status,
            'details_json' => is_array($details) ? json_encode($details) : $details,
            'executed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getByExecution($executionId, $tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE execution_id = :execution_id AND tenant_id = :tenant_id
                ORDER BY executed_at ASC";

        return $this->db->fetchAll($sql, [
            'execution_id' => $executionId,
            'tenant_id' => $tenantId
        ]);
    }
}
