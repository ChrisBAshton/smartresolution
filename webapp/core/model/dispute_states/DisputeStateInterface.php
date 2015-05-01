<?php

/**
 * Interface defining a Dispute State, describing the functions that all dispute states must define.
 */
interface DisputeStateInterface {

    /**
     * Contructor for a dispute state.
     * @param Dispute $dispute The dispute whose state we are representing.
     * @param Account $account The account of the user viewing our dispute. Often determines whether or not an action should be available.
     */
    public function __construct($dispute, $account);

    /**
     * Gets the description of the state in a human-readable way, e.g. 'Dispute Opened'
     * @return string Description.
     */
    public function getStateDescription();

    /**
     * Determines whether or not the current user can open this dispute against another law firm.
     * @return boolean
     */
    public function canOpenDispute();

    /**
     * Determines whether or not the current user can assign this dispute to an agent in their law firm.
     * @return boolean
     */
    public function canAssignDisputeToAgent();

    /**
     * Determines whether or not the current user can create a new lifespan offer.
     * @return boolean
     */
    public function canNegotiateLifespan();

    /**
     * Determines whether or not the current user can send a message to the other agent in the dispute.
     * @return boolean
     */
    public function canSendMessage();

    /**
     * Determines whether or not the current user can view evidence uploaded to the dispute.
     * @return boolean
     */
    public function canViewDocuments();

    /**
     * Determines whether or not the current user can upload new evidence to the dispute.
     * @return boolean
     */
    public function canUploadDocuments();

    /**
     * Determines whether or not the current user can write their summary of the dispute.
     * @return boolean
     */
    public function canEditSummary();

    /**
     * Determines whether or not the current user can propose mediation.
     * @return boolean
     */
    public function canProposeMediation();

    /**
     * Determines whether or not the current user can close the dispute.
     * @return boolean
     */
    public function canCloseDispute();

}