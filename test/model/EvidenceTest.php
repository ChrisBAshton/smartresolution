<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class EvidenceTest extends PHPUnit_Framework_TestCase
{

    public function testEvidence()
    {
        $evidence = new Evidence(array(
            'evidence_id' => 1,
            'uploader_id' => 3,
            'filepath'    => '/some/filepath/test.txt'
        ));

        $this->assertEquals(1, $evidence->getEvidenceId());
        $this->assertEquals(3, $evidence->getUploaderId());
        $this->assertTrue($evidence->getUploader() instanceof Agent);
        $this->assertEquals('/some/filepath/test.txt', $evidence->getUrl());
    }

}