<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DisputeTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testCreateDisputeSuccessfully() {
        
        $lawFirm = AccountDetails::emailToId('law_firm_email');
        $agent   = AccountDetails::emailToId('agent_email');

        $dispute = Dispute::create(array(
            'law_firm_a' => $lawFirm,
            'agent_a'    => $agent,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));

        $this->assertTrue($dispute instanceof Dispute);
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