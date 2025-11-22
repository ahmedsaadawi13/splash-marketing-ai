<?php
// FILE: /app/controllers/DashboardController.php

class DashboardController extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireTenantActive();

        $tenantId = $this->auth->tenantId();

        // Get dashboard analytics
        $analyticsHelper = new AnalyticsHelper();
        $dashboard = $analyticsHelper->getTenantDashboard($tenantId);

        // Get recent activity
        $activityModel = new ActivityLog();
        $recentActivity = $activityModel->getRecent($tenantId, 10);

        // Get upcoming scheduled campaigns
        $campaignModel = new Campaign();
        $scheduledCampaigns = $campaignModel->getByStatus('scheduled', $tenantId);

        // Get sends over time (last 30 days)
        $sendsOverTime = $analyticsHelper->getSendsOverTime($tenantId, 30);

        $this->view->render('dashboard/index', [
            'dashboard' => $dashboard,
            'recentActivity' => $recentActivity,
            'scheduledCampaigns' => $scheduledCampaigns,
            'sendsOverTime' => $sendsOverTime
        ]);
    }
}
