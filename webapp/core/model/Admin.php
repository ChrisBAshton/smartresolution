<?php

/**
 * Admin account.
 */
class Admin extends Account implements AccountInterface {

    private $loginId;
    private $email;
    private $verified;

    /**
     * Admin constructor.
     * @param array   $account             Admin details.
     *        int     $account['login_id'] Login ID of the admin.
     *        string  $account['email']    Admin's email address.
     *        boolean $account['verified'] Whether or not the account is verified.
     */
    function __construct($account) {
        $this->loginId  = $account['login_id'];
        $this->email    = $account['email'];
        $this->verified = $account['verified'];
    }

    /**
     * Returns the name of the administrator account. As there is only one admin account, we've hardcoded a default name here.
     * @return string
     */
    public function getName() {
        return 'Administrator';
    }

    /**
     * Returns a human-readable summary of the administrator account.
     * @return string
     */
    public function getRole() {
        return 'Administrator';
    }

    /**
     * Returns the admin profile URL. There should be no need for this facility, but it is required by the AccountInterface, so an empty string is returned.
     * @return string
     */
    public function getUrl() {
        return '';
    }

    /**
     * Returns all of the disputes related to the admin account. This will obviously be empty.
     * @return array
     */
    public function getAllDisputes() {
        return array();
    }

    /**
     * Defines how the admin account should be rendered as HTML.
     * @return string
     */
    public function __toString() {
        return 'Administrator';
    }
}
