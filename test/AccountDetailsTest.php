<?php
require_once __DIR__ . '/../webapp/autoload.php';

class AccountDetailsTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testValidCredentials() {
        $validCredentials = AccountDetails::validCredentials('law_firm_a@t.co', 'wrong password');
        $this->assertFalse($validCredentials);
        $validCredentials = AccountDetails::validCredentials('wrong email', 'wrong password');
        $this->assertFalse($validCredentials);
        $validCredentials = AccountDetails::validCredentials('law_firm_a@t.co', 'test');
        $this->assertTrue($validCredentials);
    }

    public function testGetIdFromEmail() {
        $testUser = AccountDetails::emailToId('law_firm_a@t.co');
        $this->assertEquals(1, $testUser);
    }

    public function testGetAccountDetailsIds()
    {
        $testUser = AccountDetails::getAccountByEmail('law_firm_a@t.co');
        $this->assertEquals('Webdapper Ltd', $testUser->getName());
        $testUser = AccountDetails::getAccountByEmail('agent_a@t.co');
        $this->assertEquals('Chris Ashton', $testUser->getName());
        $testUser = AccountDetails::getAccountByEmail('user_does_not_exist@t.co');
        $this->assertFalse($testUser);
    }

    public function testGetAccountDetailsTypes()
    {
        $testUser = AccountDetails::getAccountByEmail('law_firm_a@t.co');
        $this->assertTrue($testUser instanceof Organisation);
        $this->assertTrue($testUser instanceof LawFirm);
        $this->assertFalse($testUser instanceof Individual);
        $this->assertFalse($testUser instanceof MediationCentre);
        $testUser = AccountDetails::getAccountByEmail('mediation_centre_email@we-mediate.co.uk');
        $this->assertTrue($testUser instanceof Organisation);
        $this->assertTrue($testUser instanceof MediationCentre);
        $this->assertFalse($testUser instanceof Individual);
        $this->assertFalse($testUser instanceof LawFirm);
        $testUser = AccountDetails::getAccountByEmail('agent_a@t.co');
        $this->assertTrue($testUser instanceof Individual);
        $this->assertTrue($testUser instanceof Agent);
        $this->assertFalse($testUser instanceof Organisation);
        $this->assertFalse($testUser instanceof Mediator);
        $testUser = AccountDetails::getAccountByEmail('john.smith@we-mediate.co.uk');
        $this->assertTrue($testUser instanceof Individual);
        $this->assertTrue($testUser instanceof Mediator);
        $this->assertFalse($testUser instanceof Organisation);
        $this->assertFalse($testUser instanceof Agent);
    }

    public function testUserPasswordCheck()
    {
        $this->assertFalse(AccountDetails::correctPassword('test', 'test'));
        $this->assertFalse(AccountDetails::correctPassword('test', 'random string'));
        $this->assertTrue(AccountDetails::correctPassword('test', '$2y$10$md2.JKnCBFH5IGU9MeV50OUtx35VdVcThXeeQG9QUbpm9DwYmBlq.'));
    }
}