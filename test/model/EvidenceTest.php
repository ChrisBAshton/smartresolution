<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class EvidenceTest extends PHPUnit_Framework_TestCase
{

    protected function setUp() {
        $this->dispute = TestHelper::getDisputeByTitle('Smith versus Jones');
        $this->evidence = DBCreate::instance()->evidence(array(
            'uploader_id' => $this->dispute->getPartyA()->getAgent()->getLoginId(),
            'dispute_id'  => $this->dispute->getDisputeId(),
            'filepath'    => 'test_filepath'
        ));
    }

    public function testGetters() {
        $this->assertEquals(
            $this->dispute->getPartyA()->getAgent()->getLoginId(),
            $this->evidence->getUploader()->getLoginId(),
            'Login ID was ' . $this->dispute->getPartyA()->getAgent()->getLoginId()
        );
        $this->assertEquals(
            'test_filepath',
            $this->evidence->getUrl()
        );
    }

}
