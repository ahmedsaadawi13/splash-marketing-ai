<?php
// FILE: /app/controllers/ContactController.php

class ContactController extends Controller {
    public function index() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'marketer']);

        $tenantId = $this->auth->tenantId();
        $page = $this->request->get('page', 1);
        $search = $this->request->get('search');

        $contactModel = new Contact();

        if ($search) {
            $contacts = $contactModel->search($tenantId, $search);
            $pagination = null;
        } else {
            $result = $contactModel->paginate($tenantId, $page, 50, 'id', 'DESC');
            $contacts = $result['data'];
            $pagination = new PaginatorHelper(
                $result['total'],
                $result['perPage'],
                $result['page'],
                '/contacts'
            );
        }

        $this->view->render('contacts/index', [
            'contacts' => $contacts,
            'pagination' => $pagination,
            'search' => $search
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'marketer']);

        $listModel = new ContactList();
        $lists = $listModel->all($this->auth->tenantId());

        $this->view->render('contacts/create', [
            'lists' => $lists
        ]);
    }

    public function store() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'marketer']);
        CSRF::verify();

        $tenantId = $this->auth->tenantId();

        // Check quota
        $quotaCheck = $this->checkQuota('contacts');
        if (!$quotaCheck['allowed']) {
            $this->session->setFlash('error', $quotaCheck['message']);
            $this->redirect('/contacts/create');
            return;
        }

        $validator = new ValidationHelper();
        $isValid = $validator->validate($this->request->post(), [
            'email' => 'required|email',
            'first_name' => 'required',
            'status' => 'in:subscribed,unsubscribed'
        ]);

        if (!$isValid) {
            $this->session->setFlash('error', $validator->firstError());
            $this->redirect('/contacts/create');
            return;
        }

        $contactModel = new Contact();
        $contactData = [
            'email' => $this->request->post('email'),
            'phone' => $this->request->post('phone'),
            'first_name' => $this->request->post('first_name'),
            'last_name' => $this->request->post('last_name'),
            'country' => $this->request->post('country'),
            'status' => $this->request->post('status', 'subscribed'),
            'tags' => $this->request->post('tags')
        ];

        $contactId = $contactModel->upsert($contactData, $tenantId);

        // Add to lists
        $listIds = $this->request->post('list_ids', []);
        if (!empty($listIds)) {
            $listContactModel = new ListContact();
            foreach ($listIds as $listId) {
                $listContactModel->addContact($listId, $contactId, $tenantId);
            }
        }

        // Update usage
        $usageModel = new TenantUsage();
        $usageModel->incrementContacts($tenantId, 1);

        // Log activity
        $activityModel = new ActivityLog();
        $activityModel->log(
            $tenantId,
            $this->auth->id(),
            'contact',
            $contactId,
            'created',
            'Contact created: ' . $contactData['email']
        );

        $this->session->setFlash('success', 'Contact created successfully.');
        $this->redirect('/contacts');
    }

    public function import() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'marketer']);

        $listModel = new ContactList();
        $lists = $listModel->all($this->auth->tenantId());

        $this->view->render('contacts/import', [
            'lists' => $lists
        ]);
    }

    public function processImport() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'marketer']);
        CSRF::verify();

        $tenantId = $this->auth->tenantId();

        if (!$this->request->hasFile('csv_file')) {
            $this->session->setFlash('error', 'Please select a CSV file.');
            $this->redirect('/contacts/import');
            return;
        }

        $fileUploader = new FileUploadHelper();
        $uploadResult = $fileUploader->uploadCsv($this->request->file('csv_file'));

        if (!$uploadResult) {
            $this->session->setFlash('error', $fileUploader->getFirstError());
            $this->redirect('/contacts/import');
            return;
        }

        // Process CSV
        $csvPath = __DIR__ . '/../../storage/uploads/' . $uploadResult['path'];
        $handle = fopen($csvPath, 'r');

        if (!$handle) {
            $this->session->setFlash('error', 'Failed to open CSV file.');
            $this->redirect('/contacts/import');
            return;
        }

        $headers = fgetcsv($handle);
        $imported = 0;
        $updated = 0;
        $errors = 0;

        $contactModel = new Contact();
        $listId = $this->request->post('list_id');

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($headers)) {
                $errors++;
                continue;
            }

            $data = array_combine($headers, $row);

            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors++;
                continue;
            }

            $existing = $contactModel->findByEmail($data['email'], $tenantId);
            $contactId = $contactModel->upsert($data, $tenantId);

            if ($existing) {
                $updated++;
            } else {
                $imported++;
            }

            // Add to list
            if ($listId) {
                $listContactModel = new ListContact();
                $listContactModel->addContact($listId, $contactId, $tenantId);
            }
        }

        fclose($handle);

        // Update usage
        $usageModel = new TenantUsage();
        $usageModel->incrementContacts($tenantId, $imported);

        $this->session->setFlash('success', "Import complete: $imported created, $updated updated, $errors errors.");
        $this->redirect('/contacts');
    }

    public function export() {
        $this->requireAuth();

        $tenantId = $this->auth->tenantId();
        $contactModel = new Contact();
        $contacts = $contactModel->all($tenantId);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="contacts_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Email', 'First Name', 'Last Name', 'Phone', 'Country', 'Status', 'Tags', 'Created At']);

        foreach ($contacts as $contact) {
            fputcsv($output, [
                $contact['email'],
                $contact['first_name'],
                $contact['last_name'],
                $contact['phone'],
                $contact['country'],
                $contact['status'],
                $contact['tags'],
                $contact['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
