<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class DisputeTest extends PHPUnit_Framework_TestCase
{

    public function testIsAMediationParty()
    {
        $dispute = TestHelper::getDisputeByTitle('Dispute that is in mediation');
        $mediationCentre = DBAccount::instance()->emailToId('mediation_centre_email@we-mediate.co.uk');
        $mediator = DBAccount::instance()->emailToId('john.smith@we-mediate.co.uk');

        // the mediation centre and mediator associated with the dispute should be
        // classed as being part of the 'mediation party'
        $this->assertTrue($dispute->isAMediationParty($mediationCentre));
        $this->assertTrue($dispute->isAMediationParty($mediator));

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
            $this->assertFalse($dispute->isAMediationParty(DBAccount::instance()->emailToId($email)));
        }
    }

    public function testCreateDisputeSuccessfully()
    {
        $dispute = TestHelper::createNewDispute();
        $this->assertTrue($dispute instanceof Dispute);
    }

    public function testDisputeSimpleGetters()
    {
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals('Smith versus Jones', $dispute->getTitle());
    }

    public function testRoundTableCommunication()
    {
        $dispute = TestHelper::createNewDispute(); // by default, should NOT be in round table communication
        $this->assertFalse($dispute->inRoundTableCommunication());

        $dispute->enableRoundTableCommunication();
        $this->assertTrue($dispute->inRoundTableCommunication());

        $dispute->disableRoundTableCommunication();
        $this->assertFalse($dispute->inRoundTableCommunication());
    }

    public function testAuthorisationLogicIsCorrect()
    {
        $dispute = TestHelper::createNewDispute();
        $this->assertTrue($dispute->canBeViewedBy(DBAccount::instance()->emailToId('law_firm_a@t.co')));
        $this->assertTrue($dispute->canBeViewedBy(DBAccount::instance()->emailToId('agent_a@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(DBAccount::instance()->emailToId('law_firm_b@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(DBAccount::instance()->emailToId('john.smith@we-mediate.co.uk')));
    }

    public function testDisputeWorkflowCorrect()
    {
        $dispute  = TestHelper::createNewDispute();
        $state    = $dispute->getState(DBAccount::instance()->getAccountByEmail('agent_a@t.co'));
        $lawFirmB = DBAccount::instance()->emailToId('law_firm_b@t.co');
        $agentB   = DBAccount::instance()->emailToId('agent_b@t.co');

        // dispute should be able to be opened against a law firm - we've only just created it
        $this->assertTrue($state->canOpenDispute());

        $dispute->getPartyB()->setLawFirm($lawFirmB);
        $dispute->getPartyB()->setAgent($agentB);
        // now that we've opened the dispute against a law firm, it shouldn't be openable anymore
        $this->assertFalse($state->canOpenDispute());
    }

    public function testGetOpposingPartyId()
    {
        $dispute = TestHelper::createNewDispute();
        $lawFirmA = DBAccount::instance()->emailToId('law_firm_a@t.co');
        $agentA   = DBAccount::instance()->emailToId('agent_a@t.co');
        $lawFirmB = DBAccount::instance()->emailToId('law_firm_b@t.co');
        $agentB   = DBAccount::instance()->emailToId('agent_b@t.co');
        $dispute->getPartyB()->setLawFirm($lawFirmB);
        $dispute->getPartyB()->setAgent($agentB);

        $this->assertEquals($agentB, $dispute->getOpposingPartyId($agentA));
        $this->assertEquals($agentA, $dispute->getOpposingPartyId($agentB));
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeAgentToLawFirm()
    {
        $agentA   = DBAccount::instance()->emailToId('agent_a@t.co');

        return DBCreate::instance()->dispute(array(
            'law_firm_a' => $agentA, // shouldn't be able to set law firm as an agent
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeLawFirmToAgent()
    {
        $lawFirmA = DBAccount::instance()->emailToId('law_firm_a@t.co');
        $lawFirmB = DBAccount::instance()->emailToId('law_firm_b@t.co');

        $dispute = DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirmA,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));

        $dispute->getPartyB()->setAgent($lawFirmB); // shouldn't be able to set agent as a law firm
    }

    public function testGetAndSetType()
    {
        $dispute = TestHelper::createNewDispute();
        $this->assertEquals("other", $dispute->getType());
        $dispute->setType('test');
        $this->assertEquals("test", $dispute->getType());
    }
}
