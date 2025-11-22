<?php
// FILE: /app/models/Segment.php

class Segment extends Model {
    protected $table = 'segments';

    public function getContactCount($segmentId, $tenantId) {
        $segmentHelper = new SegmentHelper();
        return $segmentHelper->countSegmentContacts($tenantId, $segmentId);
    }

    public function getContacts($segmentId, $tenantId, $limit = null, $offset = null) {
        $segmentHelper = new SegmentHelper();
        return $segmentHelper->getSegmentContacts($tenantId, $segmentId, $limit, $offset);
    }
}
