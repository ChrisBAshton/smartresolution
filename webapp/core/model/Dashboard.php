<?php

class Dashboard extends Prefab {

    public function getTopLevelActions($account) {
        global $dashboardActions;

        if ($account instanceof LawFirm) {
            $dashboardActions = $this->getLawFirmActions();
        }
        elseif ($account instanceof MediationCentre) {
            $dashboardActions = $this->getMediationCentreActions();
        }
        elseif ($account instanceof Agent) {
            $dashboardActions = $this->getAgentActions();
        }
        elseif ($account instanceof Mediator) {
            $dashboardActions = $this->getMediatorActions();
        }
        elseif ($account instanceof Admin) {
            $dashboardActions = $this->getAdminActions();
        }

        ModuleController::instance()->emit('homescreen_dashboard');

        return $dashboardActions;
    }

    public function getLawFirmActions() {
        return array(
            array(
                'href'  => '/disputes',
                'image' => '/core/view/images/disputes.png',
                'title' => 'View Disputes'
            ),
            array(
                'href'  => '/disputes/new',
                'image' => '/core/view/images/dispute.png',
                'title' => 'Create Dispute'
            ),
            array(
                'href'  => '/register/individual',
                'image' => '/core/view/images/security.png',
                'title' => 'Register Agent account'
            ),
            array(
                'href'  => '/settings',
                'image' => '/core/view/images/settings.png',
                'title' => 'Edit Profile'
            )
        );
    }

    public function getMediationCentreActions() {
        return array(
            array(
                'href'  => '/disputes',
                'image' => '/core/view/images/disputes.png',
                'title' => 'View Disputes'
            ),
            array(
                'href'  => '/register/individual',
                'image' => '/core/view/images/security.png',
                'title' => 'Register Mediator account'
            ),
            array(
                'href'  => '/settings',
                'image' => '/core/view/images/settings.png',
                'title' => 'Edit Profile'
            )
        );
    }

    public function getAgentActions() {
        return array(
            array(
                'href'  => '/disputes',
                'image' => '/core/view/images/disputes.png',
                'title' => 'View Disputes'
            ),
            array(
                'href'  => '/settings',
                'image' => '/core/view/images/settings.png',
                'title' => 'Edit Profile'
            )
        );
    }

    public function getMediatorActions() {
        return array(
            array(
                'href'  => '/disputes',
                'image' => '/core/view/images/disputes.png',
                'title' => 'View Disputes'
            ),
            array(
                'href'  => '/settings',
                'image' => '/core/view/images/settings.png',
                'title' => 'Edit Profile'
            )
        );
    }

    public function getAdminActions() {
        return array(
            array(
                'href'  => '/admin-modules-new',
                'image' => '/core/view/images/cloud.png',
                'title' => 'Marketplace'
            ),
            array(
                'href'  => '/admin-modules',
                'image' => '/core/view/images/security.png',
                'title' => 'Modules'
            ),
            array(
                'href'  => '/admin-customise',
                'image' => '/core/view/images/settings.png',
                'title' => 'Customise'
            )
        );
    }

}