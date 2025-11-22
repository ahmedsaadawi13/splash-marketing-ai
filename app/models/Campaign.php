<?php
// FILE: /app/models/Campaign.php

class Campaign extends Model {
    protected $table = 'campaigns';

    public function getByStatus($status, $tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id AND status = :status
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, [
            'tenant_id' => $tenantId,
            'status' => $status
        ]);
    }

    public function updateStatus($id, $status, $tenantId) {
        $updateData = ['status' => $status];

        if ($status === 'sent') {
            $updateData['sent_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($id, $updateData, $tenantId);
    }

    public function getStats($campaignId, $tenantId) {
        $analyticsHelper = new AnalyticsHelper();
        return $analyticsHelper->getCampaignPerformance($campaignId, $tenantId);
    }

    public function getScheduled() {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'scheduled'
                AND scheduled_at <= NOW()
                ORDER BY scheduled_at ASC";

        return $this->db->fetchAll($sql);
    }
}
