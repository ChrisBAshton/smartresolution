<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class MediationStateTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testWhenNotInMediation() {
        $dispute = TestHelper::getDisputeByTitle('Smith versus Jones');
        $state = $dispute->getMediationState();

        $this->assertFalse($state->mediationCentreProposed());
        $this->assertFalse($state->mediatorProposed());
        $this->assertFalse($state->mediationCentreDecided());
        $this->assertFalse($state->mediatorDecided());
        $this->assertFalse($state->inMediation());
    }

    public function testWhenInMediation() {
        $dispute = TestHelper::getDisputeByTitle('Dispute that is in mediation');
        $state = $dispute->getMediationState();

        $this->assertTrue($state->mediationCentreProposed());
        $this->assertTrue($state->mediatorProposed());
        $this->assertTrue($state->mediationCentreDecided());
        $this->assertTrue($state->mediatorDecided());
        $this->assertTrue($state->inMediation());
    }

    public function testWhenMediationProposed() {
        $dispute = TestHelper::getDisputeByTitle('Dispute that has agreed on a Mediation Centre');
        $state = $dispute->getMediationState();

        $this->assertTrue($state->mediationCentreProposed());
        $this->assertTrue($state->mediationCentreDecided());
        $this->assertFalse($state->mediatorProposed());
        $this->assertFalse($state->mediatorDecided());
        $this->assertFalse($state->inMediation());
    }

    public function testGetters() {
        $dispute = TestHelper::getDisputeByTitle('Dispute that is in mediation');
        $state = $dispute->getMediationState();
        $this->assertTrue($state->getMediationCentre()  instanceof MediationCentre);
        $this->assertTrue($state->getMediator()         instanceof Mediator);
        $this->assertTrue($state->getMediatorProposer() instanceof Agent);
    }

}
