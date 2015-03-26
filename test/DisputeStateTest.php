<?php
require_once __DIR__ . '/../webapp/autoload.php';

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
        $dispute = Utils::getDisputeByTitle('A simple test dispute');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeCreated);
    }

    public function testDisputeAssignedToLawFirmB() {
        $dispute = Utils::getDisputeByTitle('A dispute assigned to law firm B');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeAssignedToLawFirmB);
    }

    public function testDisputeOpened() {
        $dispute = Utils::getDisputeByTitle('A fully assigned dispute with no lifespan');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeOpened);

        $dispute = Utils::getDisputeByTitle('A dispute with a proposed lifespan');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeOpened);

        $dispute = Utils::getDisputeByTitle('A dispute with a declined lifespan');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeOpened);
    }

    public function testLifespanNegotiated() {
        $dispute = Utils::getDisputeByTitle('Smith versus Jones');
        $this->assertTrue($this->extractState($dispute) instanceof LifespanNegotiated);

        // until mediation is finalised, dispute should not be "in mediation"
        $dispute = Utils::getDisputeByTitle('Dispute that has agreed on a Mediation Centre');
        $this->assertTrue($this->extractState($dispute) instanceof LifespanNegotiated);
    }

    public function testDisputeInMediation() {
        $dispute = Utils::getDisputeByTitle('Dispute that is in mediation');
        $this->assertTrue($this->extractState($dispute) instanceof InMediation);
    }

    public function testDisputeClosed() {
        $dispute = Utils::getDisputeByTitle('A dispute that has ended');
        $this->assertTrue($this->extractState($dispute) instanceof DisputeClosed);
    }

    public function testStateUpdates() {
        $dispute = Utils::getDisputeByTitle('Smith versus Jones');
        $dispute->closeSuccessfully();
        $this->assertTrue($this->extractState($dispute) instanceof DisputeClosed);
    }

}
