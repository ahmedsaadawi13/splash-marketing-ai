<?php
// FILE: /app/helpers/AnalyticsHelper.php

class AnalyticsHelper {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get tenant dashboard statistics
     */
    public function getTenantDashboard($tenantId) {
        return [
            'contacts' => $this->getContactStats($tenantId),
            'campaigns' => $this->getCampaignStats($tenantId),
            'engagement' => $this->getEngagementStats($tenantId),
            'sends' => $this->getSendStats($tenantId),
            'automation' => $this->getAutomationStats($tenantId),
            'usage' => $this->getUsageStats($tenantId)
        ];
    }

    /**
     * Get contact statistics
     */
    public function getContactStats($tenantId) {
        $sql = "SELECT
                COUNT(*) as total_contacts,
                SUM(CASE WHEN status = 'subscribed' THEN 1 ELSE 0 END) as subscribed,
                SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) as unsubscribed,
                SUM(CASE WHEN status = 'bounced' THEN 1 ELSE 0 END) as bounced,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_this_month,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_this_week
                FROM contacts
                WHERE tenant_id = :tenant_id";

        return $this->db->fetchOne($sql, ['tenant_id' => $tenantId]);
    }

    /**
     * Get campaign statistics
     */
    public function getCampaignStats($tenantId) {
        $sql = "SELECT
                COUNT(*) as total_campaigns,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'sending' THEN 1 ELSE 0 END) as sending,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as created_this_month
                FROM campaigns
                WHERE tenant_id = :tenant_id";

        return $this->db->fetchOne($sql, ['tenant_id' => $tenantId]);
    }

    /**
     * Get engagement statistics
     */
    public function getEngagementStats($tenantId) {
        $sql = "SELECT
                COUNT(*) as total_recipients,
                SUM(CASE WHEN status IN ('sent', 'opened', 'clicked') THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status IN ('opened', 'clicked') THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN status = 'clicked' THEN 1 ELSE 0 END) as clicked,
                SUM(CASE WHEN status = 'bounced' THEN 1 ELSE 0 END) as bounced,
                SUM(CASE WHEN status = 'complained' THEN 1 ELSE 0 END) as complained,
                SUM(open_count) as total_opens,
                SUM(click_count) as total_clicks
                FROM campaign_recipients
                WHERE tenant_id = :tenant_id
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stats = $this->db->fetchOne($sql, ['tenant_id' => $tenantId]);

        // Calculate rates
        $delivered = $stats['delivered'] > 0 ? $stats['delivered'] : 1;
        $stats['open_rate'] = round(($stats['opened'] / $delivered) * 100, 2);
        $stats['click_rate'] = round(($stats['clicked'] / $delivered) * 100, 2);
        $stats['bounce_rate'] = round(($stats['bounced'] / $delivered) * 100, 2);

        return $stats;
    }

    /**
     * Get send statistics by channel
     */
    public function getSendStats($tenantId) {
        $sql = "SELECT
                channel,
                COUNT(*) as count,
                SUM(CASE WHEN status IN ('sent', 'opened', 'clicked') THEN 1 ELSE 0 END) as delivered
                FROM campaign_recipients
                WHERE tenant_id = :tenant_id
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY channel";

        $results = $this->db->fetchAll($sql, ['tenant_id' => $tenantId]);

        $stats = [
            'total_sends' => 0,
            'email' => 0,
            'sms' => 0,
            'social' => 0
        ];

        foreach ($results as $row) {
            $stats['total_sends'] += $row['count'];
            $stats[$row['channel']] = $row['count'];
        }

        return $stats;
    }

    /**
     * Get automation flow statistics
     */
    public function getAutomationStats($tenantId) {
        $sql = "SELECT
                COUNT(DISTINCT af.id) as total_flows,
                SUM(CASE WHEN af.is_active = 1 THEN 1 ELSE 0 END) as active_flows,
                COUNT(DISTINCT ae.id) as total_executions,
                SUM(CASE WHEN ae.status = 'active' THEN 1 ELSE 0 END) as active_executions,
                SUM(CASE WHEN ae.status = 'completed' THEN 1 ELSE 0 END) as completed_executions
                FROM automation_flows af
                LEFT JOIN automation_executions ae ON af.id = ae.flow_id
                WHERE af.tenant_id = :tenant_id";

        return $this->db->fetchOne($sql, ['tenant_id' => $tenantId]);
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats($tenantId) {
        $sql = "SELECT * FROM tenant_usage WHERE tenant_id = :tenant_id LIMIT 1";
        return $this->db->fetchOne($sql, ['tenant_id' => $tenantId]);
    }

    /**
     * Get campaign performance details
     */
    public function getCampaignPerformance($campaignId, $tenantId) {
        $sql = "SELECT
                COUNT(*) as total_recipients,
                SUM(CASE WHEN status IN ('sent', 'opened', 'clicked') THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status IN ('opened', 'clicked') THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN status = 'clicked' THEN 1 ELSE 0 END) as clicked,
                SUM(CASE WHEN status = 'bounced' THEN 1 ELSE 0 END) as bounced,
                SUM(CASE WHEN status = 'complained' THEN 1 ELSE 0 END) as complained,
                SUM(open_count) as total_opens,
                SUM(click_count) as total_clicks
                FROM campaign_recipients
                WHERE campaign_id = :campaign_id
                AND tenant_id = :tenant_id";

        $stats = $this->db->fetchOne($sql, [
            'campaign_id' => $campaignId,
            'tenant_id' => $tenantId
        ]);

        // Calculate rates
        $delivered = $stats['delivered'] > 0 ? $stats['delivered'] : 1;
        $stats['delivery_rate'] = round(($stats['delivered'] / max($stats['total_recipients'], 1)) * 100, 2);
        $stats['open_rate'] = round(($stats['opened'] / $delivered) * 100, 2);
        $stats['click_rate'] = round(($stats['clicked'] / $delivered) * 100, 2);
        $stats['bounce_rate'] = round(($stats['bounced'] / max($stats['total_recipients'], 1)) * 100, 2);
        $stats['complaint_rate'] = round(($stats['complained'] / $delivered) * 100, 2);

        return $stats;
    }

    /**
     * Get sends over time (for charts)
     */
    public function getSendsOverTime($tenantId, $days = 30) {
        $sql = "SELECT
                DATE(created_at) as date,
                COUNT(*) as sends,
                SUM(CASE WHEN status IN ('opened', 'clicked') THEN 1 ELSE 0 END) as opens,
                SUM(CASE WHEN status = 'clicked' THEN 1 ELSE 0 END) as clicks
                FROM campaign_recipients
                WHERE tenant_id = :tenant_id
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";

        return $this->db->fetchAll($sql, [
            'tenant_id' => $tenantId,
            'days' => $days
        ]);
    }

    /**
     * Get top performing campaigns
     */
    public function getTopCampaigns($tenantId, $limit = 10) {
        $sql = "SELECT
                c.id,
                c.name,
                c.channel,
                c.status,
                c.sent_at,
                COUNT(cr.id) as recipients,
                SUM(CASE WHEN cr.status IN ('opened', 'clicked') THEN 1 ELSE 0 END) as opens,
                SUM(CASE WHEN cr.status = 'clicked' THEN 1 ELSE 0 END) as clicks,
                ROUND((SUM(CASE WHEN cr.status IN ('opened', 'clicked') THEN 1 ELSE 0 END) / COUNT(cr.id)) * 100, 2) as open_rate
                FROM campaigns c
                LEFT JOIN campaign_recipients cr ON c.id = cr.campaign_id
                WHERE c.tenant_id = :tenant_id
                AND c.status = 'sent'
                GROUP BY c.id
                ORDER BY open_rate DESC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get automation flow performance
     */
    public function getFlowPerformance($flowId, $tenantId) {
        // Get flow executions
        $execSql = "SELECT
                    COUNT(*) as total_executions,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled
                    FROM automation_executions
                    WHERE flow_id = :flow_id AND tenant_id = :tenant_id";

        $execStats = $this->db->fetchOne($execSql, [
            'flow_id' => $flowId,
            'tenant_id' => $tenantId
        ]);

        // Get step performance
        $stepSql = "SELECT
                    s.id,
                    s.step_type,
                    s.step_order,
                    COUNT(sl.id) as executions,
                    SUM(CASE WHEN sl.status = 'executed' THEN 1 ELSE 0 END) as successful,
                    SUM(CASE WHEN sl.status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN sl.status = 'skipped' THEN 1 ELSE 0 END) as skipped
                    FROM automation_steps s
                    LEFT JOIN automation_step_logs sl ON s.id = sl.step_id
                    WHERE s.flow_id = :flow_id AND s.tenant_id = :tenant_id
                    GROUP BY s.id
                    ORDER BY s.step_order ASC";

        $stepStats = $this->db->fetchAll($stepSql, [
            'flow_id' => $flowId,
            'tenant_id' => $tenantId
        ]);

        return [
            'executions' => $execStats,
            'steps' => $stepStats
        ];
    }

    /**
     * Get contact growth over time
     */
    public function getContactGrowth($tenantId, $days = 30) {
        $sql = "SELECT
                DATE(created_at) as date,
                COUNT(*) as new_contacts
                FROM contacts
                WHERE tenant_id = :tenant_id
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";

        return $this->db->fetchAll($sql, [
            'tenant_id' => $tenantId,
            'days' => $days
        ]);
    }
}
