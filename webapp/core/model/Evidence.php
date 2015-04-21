<?php

class Evidence {

    private $uploader;
    private $url;

    function __construct($evidenceID) {
        $evidence       = DBGet::instance()->evidence($evidenceID);
        $this->uploader = DBAccount::instance()->getAccountById((int) $evidence['uploader_id']);
        $this->url      = $evidence['filepath'];
    }

    public function getUploader() {
        return $this->uploader;
    }

    public function getUrl() {
        return $this->url;
    }
}
