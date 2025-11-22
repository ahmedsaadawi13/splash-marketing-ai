<?php
// FILE: /app/controllers/AuthController.php

class AuthController extends Controller {
    public function showLogin() {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }

        $this->view->render('auth/login', [], 'layouts/guest');
    }

    public function login() {
        CSRF::verify();

        $email = $this->request->post('email');
        $password = $this->request->post('password');

        $result = $this->auth->attempt($email, $password);

        if ($result['success']) {
            $this->redirect('/dashboard');
        } else {
            $this->session->setFlash('error', $result['message']);
            $this->redirect('/login');
        }
    }

    public function showRegister() {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }

        $planModel = new Plan();
        $plans = $planModel->getActive();

        $this->view->render('auth/register', [
            'plans' => $plans
        ], 'layouts/guest');
    }

    public function register() {
        CSRF::verify();

        $validator = new ValidationHelper();
        $isValid = $validator->validate($this->request->post(), [
            'company_name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'password_confirm' => 'required|match:password',
            'plan_id' => 'required|exists:plans,id'
        ]);

        if (!$isValid) {
            $this->session->setFlash('error', $validator->firstError());
            $this->session->setFlash('old_input', $this->request->post());
            $this->redirect('/register');
            return;
        }

        // Begin transaction
        $this->db = Database::getInstance();
        $this->db->beginTransaction();

        try {
            // Create tenant
            $tenantModel = new Tenant();
            $tenantId = $tenantModel->create([
                'name' => $this->request->post('company_name'),
                'domain' => strtolower(str_replace(' ', '-', $this->request->post('company_name'))),
                'status' => 'active'
            ]);

            // Create user
            $userModel = new User();
            $userId = $userModel->createUser([
                'tenant_id' => $tenantId,
                'first_name' => $this->request->post('first_name'),
                'last_name' => $this->request->post('last_name'),
                'email' => $this->request->post('email'),
                'password' => $this->request->post('password'),
                'role' => 'tenant_admin',
                'is_active' => 1
            ]);

            // Create subscription
            $subscriptionModel = new TenantSubscription();
            $subscriptionModel->subscribe($tenantId, $this->request->post('plan_id'), 'monthly');

            // Initialize usage tracking
            $usageModel = new TenantUsage();
            $usageModel->create([
                'tenant_id' => $tenantId,
                'contacts_count' => 0,
                'sends_this_month' => 0,
                'ai_tokens_used_this_month' => 0,
                'api_calls_this_month' => 0,
                'storage_bytes_used' => 0
            ]);

            // Create default list
            $listModel = new ContactList();
            $listModel->create([
                'tenant_id' => $tenantId,
                'name' => 'All Subscribers',
                'description' => 'Default subscriber list',
                'is_default' => 1
            ]);

            $this->db->commit();

            // Auto-login
            $this->auth->attempt($this->request->post('email'), $this->request->post('password'));

            $this->session->setFlash('success', 'Account created successfully! Welcome to SplashMarketingAI.');
            $this->redirect('/dashboard');

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Registration error: " . $e->getMessage());
            $this->session->setFlash('error', 'Registration failed. Please try again.');
            $this->redirect('/register');
        }
    }

    public function logout() {
        $this->auth->logout();
        $this->redirect('/login');
    }
}
