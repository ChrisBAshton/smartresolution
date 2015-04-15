<?php

class Evidence {

    private $uploader;
    private $url;

    function __construct($evidenceID) {
        $evidence       = DBEvidence::getEvidence($evidenceID);
        $this->uploader = new Individual((int) $evidence['uploader_id']);
        $this->url      = $evidence['filepath'];
    }

    public function getUploader() {
        return $this->uploader;
    }

    public function getUrl() {
        return $this->url;
    }
}
