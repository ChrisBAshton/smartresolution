<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class DashboardTest extends PHPUnit_Framework_TestCase
{

    public function testGetters() {
        $dashboard = Dashboard::instance();
        $actions = array(
            $dashboard->getLawFirmActions(),
            $dashboard->getMediationCentreActions(),
            $dashboard->getAgentActions(),
            $dashboard->getMediatorActions(),
            $dashboard->getAdminActions()
        );
        foreach($actions as $actionSet) {
            $this->assertTrue(is_array($actionSet) && count($actionSet) > 0);
        }
    }

    public function testGetTopLevelActions() {
        $dashboard = Dashboard::instance();
        $account   = DBAccount::instance()->getAccountByEmail('agent_a@t.co');
        $actions   = $dashboard->getTopLevelActions($account);
        $this->assertEquals(
            array(
                array(
                    "href"  => "/disputes",
                    "image" => "/core/view/images/disputes.png",
                    "title" => "View Disputes"
                ),
                array(
                    "href"  => "/settings",
                    "image" => "/core/view/images/settings.png",
                    "title" => "Edit Profile"
                )
            ),
            $actions
        );
    }
}