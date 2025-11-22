<?php
// FILE: /app/models/AutomationStep.php

class AutomationStep extends Model {
    protected $table = 'automation_steps';

    public function getByFlow($flowId, $tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE flow_id = :flow_id AND tenant_id = :tenant_id
                ORDER BY step_order ASC";

        return $this->db->fetchAll($sql, [
            'flow_id' => $flowId,
            'tenant_id' => $tenantId
        ]);
    }

    public function getNextStep($flowId, $currentStepOrder, $tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE flow_id = :flow_id
                AND tenant_id = :tenant_id
                AND step_order > :current_step_order
                ORDER BY step_order ASC
                LIMIT 1";

        return $this->db->fetchOne($sql, [
            'flow_id' => $flowId,
            'tenant_id' => $tenantId,
            'current_step_order' => $currentStepOrder
        ]);
    }
}
