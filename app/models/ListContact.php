<?php
// FILE: /app/models/ListContact.php

class ListContact extends Model {
    protected $table = 'list_contacts';

    public function addContact($listId, $contactId, $tenantId) {
        // Check if already exists
        $sql = "SELECT * FROM {$this->table}
                WHERE list_id = :list_id AND contact_id = :contact_id LIMIT 1";

        $existing = $this->db->fetchOne($sql, [
            'list_id' => $listId,
            'contact_id' => $contactId
        ]);

        if ($existing) {
            // Re-subscribe if was unsubscribed
            if ($existing['unsubscribed_at']) {
                $this->db->execute(
                    "UPDATE {$this->table} SET unsubscribed_at = NULL, updated_at = NOW()
                     WHERE list_id = :list_id AND contact_id = :contact_id",
                    ['list_id' => $listId, 'contact_id' => $contactId]
                );
            }
            return $existing['list_id'];
        }

        return $this->create([
            'list_id' => $listId,
            'contact_id' => $contactId,
            'tenant_id' => $tenantId,
            'subscribed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function removeContact($listId, $contactId) {
        $sql = "UPDATE {$this->table}
                SET unsubscribed_at = NOW(), updated_at = NOW()
                WHERE list_id = :list_id AND contact_id = :contact_id";

        return $this->db->execute($sql, [
            'list_id' => $listId,
            'contact_id' => $contactId
        ]);
    }
}
