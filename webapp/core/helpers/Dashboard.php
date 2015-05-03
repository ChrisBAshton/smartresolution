<?php

/**
 * Determines the top-level dashboard items on the homescreen, according to account type.
 */
class Dashboard extends Prefab {

    /**
     * Retrieves the top-level dashboard items, including any module-specific modifications.
     * @param  Account $account Account whose context will affect the dashboard items.
     * @return array            Array of dashboard items.
     *         string array['title']
     *         string array['image']
     *         string array['href']
     */
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

    /**
     * Returns the dashboard items that are specific to Law Firms.
     * @return array
     * @see  Dashboard::getTopLevelActions For details on the returned array.
     */
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

    /**
     * Returns the dashboard items that are specific to Mediation Centres.
     * @return array
     * @see  Dashboard::getTopLevelActions For details on the returned array.
     */
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

    /**
     * Returns the dashboard items that are specific to Agents.
     * @return array
     * @see  Dashboard::getTopLevelActions For details on the returned array.
     */
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

    /**
     * Returns the dashboard items that are specific to Mediators.
     * @return array
     * @see  Dashboard::getTopLevelActions For details on the returned array.
     */
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

    /**
     * Returns the dashboard items that are specific to Admins.
     * @return array
     * @see  Dashboard::getTopLevelActions For details on the returned array.
     */
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