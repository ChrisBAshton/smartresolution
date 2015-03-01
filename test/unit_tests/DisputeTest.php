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

        $dispute = Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));

        $agent = AccountDetails::emailToId('agent_email');
        $dispute->partyA()->setAgent($agent);

        return $dispute;
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
        $this->assertEquals(AccountDetails::emailToId('law_firm_email'), $dispute->partyA()->getLawFirm()->getLoginId());
        $this->assertEquals(AccountDetails::emailToId('agent_email'),    $dispute->partyA()->getAgent()->getLoginId());
    }

    public function testDisputeGettersObjectsMatch() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertEquals(
            AccountDetails::getAccountByEmail('law_firm_email')->getLoginId(),
            $dispute->partyA()->getLawFirm()->getLoginId()
        );
        $this->assertEquals(
            AccountDetails::getAccountByEmail('agent_email')->getLoginId(),
            $dispute->partyA()->getAgent()->getLoginId()
        );
    }

    public function testDisputeGettersObjectsCorrectType() {
        DisputeTest::setUpBeforeClass();
        $dispute = $this->createNewDispute();
        $this->assertTrue($dispute->partyA()->getLawFirm() instanceof LawFirm);
        $this->assertTrue($dispute->partyA()->getAgent() instanceof Agent);
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
        $dispute->setPartyB($lawFirmB);
        $this->assertEquals($lawFirmB, $dispute->partyB()->getLawFirm()->getLoginId());
        $this->assertTrue($dispute->hasBeenOpened());

        $agentB = AccountDetails::emailToId('another_agent_email');
        $dispute->partyB()->setAgent($agentB);
        $this->assertEquals($agentB, $dispute->partyB()->getAgent()->getLoginId());
        $this->assertFalse($dispute->waitingForLawFirmB());
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

        $dispute->partyA()->setAgent($lawFirmB); // shouldn't be able to set agent as a law firm
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