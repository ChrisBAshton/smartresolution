<?php

class SettingsController {

    public function view($f3) {
        $account = mustBeLoggedIn();
        errorPage('"Edit Profile" page coming soon.');
    }

}
