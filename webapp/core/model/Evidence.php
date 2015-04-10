<?php

class Evidence {

    private $uploader;
    private $url;

    function __construct($evidenceID) {
        $evidence = Database::instance()->exec(
            'SELECT * FROM evidence WHERE evidence_id = :evidence_id LIMIT 1',
            array(':evidence_id' => $evidenceID)
        );

        if (count($evidence) !== 1) {
            throw new Exception('Evidence does not exist.');
        }
        else {
            $evidence       = $evidence[0];
            $this->uploader = new Individual((int) $evidence['uploader_id']);
            $this->url      = $evidence['filepath'];
        }
    }

    public function getUploader() {
        return $this->uploader;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getDispute() {
        //return $this->dispute;
    }
}
