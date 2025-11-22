<?php
// FILE: /app/controllers/TrackingController.php

class TrackingController extends Controller {
    public function trackOpen() {
        $recipientId = $this->request->get('rid');

        if ($recipientId) {
            $recipientModel = new CampaignRecipient();
            $recipient = $recipientModel->findById($recipientId);

            if ($recipient) {
                $recipientModel->recordOpen($recipientId, $recipient['tenant_id']);

                // Update contact engagement
                $contactModel = new Contact();
                $contactModel->updateEngagement($recipient['contact_id'], 'open', $recipient['tenant_id']);
            }
        }

        // Return 1x1 transparent pixel
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }

    public function trackClick() {
        $recipientId = $this->request->get('rid');
        $url = $this->request->get('url');

        if ($recipientId) {
            $recipientModel = new CampaignRecipient();
            $recipient = $recipientModel->findById($recipientId);

            if ($recipient) {
                $recipientModel->recordClick($recipientId, $recipient['tenant_id']);

                // Update contact engagement
                $contactModel = new Contact();
                $contactModel->updateEngagement($recipient['contact_id'], 'click', $recipient['tenant_id']);
            }
        }

        // Redirect to original URL
        if ($url) {
            $this->redirect($url);
        } else {
            echo 'Invalid link';
        }
    }
}
