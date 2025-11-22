<?php
// FILE: /app/models/Plan.php

class Plan extends Model {
    protected $table = 'plans';
    protected $tenantScoped = false;

    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY price ASC";
        return $this->db->fetchAll($sql);
    }

    public function getFeatures($planId) {
        $plan = $this->findById($planId);
        if ($plan && $plan['features_json']) {
            return json_decode($plan['features_json'], true);
        }
        return [];
    }
}
