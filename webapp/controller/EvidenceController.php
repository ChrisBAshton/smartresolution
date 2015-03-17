<?php

class EvidenceController {

    public function view ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        $evidences = Database::instance()->exec(
            'SELECT * FROM evidence WHERE dispute_id = :dispute_id ORDER BY evidence_id DESC',
            array(':dispute_id' => $dispute->getDisputeId())
        );

        foreach($evidences as $evidence) {
            echo "EVIDENCE: " . $evidence['filepath'] . "\n <br />";
        }

        $f3->set('content', 'evidence.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * Based on http://fatfreeframework.com/web#receive
     */
    public function upload ($f3, $params) {
        $account = mustBeLoggedIn();
        $dispute = setDisputeFromParams($f3, $params);

        $f3->set('UPLOADS', 'uploads/');

        $web = \Web::instance();
        $files = $web->receive(
            'EvidenceController->uploadFile', // callback
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
            if ($successfulUpload) {
                echo $filepath;
                DBL::createEvidence(array(
                    'uploader' => $account,
                    'dispute'  => $dispute,
                    'filepath' => $filepath
                ));
            }
            else {
                $f3->set('error_message', 'Could not upload file.');
            }
        }

        $f3->set('content', 'evidence_new.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * Callback function to the file upload.
     *
     * @param  File    $file          The file to be uploaded.
     * @param  String  $formFieldName The corresponding form name.
     * @return boolean                True if file upload was successful, otherwise false.
     */
    public function uploadFile ($file, $formFieldName) {

        /* $file looks like:
          array(5) {
              ["name"] =>     string(19) "csshat_quittung.png"
              ["type"] =>     string(9) "image/png"
              ["tmp_name"] => string(14) "/tmp/php2YS85Q"
              ["error"] =>    int(0)
              ["size"] =>     int(172245)
            }
        */

        // $file['name'] already contains the slugged name now

        // if bigger than 2 MB
        if ($file['size'] > (2 * 1024 * 1024)) {
            return false;
        }

        // type check

        return true;
    }

}
