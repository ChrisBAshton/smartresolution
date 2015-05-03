<?php

/**
 * Represents an organisation, such as a Mediation Centre or a Law Firm, defining some of the default implementations shared by both types of organisation.
 */
class Organisation extends Account implements AccountInterface {

    protected $loginId;
    protected $email;
    protected $verified;
    protected $name;
    protected $description;

    /**
     * Organisation constructor.
     * @param array   $account                Account details retrieved from database.
     *        int     $account['login_id']    Login ID of the account.
     *        string  $account['email']       Email address of the account.
     *        boolean $account['verified']    Indicates whether or not the account has been verified.
     *        string  $account['name']        Name of the organisation.
     *        string  $account['description'] Organisation description.
     */
    function __construct($account) {
        $this->loginId     = $account['login_id'];
        $this->email       = $account['email'];
        $this->verified    = $account['verified'];
        $this->name        = $account['name'];
        $this->description = $account['description'];
    }

    /**
     * Returns the name of the organisation.
     * @return string Organisation name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Returns the description of the organisation in its raw format, as stored in the database.
     * @return string The raw description.
     */
    public function getRawDescription() {
        return $this->description;
    }

    /**
     * Returns the description of the organisation in its filtered format. The description is parsed as markdown and any harmful embedded scripts or HTML is escaped. If no description has been set yet, a default value is returned.
     * @return string The processed description.
     */
    public function getDescription() {
        if (strlen($this->description) === 0) {
            $description = '_This organisation has not provided a description._';
        }
        else {
            $description = $this->description;
        }

        return Markdown::instance()->convert($description);
    }

    /**
     * Sets the raw description of the organisation. Note that this change is not made persistent until the
     * organisation object is passed to DBUpdate::instance()->organisation().
     * @param string $description The raw description to set.
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Returns all individuals associated with the organisation. These will be Agents or Mediators depending on the type of organisation.
     * @return array<Agent>|array<Mediator> The array of individuals.
     */
    public function getIndividuals() {
        return DBQuery::instance()->getIndividuals($this->getLoginId());
    }
}

/**
 * Specialised type of organisation, representing a Law Firm, overriding the defaults set in Organisation where necessary.
 */
class LawFirm extends Organisation {

    /**
     * Returns a human-readable summary of the account type.
     * @return string The account role.
     */
    public function getRole() {
        return 'Law Firm';
    }

    /**
     * Returns all agents associated with the law firm.
     * @return array<Agent> The array of agents.
     */
    public function getAgents() {
        return parent::getIndividuals();
    }

}

/**
 * Specialised type of organisation, representing a Mediation Centre, overriding the defaults set in Organisation where necessary.
 */
class MediationCentre extends Organisation {

    /**
     * Returns a human-readable summary of the account type.
     * @return string The account role.
     */
    public function getRole() {
        return 'Mediation Centre';
    }

    /**
     * Returns all mediators associated with the mediation centre.
     * @return array<Mediator> The array of mediators.
     */
    public function getMediators() {
        return parent::getIndividuals();
    }
}