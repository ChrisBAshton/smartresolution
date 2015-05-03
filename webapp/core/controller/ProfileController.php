<?php

/**
 * Links profile-related HTTP requests to their actions.
 */
class ProfileController {

    /**
     * View an account's profile. This applies to all account types, e.g. LawFirm, Mediator, Admin, etc.
     *
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /profile/@accountID => $params['accountID'] => 1337
     */
    public function view($f3, $params) {
        mustBeLoggedIn();
        $accountID = (int) $params['accountID'];
        $viewAccount = DBGet::instance()->account($accountID);
        $f3->set('viewAccount', $viewAccount);
        $f3->set('content','profile.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * Edit the logged in account's details.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /profile/@accountID => $params['accountID'] => 1337
     */
    public function edit($f3, $params) {
        $account = mustBeLoggedIn();

        if ($account instanceof Individual) {
            $f3->set('content','profile_edit--individual.html');
        } else {
            $f3->set('content','profile_edit--organisation.html');
        }

        $this->handlePost($f3, $account);

        echo View::instance()->render('layout.html');
    }

    /**
     * If the edit page was accessed with the POST method, apply the edits persistently and set a success message.
     * @param  F3 $f3         The base F3 object.
     * @param  array $params  The parsed URL parameters, e.g. /profile/@accountID => $params['accountID'] => 1337
     */
    private function handlePost($f3, $account) {
        if (count($f3->get('POST')) > 0) {

            if ($f3->get('POST.cv')) {
                $account->setCV($f3->get('POST.cv'));
                DBUpdate::instance()->individual($account);
            }

            if ($f3->get('POST.description')) {
                $account->setDescription($f3->get('POST.description'));
                DBUpdate::instance()->organisation($account);
            }

            $f3->set('success_message', 'Profile updated.');
        }
    }
}