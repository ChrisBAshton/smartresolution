<?php

/**
 * Represents an individual, such as an Agent or Mediator, defining some of the default implementations shared by both types of individual.
 */
class Individual extends Account implements AccountInterface {

    private $loginId;
    private $email;
    private $verified;
    private $forename;
    private $surname;
    private $cv;
    private $organisation;

    /**
     * Individual constructor.
     * @param array   $account             Account details retrieved from database.
     *        int     $account['login_id'] Login ID of the account.
     *        string  $account['email']    Email address of the account.
     *        boolean $account['verified'] Indicates whether or not the account has been verified.
     *        string  $account['forename'] Individual's forename.
     *        string  $account['surname']  Individual's surname.
     *        string  $account['cv']       Individual's CV, in markdown format.
     */
    function __construct($account) {
        $this->loginId      = $account['login_id'];
        $this->email        = $account['email'];
        $this->verified     = $account['verified'];
        $this->forename     = $account['forename'];
        $this->surname      = $account['surname'];
        $this->cv           = $account['cv'];
        $this->organisation = DBGet::instance()->account($account['organisation_id']);
    }

    /**
     * Returns the individual's full name.
     * @return string Individual's name.
     */
    public function getName() {
        return $this->forename . ' ' . $this->surname;
    }

    /**
     * Gets the individual's raw CV (before parsing the markdown).
     * @return string Raw CV.
     */
    public function getRawCV() {
        return $this->cv;
    }

    /**
     * Gets the parsed markdown of the individual's raw CV. If no CV has been set, a default CV is returned.
     * @return string Processed CV.
     */
    public function getCV() {
        if (strlen($this->cv) === 0) {
            $cv = '_This individual has not provided a CV._';
        }
        else {
            $cv = $this->cv;
        }

        return Markdown::instance()->convert($cv);
    }

    /**
     * Sets the individual's CV. Note that this change is not made persistent until the individual object is passed to DBUpdate::instance()->individual().
     * @param [type] $cv [description]
     */
    public function setCV($cv) {
        $this->cv = $cv;
    }

    /**
     * Returns the organisation that the individual belongs to. This will be a LawFirm or MediationCentre object depending on the Individual account type.
     * @return Organisation
     */
    public function getOrganisation() {
        return $this->organisation;
    }
}

/**
 * Specialised type of individual, representing an Agent, overriding the defaults set in Individual where necessary.
 */
class Agent extends Individual {

    /**
     * Returns a human-readable summary of the account type.
     * @return string The account role.
     */
    public function getRole() {
        return 'Agent';
    }

}

/**
 * Specialised type of individual, representing a Mediator, overriding the defaults set in Individual where necessary.
 */
class Mediator extends Individual {

    /**
     * Returns a human-readable summary of the account type.
     * @return string The account role.
     */
    public function getRole() {
        return 'Mediator';
    }

    /**
     * Returns true if the mediator has been marked as available (by their Mediation Centre) for the given dispute.
     * @param  int  $disputeID ID of the dispute.
     * @return boolean         true if marked as available, otherwise false.
     */
    public function isAvailableForDispute($disputeID) {
        $availableMediators = DBMediation::instance()->getAvailableMediators($disputeID);
        foreach($availableMediators as $mediator) {
            if ($mediator->getLoginId() === $this->getLoginId()) {
                return true;
            }
        }
        return false;
    }

}
