<?php
// FILE: /app/controllers/ApiController.php

class ApiController extends Controller {
    private $tenantId;
    private $apiKey;

    public function __construct() {
        parent::__construct();
        $this->authenticateApi();
    }

    private function authenticateApi() {
        $apiKey = $this->request->header('X-API-KEY');

        if (!$apiKey) {
            $this->json(['status' => 'error', 'message' => 'API key required', 'code' => 'MISSING_API_KEY'], 401);
        }

        $apiKeyModel = new TenantApiKey();
        $keyData = $apiKeyModel->validateKey($apiKey);

        if (!$keyData) {
            $this->json(['status' => 'error', 'message' => 'Invalid API key', 'code' => 'INVALID_API_KEY'], 401);
        }

        $this->tenantId = $keyData['tenant_id'];
        $this->apiKey = $keyData;

        // Increment API usage
        $usageModel = new TenantUsage();
        $usageModel->incrementApiCalls($this->tenantId, 1);
    }

    public function upsertContact() {
        if (!$this->request->isPost()) {
            $this->json(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->json(['status' => 'error', 'message' => 'Valid email required', 'code' => 'INVALID_EMAIL'], 400);
        }

        $contactModel = new Contact();
        $contactData = [
            'email' => $data['email'],
            'phone' => isset($data['phone']) ? $data['phone'] : null,
            'first_name' => isset($data['first_name']) ? $data['first_name'] : '',
            'last_name' => isset($data['last_name']) ? $data['last_name'] : '',
            'country' => isset($data['country']) ? $data['country'] : null,
            'tags' => isset($data['tags']) ? (is_array($data['tags']) ? implode(',', $data['tags']) : $data['tags']) : null,
            'status' => 'subscribed'
        ];

        // Handle custom attributes
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $contactData['attributes_json'] = json_encode($data['attributes']);
        }

        $contactId = $contactModel->upsert($contactData, $this->tenantId);

        // Add to lists
        if (isset($data['list_ids']) && is_array($data['list_ids'])) {
            $listContactModel = new ListContact();
            foreach ($data['list_ids'] as $listId) {
                $listContactModel->addContact($listId, $contactId, $this->tenantId);
            }
        }

        $contact = $contactModel->findById($contactId, $this->tenantId);

        $this->json([
            'status' => 'success',
            'data' => [
                'contact_id' => $contactId,
                'contact' => $contact
            ]
        ], 200);
    }

    public function trackEvent() {
        if (!$this->request->isPost()) {
            $this->json(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['event_name']) || !isset($data['contact_email'])) {
            $this->json(['status' => 'error', 'message' => 'Event name and contact email required'], 400);
        }

        $contactModel = new Contact();
        $contact = $contactModel->findByEmail($data['contact_email'], $this->tenantId);

        if (!$contact) {
            $this->json(['status' => 'error', 'message' => 'Contact not found', 'code' => 'CONTACT_NOT_FOUND'], 404);
        }

        // Log the event as activity
        $activityModel = new ActivityLog();
        $activityModel->log(
            $this->tenantId,
            null,
            'event',
            $contact['id'],
            $data['event_name'],
            'API event: ' . $data['event_name'] . ' - ' . json_encode($data['properties'] ?? [])
        );

        // Trigger automations based on event (simplified for this implementation)
        // In production, check for automation flows with trigger_type = 'event'

        $this->json([
            'status' => 'success',
            'message' => 'Event tracked successfully'
        ], 200);
    }

    public function createCampaign() {
        if (!$this->request->isPost()) {
            $this->json(['status' => 'error', 'message' => 'Method not allowed'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['name', 'channel', 'template_id'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->json(['status' => 'error', 'message' => "$field is required"], 400);
            }
        }

        $campaignModel = new Campaign();
        $campaignId = $campaignModel->create([
            'tenant_id' => $this->tenantId,
            'name' => $data['name'],
            'channel' => $data['channel'],
            'template_id' => $data['template_id'],
            'list_id' => isset($data['list_id']) ? $data['list_id'] : null,
            'segment_id' => isset($data['segment_id']) ? $data['segment_id'] : null,
            'from_name' => isset($data['from_name']) ? $data['from_name'] : 'SplashMarketingAI',
            'from_email' => isset($data['from_email']) ? $data['from_email'] : 'noreply@example.com',
            'subject_line' => isset($data['subject_line']) ? $data['subject_line'] : '',
            'status' => 'draft',
            'created_by_user_id' => null
        ]);

        $campaign = $campaignModel->findById($campaignId, $this->tenantId);

        $this->json([
            'status' => 'success',
            'data' => [
                'campaign_id' => $campaignId,
                'campaign' => $campaign
            ]
        ], 201);
    }

    public function getCampaignStats($id) {
        $campaignModel = new Campaign();
        $campaign = $campaignModel->findById($id, $this->tenantId);

        if (!$campaign) {
            $this->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $stats = $campaignModel->getStats($id, $this->tenantId);

        $this->json([
            'status' => 'success',
            'data' => [
                'campaign' => $campaign,
                'stats' => $stats
            ]
        ], 200);
    }
}
