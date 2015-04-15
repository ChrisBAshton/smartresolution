<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class DisputeTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testCreateDisputeSuccessfully() {
        $dispute = TestHelper::createNewDispute();
        $this->assertTrue($dispute instanceof Dispute);
    }

    public function testDisputeSimpleGetters() {
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals('Smith versus Jones', $dispute->getTitle());
    }

    public function testRoundTableCommunication() {
        $dispute = TestHelper::createNewDispute(); // by default, should NOT be in round table communication
        $this->assertFalse($dispute->inRoundTableCommunication());

        $dispute->enableRoundTableCommunication();
        $this->assertTrue($dispute->inRoundTableCommunication());

        $dispute->disableRoundTableCommunication();
        $this->assertFalse($dispute->inRoundTableCommunication());
    }

    public function testIsAMediationParty() {
        $dispute = TestHelper::createNewDispute();

        // first, let's set up the second party
        $dispute->getPartyB()->setLawFirm(DBAccount::emailToId('law_firm_b@t.co'));
        $dispute->getPartyB()->setAgent(DBAccount::emailToId('agent_b@t.co'));

        // next, let's set up Mediation
        DBCreate::mediationCentreOffer(array(
            'dispute_id'  => $dispute->getDisputeId(),
            'proposer_id' => $dispute->getPartyA()->getAgent()->getLoginId(),
            'proposed_id' => DBAccount::getAccountByEmail('mediation_centre_email@we-mediate.co.uk')->getLoginId()
        ));
        $dispute->refresh();
        $dispute->getMediationState()->acceptLatestProposal();
        DBCreate::mediatorOffer(array(
            'dispute_id'  => $dispute->getDisputeId(),
            'proposer_id' => $dispute->getPartyA()->getAgent()->getLoginId(),
            'proposed_id' => DBAccount::getAccountByEmail('john.smith@we-mediate.co.uk')->getLoginId()
        ));
        $dispute->refresh();
        $dispute->getMediationState()->acceptLatestProposal();

        // now we can test the mediation
        $this->assertTrue($dispute->isAMediationParty(
            DBAccount::emailToId('mediation_centre_email@we-mediate.co.uk')
        ));
        $this->assertTrue($dispute->isAMediationParty(
            DBAccount::emailToId('john.smith@we-mediate.co.uk')
        ));

        $shouldNotPass = array(
            // agents and law_firms should not ever pass
            'agent_a@t.co',
            'agent_b@t.co',
            'law_firm_a@t.co',
            'law_firm_b@t.co',
            // other mediation centres and mediators should also not pass
            'we@also-mediate.co',
            'tim@also-mediate.co'
        );

        foreach($shouldNotPass as $email) {
            $this->assertFalse($dispute->isAMediationParty(DBAccount::emailToId($email)));
        }
    }

    public function testAuthorisationLogicIsCorrect() {
        DisputeTest::setUpBeforeClass();
        $dispute = TestHelper::createNewDispute();
        $this->assertTrue($dispute->canBeViewedBy(DBAccount::emailToId('law_firm_a@t.co')));
        $this->assertTrue($dispute->canBeViewedBy(DBAccount::emailToId('agent_a@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(DBAccount::emailToId('law_firm_b@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(DBAccount::emailToId('john.smith@we-mediate.co.uk')));
    }

    public function testDisputeWorkflowCorrect() {
        DisputeTest::setUpBeforeClass();
        $dispute = TestHelper::createNewDispute();
        $state   = $dispute->getState(DBAccount::getAccountByEmail('agent_a@t.co'));
        $this->assertTrue($state->canOpenDispute());

        $lawFirmB = DBAccount::emailToId('law_firm_b@t.co');
        $dispute->getPartyB()->setLawFirm($lawFirmB);
        $this->assertEquals($lawFirmB, $dispute->getPartyB()->getLawFirm()->getLoginId());

        $agentB = DBAccount::emailToId('agent_b@t.co');
        $dispute->getPartyB()->setAgent($agentB);
        $this->assertEquals($agentB, $dispute->getPartyB()->getAgent()->getLoginId());
        $this->assertFalse($state->canOpenDispute());

        $this->assertFalse($dispute->getPartyB()->getSummary());
        $dispute->getPartyB()->setSummary('Test summary');
        $this->assertEquals('Test summary', $dispute->getPartyB()->getSummary());
    }

    public function testGetOpposingPartyId() {
        DisputeTest::setUpBeforeClass();
        $dispute = TestHelper::createNewDispute();
        $lawFirmA = DBAccount::emailToId('law_firm_a@t.co');
        $agentA   = DBAccount::emailToId('agent_a@t.co');
        $lawFirmB = DBAccount::emailToId('law_firm_b@t.co');
        $agentB   = DBAccount::emailToId('agent_b@t.co');
        $dispute->getPartyB()->setLawFirm($lawFirmB);
        $dispute->getPartyB()->setAgent($agentB);

        $this->assertEquals($agentB, $dispute->getOpposingPartyId($agentA));
        $this->assertEquals($agentA, $dispute->getOpposingPartyId($agentB));
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeAgentToLawFirm() {
        $agentA   = DBAccount::emailToId('agent_a@t.co');

        return DBCreate::dispute(array(
            'law_firm_a' => $agentA, // shouldn't be able to set law firm as an agent
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeLawFirmToAgent() {
        $lawFirmA = DBAccount::emailToId('law_firm_a@t.co');
        $lawFirmB = DBAccount::emailToId('law_firm_b@t.co');

        $dispute = DBCreate::dispute(array(
            'law_firm_a' => $lawFirmA,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));

        $dispute->getPartyB()->setAgent($lawFirmB); // shouldn't be able to set agent as a law firm
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullLawFirm() {
        DBCreate::dispute(array(
            'law_firm_a' => NULL,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullType() {

        $lawFirm = DBAccount::emailToId('law_firm_a@t.co');

        DBCreate::dispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => NULL,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullTitle() {

        $lawFirm = DBAccount::emailToId('law_firm_a@t.co');

        DBCreate::dispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other',
            'title'      => NULL
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoLawFirm() {
        DBCreate::dispute(array(
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoType() {
        $lawFirm = DBAccount::emailToId('law_firm_a@t.co');
        DBCreate::dispute(array(
            'law_firm_a' => $lawFirm,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoTitle() {
        $lawFirm = DBAccount::emailToId('law_firm_a@t.co');
        DBCreate::dispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other'
        ));
    }

    private function getDisputeStatus($dispute) {
        return Database::instance()->exec(
            'SELECT status FROM disputes WHERE dispute_id = :dispute_id',
            array(':dispute_id' => $dispute->getDisputeId())
        )[0]['status'];
    }

    public function testCloseUnsuccessfully() {
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals("ongoing", $this->getDisputeStatus($dispute));
        $dispute->closeUnsuccessfully();
        $this->assertEquals("failed", $this->getDisputeStatus($dispute));
    }

    public function testGetAndSetType() {
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals("other", $dispute->getType());
        $dispute->setType('test');
        $this->assertEquals("test", $dispute->getType());
    }
}
