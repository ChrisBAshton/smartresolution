<?php
require_once __DIR__ . '/../../webapp/autoload.php';
require_once __DIR__ . '/../_helper.php';

class DBCreateTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testCreateAdmin()
    {
        $create = DBCreate::instance();
        $admin = $create->admin(array(
            'email'    => 'admin@t.co',
            'password' => 'test'
        ));
        $this->assertTrue($admin instanceof Admin);
    }

    public function testCreateLawFirm()
    {
        $create  = DBCreate::instance();
        $lawFirm = $create->organisation(array(
            'email'    => 'law_firm@t.co',
            'password' => 'test',
            'type'     => 'law_firm'
        ));
        $this->assertTrue($lawFirm instanceof LawFirm);
    }

    public function testCreateLawFirmWithExtraFields()
    {
        $create  = DBCreate::instance();
        $lawFirm = $create->organisation(array(
            'email'       => 'some_law_firm_a@t.co',
            'password'    => 'test',
            'type'        => 'law_firm',
            'name'        => 'Test Name',
            'description' => 'This is my description'
        ));
        $this->assertTrue($lawFirm instanceof LawFirm);
        $this->assertEquals($lawFirm->getName(), 'Test Name');
    }

    public function testCreateDispute() {
        $create  = DBCreate::instance();
        $lawFirm = DBQuery::instance()->emailToId('law_firm_a@t.co');
        $dispute = DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
        $this->assertTrue($dispute instanceof Dispute);
    }

    /**
     * @expectedException Exception
     */
    public function testSettingDisputeAgentToLawFirm()
    {
        $agentA   = DBQuery::instance()->emailToId('agent_a@t.co');

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
        $lawFirmA = DBQuery::instance()->emailToId('law_firm_a@t.co');
        $lawFirmB = DBQuery::instance()->emailToId('law_firm_b@t.co');

        $dispute = DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirmA,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));

        $dispute->getPartyB()->setAgent($lawFirmB); // shouldn't be able to set agent as a law firm
    }

    public function testCreateDisputeParty() {
        $create    = DBCreate::instance();
        $lawFirmID = DBQuery::instance()->emailToId('law_firm_a@t.co');
        $agentID   = DBQuery::instance()->emailToId('agent_a@t.co');
        $summary   = 'My summary';
        $disputeParty1 = DBCreate::instance()->disputeParty(array(
            'organisation_id' => $lawFirmID,
            'individual_id'   => $agentID,
            'summary'         => $summary
        ));
        $disputeParty2 = DBCreate::instance()->disputeParty(array(
            'organisation_id' => $lawFirmID,
            'individual_id'   => $agentID
        ));
        $disputeParty3 = DBCreate::instance()->disputeParty(array(
            'organisation_id' => $lawFirmID
        ));
        $this->assertTrue($disputeParty1 instanceof DisputeParty);
        $this->assertTrue($disputeParty2 instanceof DisputeParty);
        $this->assertTrue($disputeParty3 instanceof DisputeParty);
    }

    public function testCreateEvidence() {
        $create   = DBCreate::instance();
        $evidence = $create->evidence(array(
            'dispute_id'  => 3,
            'uploader_id' => 4,
            'filepath'    => 'some/path/file.txt'
        ));
        $this->assertTrue($evidence instanceof Evidence);
    }

    public function testCreateLifespan() {
        $currentTime = time();
        DBCreate::instance()->lifespan(array(
            'dispute_id'  => TestHelper::getDisputeByTitle('Smith versus Jones')->getDisputeId(),
            'proposer'    => DBQuery::instance()->emailToId('agent_a@t.co'),
            'valid_until' => $currentTime + 3600,
            'start_time'  => $currentTime + 7200,
            'end_time'    => $currentTime + 12000
        ));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithEmptyArray() {
        $agent = array();
        DBCreate::instance()->dbAccount($agent);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithPasswordMissing() {
        $agent = array(
            'email' => 'test@test.com'
        );
        DBCreate::instance()->dbAccount($agent);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithEmailMissing() {
        $agent = array(
            'password' => 'secret'
        );
        DBCreate::instance()->dbAccount($agent);
    }

    public function testRegisterWithValidInputs() {
        $agent = array(
            'email'    => 'test@test.com',
            'password' => 'secret'
        );
        $loginID = DBCreate::instance()->dbAccount($agent);
        $this->assertTrue(is_int($loginID));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage An account is already registered to that email address.
     */
    public function testRegisterWithExistingEmail() {
        $agent = array(
            'email'    => 'law_firm_a@t.co',
            'password' => 'secret'
        );
        DBCreate::instance()->dbAccount($agent);
    }

    /**
     * This test should raise an exception because we're defining an invalid value for 'type'.
     * @expectedException PDOException
     */
    public function testTypeCheckConstraintForOrganisations()
    {
        DBCreate::instance()->organisation(array(
            'email'       => 'valid_email@email.co.uk',
            'password'    => 'test',
            'type'        => 'invalid type',
            'name'        => 'A name',
            'description' => 'A description'
        ));
    }

    /**
     * This test should raise an exception because we're defining an invalid value for 'type'.
     * @expectedException PDOException
     */
    public function testTypeCheckConstraintForIndividuals()
    {
        DBCreate::instance()->individual(array(
            'email'           => 'valid_email@email.co.uk',
            'password'        => 'test',
            'organisation_id' => 1,
            'type'            => 'invalid type',
            'surname'         => 'A surname',
            'forename'        => 'A forename'
        ));
    }

    public function testRegisterOrganisation()
    {
        // @TODO. At the moment, we need to reset the database because a transaction
        // doesn't seem to be committed (get the 'There is already an active transaction' error
        // if we do not clear the database in this connection).
        // Look at Utils::throwException and see if we can apply the same principle elsewhere.
        DBCreateTest::setUpBeforeClass();
        DBCreate::instance()->organisation(array(
            'email'       => 'cba12@aber.ac.uk',
            'password'    => 'test',
            'type'        => 'law_firm',
            'name'        => 'Webdapper',
            'description' => 'A law firm'
        ));
    }

    public function testRegisterIndividual()
    {
        DBCreateTest::setUpBeforeClass();
        DBCreate::instance()->individual(array(
            'email'           => 'cba13@aber.ac.uk',
            'password'        => 'test',
            'organisation_id' => 1,
            'type'            => 'agent',
            'surname'         => 'Ashton',
            'forename'        => 'Chris'
        ));
    }


    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullLawFirm() {
        DBCreate::instance()->dispute(array(
            'law_firm_a' => NULL,
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullType() {

        $lawFirm = DBQuery::instance()->emailToId('law_firm_a@t.co');

        DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => NULL,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNullTitle() {

        $lawFirm = DBQuery::instance()->emailToId('law_firm_a@t.co');

        DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other',
            'title'      => NULL
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoLawFirm() {
        DBCreate::instance()->dispute(array(
            'type'       => 'other',
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoType() {
        $lawFirm = DBQuery::instance()->emailToId('law_firm_a@t.co');
        DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirm,
            'title'      => 'Smith versus Jones'
        ));
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDisputeFailsWhenNoTitle() {
        $lawFirm = DBQuery::instance()->emailToId('law_firm_a@t.co');
        DBCreate::instance()->dispute(array(
            'law_firm_a' => $lawFirm,
            'type'       => 'other'
        ));
    }
}
