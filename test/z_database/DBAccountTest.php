<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DBAccountTest extends PHPUnit_Framework_TestCase
{
    public function testValidCredentials()
    {
        $validCredentials = DBAccount::instance()->validCredentials('law_firm_a@t.co', 'wrong password');
        $this->assertFalse($validCredentials);
        $validCredentials = DBAccount::instance()->validCredentials('wrong email', 'wrong password');
        $this->assertFalse($validCredentials);
        $validCredentials = DBAccount::instance()->validCredentials('law_firm_a@t.co', 'test');
        $this->assertTrue($validCredentials);
    }

    public function testGetDBAccountIds()
    {
        $testUser = TestHelper::getAccountByEmail('law_firm_a@t.co');
        $this->assertEquals('Webdapper Ltd', $testUser->getName());
        $testUser = TestHelper::getAccountByEmail('agent_a@t.co');
        $this->assertEquals('Chris Ashton', $testUser->getName());
        $testUser = TestHelper::getAccountByEmail('user_does_not_exist@t.co');
        $this->assertFalse($testUser);
    }

    public function testGetDBAccountTypes()
    {
        $testUser = TestHelper::getAccountByEmail('law_firm_a@t.co');
        $this->assertEquals('Law Firm', $testUser->getRole());
        $this->assertFalse($testUser instanceof Admin);
        $this->assertTrue($testUser instanceof Organisation);
        $this->assertTrue($testUser instanceof LawFirm);
        $this->assertFalse($testUser instanceof Individual);
        $this->assertFalse($testUser instanceof MediationCentre);
        $testUser = TestHelper::getAccountByEmail('mediation_centre_email@we-mediate.co.uk');
        $this->assertEquals('Mediation Centre', $testUser->getRole());
        $this->assertFalse($testUser instanceof Admin);
        $this->assertTrue($testUser instanceof Organisation);
        $this->assertTrue($testUser instanceof MediationCentre);
        $this->assertFalse($testUser instanceof Individual);
        $this->assertFalse($testUser instanceof LawFirm);
        $testUser = TestHelper::getAccountByEmail('agent_a@t.co');
        $this->assertEquals('Agent', $testUser->getRole());
        $this->assertFalse($testUser instanceof Admin);
        $this->assertTrue($testUser instanceof Individual);
        $this->assertTrue($testUser instanceof Agent);
        $this->assertFalse($testUser instanceof Organisation);
        $this->assertFalse($testUser instanceof Mediator);
        $testUser = TestHelper::getAccountByEmail('john.smith@we-mediate.co.uk');
        $this->assertEquals('Mediator', $testUser->getRole());
        $this->assertFalse($testUser instanceof Admin);
        $this->assertTrue($testUser instanceof Individual);
        $this->assertTrue($testUser instanceof Mediator);
        $this->assertFalse($testUser instanceof Organisation);
        $this->assertFalse($testUser instanceof Agent);
        $testUser = TestHelper::getAccountByEmail('admin@smartresolution.org');
        $this->assertEquals('Administrator', $testUser->getRole());
        $this->assertTrue($testUser instanceof Admin);
        $this->assertFalse($testUser instanceof Individual);
        $this->assertFalse($testUser instanceof Mediator);
        $this->assertFalse($testUser instanceof Organisation);
        $this->assertFalse($testUser instanceof Agent);
    }

    public function testUserPasswordCheck()
    {
        $this->assertFalse(DBAccount::instance()->correctPassword('test', 'test'));
        $this->assertFalse(DBAccount::instance()->correctPassword('test', 'random string'));
        $this->assertTrue(DBAccount::instance()->correctPassword('test', '$2y$10$md2.JKnCBFH5IGU9MeV50OUtx35VdVcThXeeQG9QUbpm9DwYmBlq.'));
    }
}
