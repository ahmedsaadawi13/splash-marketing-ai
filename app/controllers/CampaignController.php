<?php
// FILE: /app/controllers/CampaignController.php

class CampaignController extends Controller {
    public function index() {
        $this->requireAuth();

        $tenantId = $this->auth->tenantId();
        $page = $this->request->get('page', 1);

        $campaignModel = new Campaign();
        $result = $campaignModel->paginate($tenantId, $page, 20);

        $pagination = new PaginatorHelper(
            $result['total'],
            $result['perPage'],
            $result['page'],
            '/campaigns'
        );

        $this->view->render('campaigns/index', [
            'campaigns' => $result['data'],
            'pagination' => $pagination
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'marketer']);

        $tenantId = $this->auth->tenantId();

        $listModel = new ContactList();
        $lists = $listModel->all($tenantId);

        $segmentModel = new Segment();
        $segments = $segmentModel->all($tenantId);

        $templateModel = new Template();
        $templates = $templateModel->all($tenantId);

        $this->view->render('campaigns/create', [
            'lists' => $lists,
            'segments' => $segments,
            'templates' => $templates
        ]);
    }

    public function store() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'marketer']);
        CSRF::verify();

        $tenantId = $this->auth->tenantId();

        $validator = new ValidationHelper();
        $isValid = $validator->validate($this->request->post(), [
            'name' => 'required',
            'channel' => 'required|in:email,sms,social',
            'subject_line' => 'required'
        ]);

        if (!$isValid) {
            $this->session->setFlash('error', $validator->firstError());
            $this->redirect('/campaigns/create');
            return;
        }

        $campaignModel = new Campaign();
        $campaignId = $campaignModel->create([
            'tenant_id' => $tenantId,
            'name' => $this->request->post('name'),
            'channel' => $this->request->post('channel'),
            'template_id' => $this->request->post('template_id'),
            'list_id' => $this->request->post('list_id'),
            'segment_id' => $this->request->post('segment_id'),
            'from_name' => $this->request->post('from_name'),
            'from_email' => $this->request->post('from_email'),
            'subject_line' => $this->request->post('subject_line'),
            'status' => 'draft',
            'created_by_user_id' => $this->auth->id()
        ]);

        // Log activity
        $activityModel = new ActivityLog();
        $activityModel->log(
            $tenantId,
            $this->auth->id(),
            'campaign',
            $campaignId,
            'created',
            'Campaign created: ' . $this->request->post('name')
        );

        $this->session->setFlash('success', 'Campaign created successfully.');
        $this->redirect('/campaigns/' . $campaignId);
    }

    public function show($id) {
        $this->requireAuth();

        $tenantId = $this->auth->tenantId();
        $campaignModel = new Campaign();
        $campaign = $campaignModel->findById($id, $tenantId);

        if (!$campaign) {
            $this->response->notFound('Campaign not found');
        }

        // Get stats
        $stats = $campaignModel->getStats($id, $tenantId);

        // Get recipients sample
        $recipientModel = new CampaignRecipient();
        $recipients = $recipientModel->where(['campaign_id' => $id], $tenantId);

        $this->view->render('campaigns/show', [
            'campaign' => $campaign,
            'stats' => $stats,
            'recipients' => array_slice($recipients, 0, 100)
        ]);
    }

    public function send($id) {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'marketer']);
        CSRF::verify();

        $tenantId = $this->auth->tenantId();
        $campaignModel = new Campaign();
        $campaign = $campaignModel->findById($id, $tenantId);

        if (!$campaign || $campaign['status'] !== 'draft') {
            $this->session->setFlash('error', 'Campaign cannot be sent.');
            $this->redirect('/campaigns/' . $id);
            return;
        }

        // Check quota
        $quotaCheck = $this->checkQuota('sends');
        if (!$quotaCheck['allowed']) {
            $this->session->setFlash('error', $quotaCheck['message']);
            $this->redirect('/campaigns/' . $id);
            return;
        }

        // Get recipients
        $contacts = [];
        if ($campaign['list_id']) {
            $contactModel = new Contact();
            $contacts = $contactModel->getByList($campaign['list_id'], $tenantId);
        } elseif ($campaign['segment_id']) {
            $segmentModel = new Segment();
            $contacts = $segmentModel->getContacts($campaign['segment_id'], $tenantId);
        }

        if (empty($contacts)) {
            $this->session->setFlash('error', 'No recipients found.');
            $this->redirect('/campaigns/' . $id);
            return;
        }

        // Create campaign recipients
        $recipientModel = new CampaignRecipient();
        $recipientModel->createRecipients($id, $contacts, $tenantId, $campaign['channel']);

        // Update campaign status
        $campaignModel->update($id, [
            'status' => 'sending',
            'total_recipients' => count($contacts)
        ], $tenantId);

        // Process sending (in production, this would be a queue job)
        $this->processSending($id, $campaign, $tenantId);

        $this->session->setFlash('success', 'Campaign sent to ' . count($contacts) . ' recipients.');
        $this->redirect('/campaigns/' . $id);
    }

    private function processSending($campaignId, $campaign, $tenantId) {
        $recipientModel = new CampaignRecipient();
        $recipients = $recipientModel->getPending($campaignId, $tenantId, 1000);

        $templateModel = new Template();
        $template = $templateModel->findById($campaign['template_id'], $tenantId);

        if (!$template) {
            return;
        }

        $sent = 0;

        foreach ($recipients as $recipient) {
            if ($campaign['channel'] === 'email') {
                $mailer = new MailerHelper();

                // Get full contact data
                $contactModel = new Contact();
                $contact = $contactModel->findById($recipient['contact_id'], $tenantId);

                // Personalize content
                $personalizedSubject = $mailer->replaceVariables($campaign['subject_line'], $contact);
                $personalizedHtml = $mailer->replaceVariables($template['body_html'], $contact);
                $personalizedText = $mailer->replaceVariables($template['body_text'], $contact);

                // Add tracking
                $personalizedHtml = $mailer->injectTrackingPixel($personalizedHtml, $recipient['id']);
                $personalizedHtml = $mailer->wrapLinksForTracking($personalizedHtml, $recipient['id']);

                $result = $mailer->send(
                    $recipient['email'],
                    $personalizedSubject,
                    $personalizedHtml,
                    $personalizedText,
                    $campaign['from_email'],
                    $campaign['from_name']
                );

                if ($result['success']) {
                    $recipientModel->updateStatus($recipient['id'], 'sent', $tenantId);
                    $sent++;
                } else {
                    $recipientModel->updateStatus($recipient['id'], 'bounced', $tenantId);
                }
            } elseif ($campaign['channel'] === 'sms') {
                $sms = new SmsHelper();

                $contactModel = new Contact();
                $contact = $contactModel->findById($recipient['contact_id'], $tenantId);

                $personalizedMessage = $sms->replaceVariables($template['body_text'], $contact);

                $result = $sms->send($recipient['phone'], $personalizedMessage);

                if ($result['success']) {
                    $recipientModel->updateStatus($recipient['id'], 'sent', $tenantId);
                    $sent++;
                } else {
                    $recipientModel->updateStatus($recipient['id'], 'bounced', $tenantId);
                }
            }
        }

        // Update campaign status
        $campaignModel = new Campaign();
        $campaignModel->updateStatus($campaignId, 'sent', $tenantId);

        // Update usage
        $usageModel = new TenantUsage();
        $usageModel->incrementSends($tenantId, $sent);
    }
}
