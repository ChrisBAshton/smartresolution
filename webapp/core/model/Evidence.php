<?php

/**
 * Evidence can be uploaded to a dispute. This class represents the item of evidence and knows where it can be found, who uploaded it, and so on.
 */
class Evidence {

    private $evidenceID;
    private $uploaderID;
    private $url;

    /**
     * Evidence constructor.
     * @param array  $evidence                The evidence details.
     *        int    $evidence['evidence_id'] The ID of the evidence.
     *        int    $evidence['uploader_id'] The login ID of the uploader.
     *        string $evidence['filepath']    The URL of the item of evidence.
     */
    function __construct($evidence) {
        $this->evidenceID = $evidence['evidence_id'];
        $this->uploaderID = $evidence['uploader_id'];
        $this->url        = $evidence['filepath'];
    }

    /**
     * Returns the ID of the evidence.
     * @return int ID
     */
    public function getEvidenceId() {
        return $this->evidenceID;
    }

    /**
     * Returns the ID of the uploader.
     * @return int Login ID of the uploader
     */
    public function getUploaderId() {
        return $this->uploaderID;
    }

    /**
     * Returns the account object representing the uploader.
     * @return Account Uploader.
     */
    public function getUploader() {
        return DBGet::instance()->account($this->getUploaderId());
    }

    /**
     * Returns the URL endpoint where the item of evidence can be viewed/downloaded.
     * @return string Evidence URL.
     */
    public function getUrl() {
        return $this->url;
    }
}
