<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class AccountTest extends PHPUnit_Framework_TestCase
{

    private function createAgent() {
        return new Agent(array(
            'login_id'        => 1,
            'organisation_id' => 3,
            'email'           => 'agent_a@t.co',
            'forename'        => 'John',
            'surname'         => 'Smith',
            'cv'              => false
        ));
    }

    private function createLawFirm() {
        return new LawFirm(array(
            'login_id'        => 3,
            'email'           => 'law_firm_a@t.co',
            'name'            => 'Webdapper Ltd',
            'description'     => false
        ));
    }

    public function testIndividualGetters() {
        $account = $this->createAgent();
        $this->assertEquals(1, $account->getLoginId());
        $this->assertEquals('agent_a@t.co', $account->getEmail());
        $this->assertEquals('John Smith', $account->getName());
        $this->assertTrue(is_array($account->getNotifications()));
        $this->assertTrue(is_array($account->getAllDisputes()));
        $this->assertEquals('/accounts/' . $account->getLoginId(), $account->getUrl());
    }

    public function testIndividualSetters() {
        $account = $this->createAgent();
        $this->assertEquals(false, $account->getRawCV());
        $account->setCV('test');
        $this->assertEquals('test', $account->getRawCV());
        $this->assertEquals('<p>test</p>', trim($account->getCV()));
        $account->setCV('#CV coming soon.');
        $this->assertEquals('<h1 id="cv-coming-soon">CV coming soon.</h1>', trim($account->getCV()));
    }

    public function testOrganisationGetters() {
        $account = $this->createLawFirm();
        $this->assertEquals(3, $account->getLoginId());
        $this->assertEquals('law_firm_a@t.co', $account->getEmail());
        $this->assertEquals('Webdapper Ltd', $account->getName());
        $this->assertTrue(is_array($account->getNotifications()));
        $this->assertTrue(is_array($account->getAllDisputes()));
        $this->assertEquals('/accounts/' . $account->getLoginId(), $account->getUrl());
    }

    public function testOrganisationSetters() {
        $account = $this->createLawFirm();
        $this->assertEquals(false, $account->getRawDescription());
        $account->setDescription('TEST');
        $this->assertEquals('TEST', $account->getRawDescription());
    }
}