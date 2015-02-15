<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class AccountDetailsTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        Database::setEnvironment('test');
        Database::clear();
    }

    // @TODO - need to extensively test the register() function

    public function testGetIdFromEmail() {
        $testUser = AccountDetails::emailToId('law_firm_email');
        $this->assertEquals(1, $testUser);
    }

    public function testGetAccountDetails()
    {
        $testUser = AccountDetails::getAccountFromDatabase('law_firm_email');
        $this->assertEquals(1, $testUser['login_id']);
        $testUser = AccountDetails::getAccountFromDatabase('mediation_centre_email');
        $this->assertEquals(2, $testUser['login_id']);
        $testUser = AccountDetails::getAccountFromDatabase('user_does_not_exist@t.co');
        $this->assertFalse($testUser);
    }

    public function testUserPasswordCheck()
    {
        $this->assertFalse(AccountDetails::correctPassword('test', 'test'));
        $this->assertFalse(AccountDetails::correctPassword('test', 'random string'));
        $this->assertTrue(AccountDetails::correctPassword('test', '$2y$10$md2.JKnCBFH5IGU9MeV50OUtx35VdVcThXeeQG9QUbpm9DwYmBlq.'));
    }
}