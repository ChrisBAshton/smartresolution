<?php

class ProfileController {

    public function view($f3, $params) {
        mustBeLoggedIn();
        $accountID = (int) $params['accountID'];
        $viewAccount = AccountDetails::getAccountById($accountID);
        $f3->set('viewAccount', $viewAccount);
        $f3->set('content','profile.html');
        echo View::instance()->render('layout.html');
    }

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

    private function handlePost($f3, $account) {
        if (count($f3->get('POST')) > 0) {

            if ($f3->get('POST.cv')) {
                $account->setCV($f3->get('POST.cv'));
            }

            if ($f3->get('POST.description')) {
                $account->setDescription($f3->get('POST.description'));
            }

            $f3->set('success_message', 'Profile updated.');
        }
    }

}
