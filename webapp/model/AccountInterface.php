<?php

interface AccountInterface {

    /**
     * Constructor should create a sub-classed Account object.
     * @param Array|Int $account Associative array corresponding to the account, taken from the database. OR an integer corresponding to the login_id of the account.
     */
    public function __construct($account);

    /**
     * Called by constructor - repopulates the object properties from the database. Usually done after a refresh.
     * @param Array|Int $account Associative array corresponding to the account, taken from the database. OR an integer corresponding to the login_id of the account.
     */
    public function setVariables($account);

    /**
     * Gets the login ID representing the account in the database.
     * @return integer
     */
    public function getLoginId();

    /**
     * Gets the email associated with the account.
     * @return String
     */
    public function getEmail();

    /**
     * Gets the name of the account.
     * @return String
     */
    public function getName();

    /**
     * Gets all unread notifications associated with the account.
     * @return Array<Notification>
     */
    public function getNotifications();

    /**
     * Gets all disputes associated with the account.
     * @return Array<Dispute>
     */
    public function getAllDisputes();

    /**
     * Gets the URL to the account's public profile.
     * @return Url
     */
    public function getUrl();

    /**
     * Returns the type of account, displayed to the user.
     * Example: 'Agent', 'Law Firm'
     * @return String Account type.
     */
    public function getRole();

    /**
     * Account should be rendered as follows:
     *     <a href="/link/to/account/profile">Account Name</a>
     * @return String
     */
    public function __toString();
}
