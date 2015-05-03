<?php

/**
 * Interface describing an Account and all of the functions that any Account classes must implement.
 */
interface AccountInterface {

    /**
     * Constructor should create a sub-classed Account object.
     * @param array $account Associative array corresponding to the account, taken from the database.
     */
    public function __construct($account);

    /**
     * Gets the login ID representing the account in the database.
     * @return integer
     */
    public function getLoginId();

    /**
     * Gets the email associated with the account.
     * @return string
     */
    public function getEmail();

    /**
     * @return boolean True if account is verified, false if not.
     */
    public function isVerified();

    /**
     * Gets the name of the account.
     * @return string
     */
    public function getName();

    /**
     * Gets all unread notifications associated with the account.
     * @return array<Notification>
     */
    public function getNotifications();

    /**
     * Gets all disputes associated with the account.
     * @return array<Dispute>
     */
    public function getAllDisputes();

    /**
     * Gets the URL to the account's public profile.
     * @return string
     */
    public function getUrl();

    /**
     * Returns the type of account, displayed to the user.
     * Example: 'Agent', 'Law Firm'
     * @return string Account type.
     */
    public function getRole();

    /**
     * Account should be rendered as follows:
     *     <a href="/link/to/account/profile">Account Name</a>
     * @return string
     */
    public function __toString();
}
