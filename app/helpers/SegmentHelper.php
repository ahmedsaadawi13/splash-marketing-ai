<?php
// FILE: /app/helpers/SegmentHelper.php

class SegmentHelper {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Build SQL query from segment filter JSON
     */
    public function buildSegmentQuery($tenantId, $filterJson) {
        $filters = json_decode($filterJson, true);

        if (!$filters || !isset($filters['conditions'])) {
            return [
                'sql' => "SELECT * FROM contacts WHERE tenant_id = :tenant_id",
                'params' => ['tenant_id' => $tenantId]
            ];
        }

        $sql = "SELECT DISTINCT c.* FROM contacts c";
        $joins = [];
        $where = ["c.tenant_id = :tenant_id"];
        $params = ['tenant_id' => $tenantId];
        $paramCounter = 0;

        foreach ($filters['conditions'] as $condition) {
            $result = $this->buildCondition($condition, $paramCounter);

            if ($result['join']) {
                $joins[] = $result['join'];
            }

            if ($result['where']) {
                $where[] = $result['where'];
            }

            $params = array_merge($params, $result['params']);
        }

        // Add joins
        if (!empty($joins)) {
            $sql .= " " . implode(" ", array_unique($joins));
        }

        // Add where conditions
        $matchType = isset($filters['match']) ? $filters['match'] : 'all';
        $operator = $matchType === 'all' ? ' AND ' : ' OR ';

        if (count($where) > 1) {
            $mainCondition = array_shift($where); // Remove tenant_id condition
            $sql .= " WHERE " . $mainCondition;
            if (!empty($where)) {
                $sql .= " AND (" . implode($operator, $where) . ")";
            }
        } else {
            $sql .= " WHERE " . implode($operator, $where);
        }

        return [
            'sql' => $sql,
            'params' => $params
        ];
    }

    /**
     * Build individual condition
     */
    private function buildCondition($condition, &$paramCounter) {
        $field = $condition['field'];
        $operator = $condition['operator'];
        $value = isset($condition['value']) ? $condition['value'] : null;

        $join = null;
        $where = null;
        $params = [];

        switch ($field) {
            case 'list':
                $join = "INNER JOIN list_contacts lc ON c.id = lc.contact_id";
                $paramName = "param_" . $paramCounter++;
                $where = "lc.list_id = :$paramName";
                $params[$paramName] = $value;
                break;

            case 'tag':
                $paramName = "param_" . $paramCounter++;
                if ($operator === 'contains') {
                    $where = "c.tags LIKE :$paramName";
                    $params[$paramName] = '%' . $value . '%';
                } elseif ($operator === 'not_contains') {
                    $where = "(c.tags NOT LIKE :$paramName OR c.tags IS NULL)";
                    $params[$paramName] = '%' . $value . '%';
                }
                break;

            case 'status':
                $paramName = "param_" . $paramCounter++;
                $where = "c.status = :$paramName";
                $params[$paramName] = $value;
                break;

            case 'country':
                $paramName = "param_" . $paramCounter++;
                if ($operator === 'is') {
                    $where = "c.country = :$paramName";
                    $params[$paramName] = $value;
                } elseif ($operator === 'is_not') {
                    $where = "c.country != :$paramName";
                    $params[$paramName] = $value;
                }
                break;

            case 'email':
                $paramName = "param_" . $paramCounter++;
                if ($operator === 'contains') {
                    $where = "c.email LIKE :$paramName";
                    $params[$paramName] = '%' . $value . '%';
                } elseif ($operator === 'is') {
                    $where = "c.email = :$paramName";
                    $params[$paramName] = $value;
                } elseif ($operator === 'exists') {
                    $where = "c.email IS NOT NULL AND c.email != ''";
                }
                break;

            case 'phone':
                if ($operator === 'exists') {
                    $where = "c.phone IS NOT NULL AND c.phone != ''";
                } elseif ($operator === 'not_exists') {
                    $where = "(c.phone IS NULL OR c.phone = '')";
                }
                break;

            case 'last_open':
                $paramName = "param_" . $paramCounter++;
                if ($operator === 'within_days') {
                    $where = "c.last_open_at >= DATE_SUB(NOW(), INTERVAL :$paramName DAY)";
                    $params[$paramName] = $value;
                } elseif ($operator === 'not_within_days') {
                    $where = "(c.last_open_at < DATE_SUB(NOW(), INTERVAL :$paramName DAY) OR c.last_open_at IS NULL)";
                    $params[$paramName] = $value;
                }
                break;

            case 'last_click':
                $paramName = "param_" . $paramCounter++;
                if ($operator === 'within_days') {
                    $where = "c.last_click_at >= DATE_SUB(NOW(), INTERVAL :$paramName DAY)";
                    $params[$paramName] = $value;
                } elseif ($operator === 'not_within_days') {
                    $where = "(c.last_click_at < DATE_SUB(NOW(), INTERVAL :$paramName DAY) OR c.last_click_at IS NULL)";
                    $params[$paramName] = $value;
                }
                break;

            case 'last_engagement':
                $paramName = "param_" . $paramCounter++;
                if ($operator === 'within_days') {
                    $where = "c.last_engagement_at >= DATE_SUB(NOW(), INTERVAL :$paramName DAY)";
                    $params[$paramName] = $value;
                } elseif ($operator === 'not_within_days') {
                    $where = "(c.last_engagement_at < DATE_SUB(NOW(), INTERVAL :$paramName DAY) OR c.last_engagement_at IS NULL)";
                    $params[$paramName] = $value;
                }
                break;

            case 'created':
                $paramName = "param_" . $paramCounter++;
                if ($operator === 'within_days') {
                    $where = "c.created_at >= DATE_SUB(NOW(), INTERVAL :$paramName DAY)";
                    $params[$paramName] = $value;
                } elseif ($operator === 'before_days') {
                    $where = "c.created_at < DATE_SUB(NOW(), INTERVAL :$paramName DAY)";
                    $params[$paramName] = $value;
                }
                break;

            default:
                // Handle custom attributes from attributes_json
                if (strpos($field, 'custom.') === 0) {
                    $customField = str_replace('custom.', '', $field);
                    $paramName = "param_" . $paramCounter++;

                    if ($operator === 'is') {
                        $where = "JSON_EXTRACT(c.attributes_json, '$.$customField') = :$paramName";
                        $params[$paramName] = $value;
                    } elseif ($operator === 'contains') {
                        $where = "JSON_EXTRACT(c.attributes_json, '$.$customField') LIKE :$paramName";
                        $params[$paramName] = '%' . $value . '%';
                    } elseif ($operator === 'greater_than') {
                        $where = "CAST(JSON_EXTRACT(c.attributes_json, '$.$customField') AS DECIMAL) > :$paramName";
                        $params[$paramName] = $value;
                    } elseif ($operator === 'less_than') {
                        $where = "CAST(JSON_EXTRACT(c.attributes_json, '$.$customField') AS DECIMAL) < :$paramName";
                        $params[$paramName] = $value;
                    }
                }
                break;
        }

        return [
            'join' => $join,
            'where' => $where,
            'params' => $params
        ];
    }

    /**
     * Get contacts matching a segment
     */
    public function getSegmentContacts($tenantId, $segmentId, $limit = null, $offset = null) {
        $segment = $this->db->fetchOne(
            "SELECT * FROM segments WHERE id = :id AND tenant_id = :tenant_id",
            ['id' => $segmentId, 'tenant_id' => $tenantId]
        );

        if (!$segment) {
            return [];
        }

        $query = $this->buildSegmentQuery($tenantId, $segment['filter_json']);
        $sql = $query['sql'];
        $params = $query['params'];

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Count contacts in a segment
     */
    public function countSegmentContacts($tenantId, $segmentId) {
        $segment = $this->db->fetchOne(
            "SELECT * FROM segments WHERE id = :id AND tenant_id = :tenant_id",
            ['id' => $segmentId, 'tenant_id' => $tenantId]
        );

        if (!$segment) {
            return 0;
        }

        $query = $this->buildSegmentQuery($tenantId, $segment['filter_json']);

        // Replace SELECT clause with COUNT
        $countSql = preg_replace('/^SELECT DISTINCT c\.\* FROM/', 'SELECT COUNT(DISTINCT c.id) as total FROM', $query['sql']);

        $result = $this->db->fetchOne($countSql, $query['params']);
        return $result['total'];
    }

    /**
     * Test if a contact matches segment criteria
     */
    public function contactMatchesSegment($contactId, $segmentId, $tenantId) {
        $contacts = $this->getSegmentContacts($tenantId, $segmentId);
        foreach ($contacts as $contact) {
            if ($contact['id'] == $contactId) {
                return true;
            }
        }
        return false;
    }
}
