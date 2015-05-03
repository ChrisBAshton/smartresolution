<?php

/**
 * Links HTTP requests to view items of evidence with evidence-related functions and views.
 */
class EvidenceController {

    /**
     * View the list of evidence for the given dispute. If logged in account is not authorised, error page is raised.
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function view ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        if (!$dispute->getState()->canViewDocuments()) {
            errorPage('You are not allowed to view these documents.');
        }

        $f3->set('evidences', $dispute->getEvidences());
        $f3->set('content', 'evidence.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * POST function; uploads an item of evidence (a file from the user's computer to the SmartResolution installation).
     * Based on http://fatfreeframework.com/web#receive
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /disputes/@disputeID => $params['disputeID'] => 1337
     */
    public function upload ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        if (!$dispute->getState()->canUploadDocuments()) {
            errorPage('You cannot upload any documents at this stage of the dispute lifecycle.');
        }

        $f3->set('UPLOADS', 'uploads/');

        $web = \Web::instance();
        $files = $web->receive(
            'EvidenceController->validate', // callback
            false,        // overwrite - true or false
            function($fileBaseName, $formFieldName) {
                return strtolower(time() . '__' . $fileBaseName);
            }
        );

        /* $files looks like:
          array(3) {
              ["uploads/csshat_quittung.png"] => bool(true)
              ["uploads/foo.pdf"] => bool(false)
              ["uploads/my.pdf"] => bool(true)
            }
          foo.pdf was not uploaded...
        */

        foreach($files as $filepath => $successfulUpload) {
            $dbEntryCreated = false;

            if ($successfulUpload) {
                $dbEntryCreated = DBCreate::instance()->evidence(array(
                    'uploader_id' => $account->getLoginId(),
                    'dispute_id'  => $dispute->getDisputeId(),
                    'filepath'    => $filepath
                ));
            }

            if ($dbEntryCreated) {
                $f3->set('success_message', 'File uploaded.');
            }
            else {
                $f3->set('error_message', 'Could not upload file.');
            }
        }

        $f3->set('content', 'evidence_new.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * Callback function to the file upload, validating that the file is the right type and size, etc.
     *
     * @param  File    $file          The file to be uploaded.
     * @param  string  $formFieldName The corresponding form name.
     * @return boolean                True if file upload was successful, otherwise false.
     */
    public function validate ($file, $formFieldName) {

        /* $file looks like:
          array(5) {
              ["name"] =>     string(19) "csshat_quittung.png" // $file['name'] already contains the slugged name now
              ["type"] =>     string(9) "image/png"
              ["tmp_name"] => string(14) "/tmp/php2YS85Q"
              ["error"] =>    int(0)
              ["size"] =>     int(172245)
            }
        */

        $maxFileSize = (2 * 1024 * 1024); // 2MB
        if ($file['size'] > $maxFileSize) {
            return false;
        }

        // @TODO - type check

        return true;
    }

}
