<?php
require_once __DIR__ . '/../webapp/autoload.php';

class EvidenceTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    protected function setUp() {
        $this->dispute = Utils::getDisputeByTitle('Smith versus Jones');
        $evidenceID = DBL::createEvidence(array(
            'uploader' => $this->dispute->getAgentA(),
            'dispute'  => $this->dispute,
            'filepath' => 'test_filepath'
        ));
        $this->evidence = new Evidence($evidenceID);
    }

    public function testGetters() {
        $this->assertEquals(
            $this->dispute->getAgentA()->getLoginId(),
            $this->evidence->getUploader()->getLoginId()
        );
        $this->assertEquals(
            'test_filepath',
            $this->evidence->getUrl()
        );
    }

}
