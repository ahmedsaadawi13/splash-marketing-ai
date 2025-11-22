<?php
// FILE: /app/models/Contact.php

class Contact extends Model {
    protected $table = 'contacts';

    public function findByEmail($email, $tenantId) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND tenant_id = :tenant_id LIMIT 1";
        return $this->db->fetchOne($sql, [
            'email' => $email,
            'tenant_id' => $tenantId
        ]);
    }

    public function upsert($data, $tenantId) {
        // Check if contact exists
        $existing = $this->findByEmail($data['email'], $tenantId);

        if ($existing) {
            // Update existing contact
            $this->update($existing['id'], $data, $tenantId);
            return $existing['id'];
        } else {
            // Create new contact
            $data['tenant_id'] = $tenantId;
            $data['full_name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            return $this->create($data);
        }
    }

    public function updateEngagement($contactId, $type, $tenantId) {
        $fields = ['last_engagement_at' => date('Y-m-d H:i:s')];

        if ($type === 'open') {
            $fields['last_open_at'] = date('Y-m-d H:i:s');
        } elseif ($type === 'click') {
            $fields['last_click_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($contactId, $fields, $tenantId);
    }

    public function unsubscribe($contactId, $tenantId) {
        return $this->update($contactId, ['status' => 'unsubscribed'], $tenantId);
    }

    public function markBounced($contactId, $tenantId) {
        return $this->update($contactId, ['status' => 'bounced'], $tenantId);
    }

    public function search($tenantId, $query, $limit = 50) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id
                AND (email LIKE :query OR first_name LIKE :query OR last_name LIKE :query OR phone LIKE :query)
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getByList($listId, $tenantId, $status = 'subscribed') {
        $sql = "SELECT c.*
                FROM {$this->table} c
                INNER JOIN list_contacts lc ON c.id = lc.contact_id
                WHERE c.tenant_id = :tenant_id
                AND lc.list_id = :list_id";

        $params = [
            'tenant_id' => $tenantId,
            'list_id' => $listId
        ];

        if ($status) {
            $sql .= " AND c.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }
}
