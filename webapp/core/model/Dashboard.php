<?php

class Dashboard {

    public static function getTopLevelActions($account) {
        global $dashboardActions;

        if ($account instanceof LawFirm) {
            $dashboardActions = Dashboard::getLawFirmActions();
        }
        elseif ($account instanceof MediationCentre) {
            $dashboardActions = Dashboard::getMediationCentreActions();
        }
        elseif ($account instanceof Agent) {
            $dashboardActions = Dashboard::getAgentActions();
        }
        elseif ($account instanceof Mediator) {
            $dashboardActions = Dashboard::getMediatorActions();
        }
        elseif ($account instanceof Admin) {
            $dashboardActions = Dashboard::getAdminActions();
        }

        ModuleController::emit('homescreen_dashboard');

        return $dashboardActions;
    }

    private static function getLawFirmActions() {
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

    private static function getMediationCentreActions() {
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

    private static function getAgentActions() {
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

    private static function getMediatorActions() {
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

    private static function getAdminActions() {
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