<?php
// FILE: /app/models/Payment.php

class Payment extends Model {
    protected $table = 'payments';

    public function recordPayment($tenantId, $invoiceId, $amount, $method, $transactionReference) {
        $paymentId = $this->create([
            'tenant_id' => $tenantId,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'method' => $method,
            'transaction_reference' => $transactionReference,
            'paid_at' => date('Y-m-d H:i:s')
        ]);

        // Mark invoice as paid
        $invoiceModel = new Invoice();
        $invoiceModel->markPaid($invoiceId, $tenantId);

        return $paymentId;
    }

    public function getByInvoice($invoiceId, $tenantId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE invoice_id = :invoice_id AND tenant_id = :tenant_id
                ORDER BY paid_at DESC";

        return $this->db->fetchAll($sql, [
            'invoice_id' => $invoiceId,
            'tenant_id' => $tenantId
        ]);
    }
}
