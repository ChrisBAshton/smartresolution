<?php

class Evidence {

    private $evidenceID;
    private $uploaderID;
    private $url;

    function __construct($evidence) {
        $this->evidenceID = $evidence['evidence_id'];
        $this->uploaderID = $evidence['uploader_id'];
        $this->url        = $evidence['filepath'];
    }

    public function getUploaderId() {
        return $this->uploaderID;
    }

    public function getUploader() {
        return DBAccount::instance()->getAccountById($this->getUploaderId());
    }

    public function getUrl() {
        return $this->url;
    }
}
