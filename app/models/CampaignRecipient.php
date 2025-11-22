<?php
// FILE: /app/models/CampaignRecipient.php

class CampaignRecipient extends Model {
    protected $table = 'campaign_recipients';

    public function createRecipients($campaignId, $contacts, $tenantId, $channel) {
        $insertedIds = [];

        foreach ($contacts as $contact) {
            $recipientData = [
                'tenant_id' => $tenantId,
                'campaign_id' => $campaignId,
                'contact_id' => $contact['id'],
                'email' => $contact['email'] ?? null,
                'phone' => $contact['phone'] ?? null,
                'channel' => $channel,
                'status' => 'pending'
            ];

            $insertedIds[] = $this->create($recipientData);
        }

        return $insertedIds;
    }

    public function updateStatus($id, $status, $tenantId) {
        return $this->update($id, [
            'status' => $status,
            'last_event_at' => date('Y-m-d H:i:s')
        ], $tenantId);
    }

    public function recordOpen($recipientId, $tenantId) {
        $sql = "UPDATE {$this->table}
                SET status = CASE WHEN status = 'sent' THEN 'opened' ELSE status END,
                    open_count = open_count + 1,
                    last_event_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id AND tenant_id = :tenant_id";

        return $this->db->execute($sql, [
            'id' => $recipientId,
            'tenant_id' => $tenantId
        ]);
    }

    public function recordClick($recipientId, $tenantId) {
        $sql = "UPDATE {$this->table}
                SET status = 'clicked',
                    click_count = click_count + 1,
                    last_event_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id AND tenant_id = :tenant_id";

        return $this->db->execute($sql, [
            'id' => $recipientId,
            'tenant_id' => $tenantId
        ]);
    }

    public function getPending($campaignId, $tenantId, $limit = 100) {
        $sql = "SELECT * FROM {$this->table}
                WHERE campaign_id = :campaign_id
                AND tenant_id = :tenant_id
                AND status = 'pending'
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
