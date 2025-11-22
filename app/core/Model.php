<?php
// FILE: /app/core/Model.php

class Model {
    protected $db;
    protected $table;
    protected $tenantScoped = true;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    protected function addTenantScope(&$sql, &$params, $tenantId) {
        if ($this->tenantScoped && $tenantId !== null) {
            if (stripos($sql, 'WHERE') !== false) {
                $sql .= " AND tenant_id = :tenant_id";
            } else {
                $sql .= " WHERE tenant_id = :tenant_id";
            }
            $params['tenant_id'] = $tenantId;
        }
    }

    public function findById($id, $tenantId = null) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = ['id' => $id];
        $this->addTenantScope($sql, $params, $tenantId);
        $sql .= " LIMIT 1";
        return $this->db->fetchOne($sql, $params);
    }

    public function all($tenantId = null, $orderBy = 'id', $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        $this->addTenantScope($sql, $params, $tenantId);
        $sql .= " ORDER BY {$orderBy} {$order}";
        return $this->db->fetchAll($sql, $params);
    }

    public function paginate($tenantId = null, $page = 1, $perPage = 20, $orderBy = 'id', $order = 'DESC') {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        $this->addTenantScope($countSql, $params, $tenantId);
        $total = $this->db->fetchOne($countSql, $params)['total'];

        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        $this->addTenantScope($sql, $params, $tenantId);
        $sql .= " ORDER BY {$orderBy} {$order} LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        $stmt->execute();
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $fields = array_keys($data);
        $placeholders = array_map(function($field) { return ":$field"; }, $fields);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->execute($sql, $data);
        return $this->db->lastInsertId();
    }

    public function update($id, $data, $tenantId = null) {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $setParts = [];
        foreach (array_keys($data) as $field) {
            $setParts[] = "$field = :$field";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $data['id'] = $id;

        $params = $data;
        $this->addTenantScope($sql, $params, $tenantId);

        return $this->db->execute($sql, $params);
    }

    public function delete($id, $tenantId = null) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $params = ['id' => $id];
        $this->addTenantScope($sql, $params, $tenantId);
        return $this->db->execute($sql, $params);
    }

    public function where($conditions, $tenantId = null) {
        $whereParts = [];
        $params = [];

        foreach ($conditions as $field => $value) {
            $whereParts[] = "$field = :$field";
            $params[$field] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereParts);
        $this->addTenantScope($sql, $params, $tenantId);

        return $this->db->fetchAll($sql, $params);
    }

    public function count($tenantId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        $this->addTenantScope($sql, $params, $tenantId);
        return $this->db->fetchOne($sql, $params)['total'];
    }
}
