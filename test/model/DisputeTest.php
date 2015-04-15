<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DisputeTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    private function createNewDispute() {
        $lawFirm = DBAccount::emailToId('law_firm_a@t.co');
        $agent = DBAccount::emailToId('agent_a@t.co');

        return DBCreate::dispute(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones',
            'summary'    => 'This is my summary'
        ));
    }

    public function testCreateDisputeSuccessfully() {
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute instanceof Dispute);
    }

    public function testOverridingAgentWithAnotherFromSameLawFirm() {
        $dispute = $this->createNewDispute();
        $agent1 = DBAccount::emailToId('agent_a@t.co');
        $agent2 = DBAccount::emailToId('agent_c@t.co');

        $this->assertEquals($agent1, $dispute->getAgentA()->getLoginId());
        $dispute->setAgentA($agent2);
        $this->assertEquals($agent2, $dispute->getAgentA()->getLoginId());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Tried setting a non-agent type as an agent!
     */
    public function testOverridingAgentWithMediator() {
        $dispute  = $this->createNewDispute();
        $agent    = DBAccount::emailToId('agent_a@t.co');
        $mediator = DBAccount::emailToId('john.smith@we-mediate.co.uk');

        $this->assertEquals($agent, $dispute->getAgentA()->getLoginId());
        $dispute->setAgentA($mediator);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You can only assign agents that are in your law firm!
     */
    public function testOverridingAgentWithAgentFromDifferentLawFirm() {
        $dispute = $this->createNewDispute();
        $agentInCompanyA = DBAccount::emailToId('agent_a@t.co');
        $agentInCompanyB = DBAccount::emailToId('agent_b@t.co');

        $this->assertEquals($agentInCompanyA, $dispute->getAgentA()->getLoginId());
        $dispute->setAgentA($agentInCompanyB);
    }

    public function testDisputeSimpleGetters() {
        $dispute = $this->createNewDispute();
        $this->assertEquals(DBAccount::emailToId('agent_a@t.co'), $dispute->getAgentA()->getLoginId());
        $this->assertEquals(DBAccount::emailToId('law_firm_a@t.co'), $dispute->getLawFirmA()->getLoginId());
        $this->assertEquals('Smith versus Jones', $dispute->getTitle());
        $this->assertEquals('This is my summary', $dispute->getSummaryFromPartyA());
    }

    public function testRoundTableCommunication() {
        $dispute = $this->createNewDispute(); // by default, should NOT be in round table communication
        $this->assertFalse($dispute->inRoundTableCommunication());

        $dispute->enableRoundTableCommunication();
        $this->assertTrue($dispute->inRoundTableCommunication());

        $dispute->disableRoundTableCommunication();
        $this->assertFalse($dispute->inRoundTableCommunication());
    }

    public function testIsAMediationParty() {
        $dispute = $this->createNewDispute();

        // first, let's set up the second party
        $dispute->setLawFirmB(DBAccount::emailToId('law_firm_b@t.co'));
        $dispute->setAgentB(DBAccount::emailToId('agent_b@t.co'));

        // next, let's set up Mediation
        DBCreate::mediationCentreOffer(array(
            'dispute'          => $dispute,
            'proposed_by'      => $dispute->getAgentA(),
            'mediation_centre' => DBAccount::getAccountByEmail('mediation_centre_email@we-mediate.co.uk')
        ));
        $dispute->refresh();
        $dispute->getMediationState()->acceptLatestProposal();
        DBCreate::mediatorOffer(array(
            'dispute'     => $dispute,
            'proposed_by' => $dispute->getAgentA(),
            'mediator'    => DBAccount::getAccountByEmail('john.smith@we-mediate.co.uk')
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

    public function testDisputeGettersObjectsMatch() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertEquals(
            DBAccount::getAccountByEmail('law_firm_a@t.co')->getLoginId(),
            $dispute->getLawFirmA()->getLoginId()
        );
        $this->assertEquals(
            DBAccount::getAccountByEmail('agent_a@t.co')->getLoginId(),
            $dispute->getAgentA()->getLoginId()
        );
    }

    public function testDisputeGettersObjectsCorrectType() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute->getLawFirmA() instanceof LawFirm);
        $this->assertTrue($dispute->getAgentA() instanceof Agent);
    }

    public function testAuthorisationLogicIsCorrect() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute->canBeViewedBy(DBAccount::emailToId('law_firm_a@t.co')));
        $this->assertTrue($dispute->canBeViewedBy(DBAccount::emailToId('agent_a@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(DBAccount::emailToId('law_firm_b@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(DBAccount::emailToId('john.smith@we-mediate.co.uk')));
    }

    public function testDisputeWorkflowCorrect() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $state   = $dispute->getState(DBAccount::getAccountByEmail('agent_a@t.co'));
        $this->assertTrue($state->canOpenDispute());

        $lawFirmB = DBAccount::emailToId('law_firm_b@t.co');
        $dispute->setLawFirmB($lawFirmB);
        $this->assertEquals($lawFirmB, $dispute->getLawFirmB()->getLoginId());

        $agentB = DBAccount::emailToId('agent_b@t.co');
        $dispute->setAgentB($agentB);
        $this->assertEquals($agentB, $dispute->getAgentB()->getLoginId());
        $this->assertFalse($state->canOpenDispute());

        $this->assertFalse($dispute->getSummaryFromPartyB());
        $dispute->setSummaryForPartyB('Test summary');
        $this->assertEquals('Test summary', $dispute->getSummaryFromPartyB());
    }

    public function testGetOpposingPartyId() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $lawFirmA = DBAccount::emailToId('law_firm_a@t.co');
        $agentA   = DBAccount::emailToId('agent_a@t.co');
        $lawFirmB = DBAccount::emailToId('law_firm_b@t.co');
        $agentB   = DBAccount::emailToId('agent_b@t.co');
        $dispute->setLawFirmB($lawFirmB);
        $dispute->setAgentB($agentB);

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

        $dispute->setAgentB($lawFirmB); // shouldn't be able to set agent as a law firm
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
        $dispute = $this->createNewDispute();
        $this->assertEquals("ongoing", $this->getDisputeStatus($dispute));
        $dispute->closeUnsuccessfully();
        $this->assertEquals("failed", $this->getDisputeStatus($dispute));
    }

    public function testGetAndSetType() {
        $dispute = $this->createNewDispute();
        $this->assertEquals("other", $dispute->getType());
        $dispute->setType('test');
        $this->assertEquals("test", $dispute->getType());
    }
}
