<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class DisputeTest extends PHPUnit_Framework_TestCase
{

    private function createSimpleDispute()
    {
        return new Dispute(array(
            'dispute_id' => 1337,
            'title'      => 'Title of my dispute',
            'type'       => 'other',
            'status'     => 'ongoing',
            'party_a'    => false,
            'party_b'    => false,
            'round_table_communication' => false
        ));
    }

    public function testSimpleGetters()
    {
        $dispute = $this->createSimpleDispute();
        $this->assertEquals(1337, $dispute->getDisputeId());
        $this->assertEquals('/disputes/1337', $dispute->getUrl());
        $this->assertEquals('other', $dispute->getType());
        $this->assertEquals('ongoing', $dispute->getStatus());
        $this->assertEquals('Title of my dispute', $dispute->getTitle());
        $this->assertFalse($dispute->inRoundTableCommunication());
        $this->assertEquals(array(), $dispute->getMessages());
        $this->assertEquals(array(), $dispute->getEvidences());
        $this->assertFalse($dispute->getPartyA()->getLawFirm());
        $this->assertFalse($dispute->getPartyB()->getLawFirm());
        // we expect a mock lifespan object because no lifespan has been created yet
        $this->assertTrue($dispute->getCurrentLifespan() instanceof LifespanMock);
        $this->assertTrue($dispute->getLatestLifespan() instanceof LifespanMock);
    }

    public function testSimpleSetters()
    {
        $dispute = $this->createSimpleDispute();
        // dispute type
        $dispute->setType('test');
        $this->assertEquals('test', $dispute->getType());

        // dispute status
        $dispute->closeSuccessfully();
        $this->assertEquals('resolved', $dispute->getStatus());
        $dispute->closeUnsuccessfully();
        $this->assertEquals('failed', $dispute->getStatus());

        // round table communication status
        $dispute->enableRoundTableCommunication();
        $this->assertTrue($dispute->inRoundTableCommunication());
        $dispute->disableRoundTableCommunication();
        $this->assertFalse($dispute->inRoundTableCommunication());
    }

    // sanity check for our TestHelper function
    public function testCreateDisputeSuccessfully()
    {
        $dispute = TestHelper::createNewDispute();
        $this->assertTrue($dispute instanceof Dispute);
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
        $this->assertTrue($dispute->canBeViewedBy(DBQuery::instance()->emailToId('law_firm_a@t.co')));
        $this->assertTrue($dispute->canBeViewedBy(DBQuery::instance()->emailToId('agent_a@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(DBQuery::instance()->emailToId('law_firm_b@t.co')));
        $this->assertFalse($dispute->canBeViewedBy(DBQuery::instance()->emailToId('john.smith@we-mediate.co.uk')));
    }

    public function testDisputeWorkflowCorrect()
    {
        $dispute  = TestHelper::createNewDispute();
        $state    = $dispute->getState(TestHelper::getAccountByEmail('agent_a@t.co'));
        $lawFirmB = DBQuery::instance()->emailToId('law_firm_b@t.co');
        $agentB   = DBQuery::instance()->emailToId('agent_b@t.co');

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
        $lawFirmA = DBQuery::instance()->emailToId('law_firm_a@t.co');
        $agentA   = DBQuery::instance()->emailToId('agent_a@t.co');
        $lawFirmB = DBQuery::instance()->emailToId('law_firm_b@t.co');
        $agentB   = DBQuery::instance()->emailToId('agent_b@t.co');
        $dispute->getPartyB()->setLawFirm($lawFirmB);
        $dispute->getPartyB()->setAgent($agentB);

        $this->assertEquals($agentB, $dispute->getOpposingPartyId($agentA));
        $this->assertEquals($agentA, $dispute->getOpposingPartyId($agentB));
    }

    public function testIsAMediationParty()
    {
        $dispute = TestHelper::getDisputeByTitle('Dispute that is in mediation');
        $mediationCentre = DBQuery::instance()->emailToId('mediation_centre_email@we-mediate.co.uk');
        $mediator = DBQuery::instance()->emailToId('john.smith@we-mediate.co.uk');

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
            $this->assertFalse($dispute->isAMediationParty(DBQuery::instance()->emailToId($email)));
        }
    }

}