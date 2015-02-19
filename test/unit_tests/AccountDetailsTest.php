<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class AccountDetailsTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        Database::setEnvironment('test');
        Database::clear();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithEmptyArray() {
        $agent = array();
        AccountDetails::register($agent);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithPasswordMissing() {
        $agent = array(
            'email' => 'test@test.com'
        );
        AccountDetails::register($agent);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The minimum required to register is an email and password!
     */
    public function testRegisterWithEmailMissing() {
        $agent = array(
            'password' => 'secret'
        );
        AccountDetails::register($agent);
    }

    public function testRegisterWithValidInputs() {
        $agent = array(
            'email'    => 'test@test.com',
            'password' => 'secret'
        );
        $loginID = AccountDetails::register($agent);
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
        AccountDetails::register($agent);
    }

    public function testValidCredentials() {
        $validCredentials = AccountDetails::validCredentials('law_firm_email', 'wrong password');
        $this->assertFalse($validCredentials);
        $validCredentials = AccountDetails::validCredentials('wrong email', 'wrong password');
        $this->assertFalse($validCredentials);
        $validCredentials = AccountDetails::validCredentials('law_firm_email', 'test');
        $this->assertTrue($validCredentials);
    }

    public function testGetIdFromEmail() {
        $testUser = AccountDetails::emailToId('law_firm_email');
        $this->assertEquals(1, $testUser);
    }

    public function testGetAccountDetailsIds()
    {
        $testUser = AccountDetails::getAccountFromDatabase('law_firm_email');
        $this->assertEquals('Webdapper Ltd', $testUser->getName());
        $testUser = AccountDetails::getAccountFromDatabase('agent_email');
        $this->assertEquals('Chris Ashton', $testUser->getName());
        $testUser = AccountDetails::getAccountFromDatabase('user_does_not_exist@t.co');
        $this->assertFalse($testUser);
    }

    // @TODO - when I add Mediator/Agent subclasses, these tests should still pass but
    // should also be extended.
    public function testGetAccountDetailsTypes()
    {
        $testUser = AccountDetails::getAccountFromDatabase('law_firm_email');
        $this->assertTrue($testUser instanceof Organisation);
        $testUser = AccountDetails::getAccountFromDatabase('agent_email');
        $this->assertTrue($testUser instanceof Individual);
    }

    public function testUserPasswordCheck()
    {
        $this->assertFalse(AccountDetails::correctPassword('test', 'test'));
        $this->assertFalse(AccountDetails::correctPassword('test', 'random string'));
        $this->assertTrue(AccountDetails::correctPassword('test', '$2y$10$md2.JKnCBFH5IGU9MeV50OUtx35VdVcThXeeQG9QUbpm9DwYmBlq.'));
    }
}