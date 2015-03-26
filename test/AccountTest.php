<?php
require_once __DIR__ . '/../webapp/autoload.php';

class AccountTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testCommonGetters() {
        $account = AccountDetails::getAccountByEmail('agent_a@t.co');

        $this->assertTrue(is_int($account->getLoginId()));
        $this->assertEquals('agent_a@t.co', $account->getEmail());
        $this->assertEquals('Chris Ashton', $account->getName());
        $this->assertTrue(is_array($account->getNotifications()));
        $this->assertTrue(is_array($account->getAllDisputes()));
        $this->assertEquals('/accounts/' . $account->getLoginId(), $account->getUrl());

        $account = AccountDetails::getAccountByEmail('law_firm_a@t.co');

        $this->assertTrue(is_int($account->getLoginId()));
        $this->assertEquals('law_firm_a@t.co', $account->getEmail());
        $this->assertEquals('Webdapper Ltd', $account->getName());
        $this->assertTrue(is_array($account->getNotifications()));
        $this->assertTrue(is_array($account->getAllDisputes()));
        $this->assertEquals('/accounts/' . $account->getLoginId(), $account->getUrl());
    }

    public function testIndividualGetters() {
        $account = AccountDetails::getAccountByEmail('agent_a@t.co');
        $this->assertEquals('Webdapper Ltd', $account->getOrganisation()->getName());
    }

    public function testOrganisationGetters() {
        $account = AccountDetails::getAccountByEmail('law_firm_a@t.co');
        $this->assertTrue(is_array($account->getIndividuals('Agent')));
        $this->assertEquals('Chris Ashton', $account->getIndividuals('Agent')[0]->getName());
    }

}
