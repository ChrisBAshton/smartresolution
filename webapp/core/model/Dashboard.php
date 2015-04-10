<?php

class Dashboard {

    public static function getTopLevelActions($account) {
        global $dashboardActions;

        if ($account instanceof LawFirm) {
            $dashboardActions = array(
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
        elseif ($account instanceof MediationCentre) {
            $dashboardActions = array(
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
        elseif ($account instanceof Agent) {
            $dashboardActions = array(
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
        elseif ($account instanceof Mediator) {
            $dashboardActions = array(
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

        ModuleController::emit('homescreen_dashboard');

        return $dashboardActions;
    }

}