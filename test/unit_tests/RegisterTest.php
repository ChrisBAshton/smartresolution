<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class RegisterTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
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
        Register::accountDetails($agent);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithPasswordMissing() {
        $agent = array(
            'email' => 'test@test.com'
        );
        Register::accountDetails($agent);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithEmailMissing() {
        $agent = array(
            'password' => 'secret'
        );
        Register::accountDetails($agent);
    }

    public function testRegisterWithValidInputs() {
        $agent = array(
            'email'    => 'test@test.com',
            'password' => 'secret'
        );
        $loginID = Register::accountDetails($agent);
        $this->assertTrue(is_int($loginID));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage An account is already registered to that email address.
     */
    public function testRegisterWithExistingEmail() {
        $agent = array(
            'email'    => 'law_firm_email',
            'password' => 'secret'
        );
        Register::accountDetails($agent);
    }

    /**
     * This test should raise an exception because we're defining an invalid value for 'type'.
     * @expectedException PDOException
     */
    public function testTypeCheckConstraintForOrganisations()
    {
        Register::organisation(array(
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
        Register::individual(array(
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
        Register::organisation(array(
            'email'       => 'cba12@aber.ac.uk',
            'password'    => 'test',
            'type'        => 'law_firm',
            'name'        => 'Webdapper',
            'description' => 'A law firm'
        ));
    }

    public function testRegisterIndividual()
    {
        Register::individual(array(
            'email'           => 'cba12@aber.ac.uk',
            'password'        => 'test',
            'organisation_id' => 1,
            'type'            => 'agent',
            'surname'         => 'Ashton',
            'forename'        => 'Chris'
        ));
    }

    public function testGetValueFunction()
    {
        $testArray = array(
            'exists' => 'this value exists'
        );

        $result = Register::getValue($testArray, 'exists', 'default value');
        $this->assertEquals('this value exists', $result);

        $result = Register::getValue($testArray, 'does not exist', 'default value');
        $this->assertEquals('default value', $result);
    }

    /**
     * This test should raise an exception because we're trying to retrieve a value
     * that MUST exist, and it does not.
     * @expectedException Exception
     */
    public function testGetValueFunctionExceptionRaising()
    {
        $testArray = array();
        $result = Register::getValue($testArray, 'this index must exist or else we raise an exception');
    }
}
?>