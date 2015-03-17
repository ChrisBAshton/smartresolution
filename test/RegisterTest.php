<?php
require_once __DIR__ . '/../webapp/autoload.php';

class RegisterTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public static function setUpBeforeClass()
    {
        Database::setEnvironment('test');
        Database::clear();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithEmptyArray() {
        $agent = array();
        DBL::createAccountDetails($agent);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithPasswordMissing() {
        $agent = array(
            'email' => 'test@test.com'
        );
        DBL::createAccountDetails($agent);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithEmailMissing() {
        $agent = array(
            'password' => 'secret'
        );
        DBL::createAccountDetails($agent);
    }

    public function testRegisterWithValidInputs() {
        $agent = array(
            'email'    => 'test@test.com',
            'password' => 'secret'
        );
        $loginID = DBL::createAccountDetails($agent);
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
        DBL::createAccountDetails($agent);
    }

    /**
     * This test should raise an exception because we're defining an invalid value for 'type'.
     * @expectedException PDOException
     */
    public function testTypeCheckConstraintForOrganisations()
    {
        DBL::createOrganisation(array(
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
        DBL::createIndividual(array(
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
        Database::setEnvironment('test');
        Database::clear();
        DBL::createOrganisation(array(
            'email'       => 'cba12@aber.ac.uk',
            'password'    => 'test',
            'type'        => 'law_firm',
            'name'        => 'Webdapper',
            'description' => 'A law firm'
        ));
    }

    public function testRegisterIndividual()
    {
        Database::setEnvironment('test');
        Database::clear();
        DBL::createIndividual(array(
            'email'           => 'cba12@aber.ac.uk',
            'password'        => 'test',
            'organisation_id' => 1,
            'type'            => 'agent',
            'surname'         => 'Ashton',
            'forename'        => 'Chris'
        ));
    }
}
?>
