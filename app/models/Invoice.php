<?php
// FILE: /app/models/Invoice.php

class Invoice extends Model {
    protected $table = 'invoices';

    public function createInvoice($tenantId, $amount, $currency, $description, $dueDate) {
        return $this->create([
            'tenant_id' => $tenantId,
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'due_date' => $dueDate,
            'status' => 'unpaid'
        ]);
    }

    public function markPaid($invoiceId, $tenantId) {
        return $this->update($invoiceId, ['status' => 'paid'], $tenantId);
    }

    public function markOverdue($invoiceId, $tenantId) {
        return $this->update($invoiceId, ['status' => 'overdue'], $tenantId);
    }

    public function getUnpaid($tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = :tenant_id AND status = 'unpaid'
                ORDER BY due_date ASC";

        return $this->db->fetchAll($sql, ['tenant_id' => $tenantId]);
    }
}
