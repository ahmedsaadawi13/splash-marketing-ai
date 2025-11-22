<?php
// FILE: /app/core/Controller.php

class Controller {
    protected $view;
    protected $request;
    protected $response;
    protected $session;
    protected $auth;

    public function __construct() {
        $this->view = new View();
        $this->request = new Request();
        $this->response = new Response();
        $this->session = Session::getInstance();
        $this->auth = Auth::getInstance();
    }

    protected function requireAuth() {
        if (!$this->auth->check()) {
            $this->redirect('/login');
            exit;
        }
    }

    protected function requireRole($roles) {
        if (!$this->auth->check()) {
            $this->redirect('/login');
            exit;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $userRole = $this->auth->user()['role'];
        if (!in_array($userRole, $roles)) {
            $this->response->forbidden();
            echo "Access denied. Insufficient permissions.";
            exit;
        }
    }

    protected function requireTenantActive() {
        if (!$this->auth->check()) {
            $this->redirect('/login');
            exit;
        }

        $tenantModel = new Tenant();
        $tenant = $tenantModel->findById($this->auth->tenantId());

        if (!$tenant || $tenant['status'] !== 'active') {
            $this->view->render('errors/tenant_inactive', [
                'message' => 'Your account is currently inactive. Please contact support.'
            ]);
            exit;
        }
    }

    protected function checkQuota($quotaType) {
        $usageModel = new TenantUsage();
        $subscriptionModel = new TenantSubscription();

        $tenantId = $this->auth->tenantId();
        $usage = $usageModel->getByTenantId($tenantId);
        $subscription = $subscriptionModel->getActivePlan($tenantId);

        if (!$subscription) {
            return ['allowed' => false, 'message' => 'No active subscription'];
        }

        $plan = $subscription;

        switch ($quotaType) {
            case 'contacts':
                if ($usage['contacts_count'] >= $plan['max_contacts']) {
                    return ['allowed' => false, 'message' => 'Contact limit reached'];
                }
                break;
            case 'sends':
                if ($usage['sends_this_month'] >= $plan['max_sends_per_month']) {
                    return ['allowed' => false, 'message' => 'Monthly send limit reached'];
                }
                break;
            case 'ai_tokens':
                if ($usage['ai_tokens_used_this_month'] >= $plan['max_ai_tokens_per_month']) {
                    return ['allowed' => false, 'message' => 'AI token limit reached'];
                }
                break;
            case 'api_calls':
                if ($usage['api_calls_this_month'] >= $plan['max_api_calls_per_month']) {
                    return ['allowed' => false, 'message' => 'API call limit reached'];
                }
                break;
        }

        return ['allowed' => true];
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }

    protected function json($data, $statusCode = 200) {
        $this->response->json($data, $statusCode);
    }

    protected function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
}
