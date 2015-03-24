<?php

interface AccountInterface {

    /**
     * Constructor should create a sub-classed Account object.
     * @param Array $account Associative array corresponding to the account, taken from the database.
     */
    public function __construct($account);

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
     * Account should be rendered as follows:
     *     <a href="/link/to/account/profile">Account Name</a>
     * @return String
     */
    public function __toString();
}
