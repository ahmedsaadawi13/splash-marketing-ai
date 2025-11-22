<?php
// FILE: /app/models/ContactList.php

class ContactList extends Model {
    protected $table = 'lists';

    public function getDefault($tenantId) {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id AND is_default = 1 LIMIT 1";
        return $this->db->fetchOne($sql, ['tenant_id' => $tenantId]);
    }

    public function getContactCount($listId, $tenantId) {
        $sql = "SELECT COUNT(*) as total
                FROM list_contacts lc
                INNER JOIN contacts c ON lc.contact_id = c.id
                WHERE lc.list_id = :list_id
                AND c.tenant_id = :tenant_id
                AND lc.unsubscribed_at IS NULL";

        $result = $this->db->fetchOne($sql, [
            'list_id' => $listId,
            'tenant_id' => $tenantId
        ]);

        return $result['total'];
    }
}
