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
        $agent   = AccountDetails::emailToId('agent_email');

        return Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    public function testCreateDisputeSuccessfully() {
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute instanceof Dispute);
    }

    public function testDisputeSimpleGetters() {
        $dispute = $this->createNewDispute();
        $this->assertEquals('Smith versus Jones', $dispute->getTitle());
    }

    public function testDisputeGettersIdsMatch() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertEquals(AccountDetails::emailToId('law_firm_email'), $dispute->getLawFirmAId());
        $this->assertEquals(AccountDetails::emailToId('agent_email'), $dispute->getAgentAId());
    }

    public function testDisputeGettersObjectsMatch() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertEquals(
            AccountDetails::getAccountFromDatabase('law_firm_email')->getLoginId(),
            $dispute->getLawFirmA()->getLoginId()
        );
        $this->assertEquals(
            AccountDetails::getAccountFromDatabase('agent_email')->getLoginId(),
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
        $this->assertEquals($lawFirmB, $dispute->getLawFirmBId());
        $this->assertTrue($dispute->hasBeenOpened());

        $agentB = AccountDetails::emailToId('another_agent_email');
        $dispute->setAgentB($agentB);
        $this->assertEquals($agentB, $dispute->getAgentBId());
        $this->assertFalse($dispute->waitingForLawFirmB());
    }

    public function testDisputeAuthorisation() {
        
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        $agent   = AccountDetails::emailToId('agent_email');

        $dispute = Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));

        $this->assertTrue($dispute->canBeViewedBy($lawFirm));
        $this->assertTrue($dispute->canBeViewedBy($agent));
        $this->assertFalse($dispute->canBeViewedBy(1337));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullLawFirm() {
        
        $agent   = AccountDetails::emailToId('agent_email');

        Dispute::create(array(
            'law_firm_a' => NULL,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullAgent() {
        
        $lawFirm = AccountDetails::emailToId('law_firm_email');

        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => NULL,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullType() {
        
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        $agent   = AccountDetails::emailToId('agent_email');

        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => NULL,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullTitle() {
        
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        $agent   = AccountDetails::emailToId('agent_email');

        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => NULL
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoLawFirm() {
        $agent   = AccountDetails::emailToId('agent_email');

        Dispute::create(array(
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoAgent() {
        $lawFirm = AccountDetails::emailToId('law_firm_email');

        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoType() {
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        $agent   = AccountDetails::emailToId('agent_email');
        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoTitle() {
        
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        $agent   = AccountDetails::emailToId('agent_email');

        Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other'
        ));
    }
}