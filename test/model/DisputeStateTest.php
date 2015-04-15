<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class DisputeStateTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    private function extractState($dispute) {
        $accountContext = $dispute->getAgentA();
        return $dispute->getState($accountContext);
    }

    public function testDisputeCreated() {
        $dispute = TestHelper::getDisputeByTitle('A simple test dispute');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeCreated);
    }

    public function testDisputeAssignedToLawFirmB() {
        $dispute = TestHelper::getDisputeByTitle('A dispute assigned to law firm B');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeAssignedToLawFirmB);
    }

    public function testDisputeOpened() {
        $dispute = TestHelper::getDisputeByTitle('A fully assigned dispute with no lifespan');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeOpened);

        $dispute = TestHelper::getDisputeByTitle('A dispute with a proposed lifespan');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeOpened);

        $dispute = TestHelper::getDisputeByTitle('A dispute with a declined lifespan');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeOpened);
    }

    public function testLifespanNegotiated() {
        $dispute = TestHelper::getDisputeByTitle('Smith versus Jones');
        $this->assertTrue($this->extractState($dispute) instanceof LifespanNegotiated);

        // until mediation is finalised, dispute should not be "in mediation"
        $dispute = TestHelper::getDisputeByTitle('Dispute that has agreed on a Mediation Centre');
        $this->assertTrue($this->extractState($dispute) instanceof LifespanNegotiated);
    }

    public function testDisputeInMediation() {
        $dispute = TestHelper::getDisputeByTitle('Dispute that is in mediation');
        $this->assertTrue($this->extractState($dispute) instanceof InMediation);
    }

    public function testDisputeInRoundTableMediation() {
        $dispute = TestHelper::getDisputeByTitle('Dispute that is in mediation');
        $dispute->enableRoundTableCommunication();
        $this->assertTrue($this->extractState($dispute) instanceof InRoundTableMediation);
    }

    public function testDisputeClosed() {
        $dispute = TestHelper::getDisputeByTitle('A dispute that has ended');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeClosed);
    }

    public function testStateUpdates() {
        $dispute = TestHelper::getDisputeByTitle('Smith versus Jones');
        $dispute->closeSuccessfully();
        $this->assertTrue($this->extractState($dispute) instanceof DisputeClosed);
    }

}
