<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DisputeTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    private function createNewDispute() {
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        $agent = AccountDetails::emailToId('agent_email');
        
        return Dispute::create(array(
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
        $agent1 = AccountDetails::emailToId('agent_email');
        $agent2 = AccountDetails::emailToId('another_agent_email');

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
        $agent    = AccountDetails::emailToId('agent_email');
        $mediator = AccountDetails::emailToId('mediator_email');

        $this->assertEquals($agent, $dispute->getAgentA()->getLoginId());
        $dispute->setAgentA($mediator);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You can only assign agents that are in your law firm!
     */
    public function testOverridingAgentWithAgentFromDifferentLawFirm() {
        $dispute = $this->createNewDispute();
        $agentInCompanyA = AccountDetails::emailToId('agent_email');
        $agentInCompanyB = AccountDetails::emailToId('agent_b');

        $this->assertEquals($agentInCompanyA, $dispute->getAgentA()->getLoginId());
        $dispute->setAgentA($agentInCompanyB);
    }

    public function testDisputeSimpleGetters() {
        $dispute = $this->createNewDispute();
        $this->assertEquals(AccountDetails::emailToId('agent_email'), $dispute->getAgentA()->getLoginId());
        $this->assertEquals(AccountDetails::emailToId('law_firm_email'), $dispute->getLawFirmA()->getLoginId());
        $this->assertEquals('Smith versus Jones', $dispute->getTitle());
        $this->assertEquals('This is my summary', $dispute->getSummaryFromPartyA());
    }

    public function testDisputeGettersObjectsMatch() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertEquals(
            AccountDetails::getAccountByEmail('law_firm_email')->getLoginId(),
            $dispute->getLawFirmA()->getLoginId()
        );
        $this->assertEquals(
            AccountDetails::getAccountByEmail('agent_email')->getLoginId(),
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
        $this->assertTrue($dispute->canBeViewedBy(AccountDetails::emailToId('law_firm_email')));
        $this->assertTrue($dispute->canBeViewedBy(AccountDetails::emailToId('agent_email')));
        $this->assertFalse($dispute->canBeViewedBy(AccountDetails::emailToId('another_law_firm_email')));
        $this->assertFalse($dispute->canBeViewedBy(AccountDetails::emailToId('mediator_email')));
    }

    public function testDisputeWorkflowCorrect() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute->waitingForLawFirmB());
        $this->assertTrue($dispute->hasNotBeenOpened());

        $lawFirmB = AccountDetails::emailToId('another_law_firm_email');
        $dispute->setLawFirmB($lawFirmB);
        $this->assertEquals($lawFirmB, $dispute->getLawFirmB()->getLoginId());
        $this->assertTrue($dispute->hasBeenOpened());

        $agentB = AccountDetails::emailToId('agent_b');
        $dispute->setAgentB($agentB);
        $this->assertEquals($agentB, $dispute->getAgentB()->getLoginId());
        $this->assertFalse($dispute->waitingForLawFirmB());

        $this->assertFalse($dispute->getSummaryFromPartyB());
        $dispute->setSummaryForPartyB('Test summary');
        $this->assertEquals('Test summary', $dispute->getSummaryFromPartyB());
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeAgentToLawFirm() {
        $agentA   = AccountDetails::emailToId('agent_email');

        return Dispute::create(array(
            'law_firm_a' => $agentA, // shouldn't be able to set law firm as an agent
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeLawFirmToAgent() {
        $lawFirmA = AccountDetails::emailToId('law_firm_email');
        $lawFirmB = AccountDetails::emailToId('another_law_firm_email');

        $dispute = Dispute::create(array(
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
        Dispute::create(array(
            'law_firm_a' => NULL,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullType() {
        
        $lawFirm = AccountDetails::emailToId('law_firm_email');

        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'type'       => NULL,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullTitle() {
        
        $lawFirm = AccountDetails::emailToId('law_firm_email');

        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other',
            'title'      => NULL
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoLawFirm() {
        Dispute::create(array(
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoType() {
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoTitle() {
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other'
        ));
    }
}