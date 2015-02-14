<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class AccountDetailsTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        Database::setEnvironment('test');
        Database::clear();
    }

    // @TODO - need to extensively test the register() function

    public function testGetAccountDetails()
    {
        $testUser = AccountDetails::getAccountFromDatabase('test1@test.com');
        $this->assertEquals($testUser['login_id'], 1);
        $testUser = AccountDetails::getAccountFromDatabase('test2@test.com');
        $this->assertEquals($testUser['login_id'], 2);
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