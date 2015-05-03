<?php

/**
 * Represents the state of a dispute's mediation progress. By default, mediationCentreProposed() will return false and is the first function one should call when determining mediation state. If mediationCentreProposed() returns true, there are other finely granulated functions allowing the system to query whether or not the mediation centre has been accepted, the mediator has been proposed or accepted, and indeed, inMediation() denotes whether or not the dispute has made the full transition to being 'in mediation'.
 *
 * @todo This is not a very object-oriented design. There is an issue on GitHub relating to removing this class, moving much of it to the DisputeState state pattern and also creating new classes (such as MediationOffer) where necessary. This class needs to be removed and implemented elsewhere in a more OO way.
 * @deprecated
 */
class MediationState {

    private $disputeID;
    private $mediationCentreOffer;
    private $mediatorOffer;

    /**
     * Mediation state constructor.
     * @param int $disputeID The ID of the dispute we want to determine mediation state upon.
     */
    function __construct($disputeID) {
        $this->setVariables($disputeID);
    }

    /**
     * Queries the database and sets the class' private attributes. This is encapsulated in its own function rather than being in the constructor as it allows us to update the object according to how it is represented in the database, without reinitialising the object entirely.
     * This is an old design pattern that has since been refactored out of most classes due to the tight coupling with the database. This is not the way we should be doing things!
     * @param int $disputeID The ID of the dispute we want to determine mediation state upon.
     */
    private function setVariables($disputeID) {
        $this->disputeID            = $disputeID;
        $this->mediationCentreOffer = DBMediation::instance()->getMediationCentreOfferForDispute($disputeID);
        $this->mediatorOffer        = DBMediation::instance()->getMediatorOfferForDispute($disputeID);
    }

    /**
     * Denotes whether or not either agent has proposed using a mediation centre (and, consequently, proposed that the dispute be put into mediation).
     * @return boolean True if a mediation centre has been proposed, otherwise false.
     */
    public function mediationCentreProposed() {
        return $this->mediationCentreOffer !== false;
    }

    /**
     * Denotes whether or not either agent has proposed using a mediator. This can only be true if a mediation centre has been decided upon.
     * @return boolean True if a mediator has been proposed, otherwise false.
     */
    public function mediatorProposed() {
        return $this->mediatorOffer !== false;
    }

    /**
     * Denotes whether or not a mediation centre has been decided. This is different from mediationCentreProposed(), which is only concerned with whether or not either agent has proposed using a mediation centre.
     * @return boolean True if both agents have agreed upon a mediation centre. Otherwise false.
     */
    public function mediationCentreDecided() {
        return (
            $this->mediationCentreProposed() &&
            $this->mediationCentreOffer['status'] === 'accepted'
        );
    }

    /**
     * Denotes whether or not a mediator has been decided. Different to mediatorProposed(), which is only concerned with whether or not either agent has proposed using a mediator.
     * @return boolean True if both agents have agreed upon a mediator. Otherwise false.
     */
    public function mediatorDecided() {
        return (
            $this->mediatorProposed() &&
            $this->mediatorOffer['status'] === 'accepted'
        );
    }

    /**
     * Denotes whether or not the dispute is in mediation. For a dispute to be in mediation, a mediation centre and a mediator must have been mutually agreed by both agents.
     * @return boolean True if in mediation, false if not.
     */
    public function inMediation() {
        return (
            $this->mediationCentreDecided() && $this->mediatorDecided()
        );
    }

    /**
     * Returns the mediation centre that has been proposed or decided.
     * @return MediationCentre
     */
    public function getMediationCentre() {
        return DBGet::instance()->account((int) $this->mediationCentreOffer['proposed_id']);
    }

    /**
     * Returns the account who proposed using the mediation centre.
     * @return Account
     */
    public function getMediationCentreProposer() {
        return DBGet::instance()->account((int) $this->mediationCentreOffer['proposer_id']);
    }

    /**
     * Returns the mediator that has been proposed or decided.
     * @return Mediator
     */
    public function getMediator() {
        return DBGet::instance()->account((int) $this->mediatorOffer['proposed_id']);
    }

    /**
     * Returns the account who proposed using the mediator.
     * @return Account
     */
    public function getMediatorProposer() {
        return DBGet::instance()->account((int) $this->mediatorOffer['proposer_id']);
    }

    /**
     * Accepts the latest mediation-related proposal, whether that is a mediation centre proposal or a mediator proposal. This function makes persistent changes: the fact that the proposal is accepted is applied to the database as well as the object.
     */
    public function acceptLatestProposal() {
        DBMediation::instance()->respondToMediationProposal($this->getLatestProposalId(), 'accepted');
        $this->setVariables($this->disputeID);
        $this->notifyAcceptedParty();
    }

    /**
     * Declines the latest mediation-related proposal, whether that is a mediation centre proposal or a mediator proposal. This function makes persistent changes: the fact that the proposal is declined is applied to the database as well as the object.
     */
    public function declineLatestProposal() {
        DBMediation::instance()->respondToMediationProposal($this->getLatestProposalId(), 'declined');
        $this->setVariables($this->disputeID);
    }

    /**
     * Returns the 'mediation_offers.mediation_offer_id' of the latest proposal related to the dispute.
     * @return int Latest mediation proposal ID.
     */
    private function getLatestProposalId() {
        if ($this->mediationCentreDecided()) {
            $mediationOfferId = $this->mediatorOffer['mediation_offer_id'];
        }
        else {
            $mediationOfferId = $this->mediationCentreOffer['mediation_offer_id'];
        }
        return (int) $mediationOfferId;
    }

    /**
     * Sends a notification to the mediation centre or mediator that has been chosen by the agents.
     */
    private function notifyAcceptedParty() {
        if ($this->mediatorDecided()) {
            $partyID = (int) $this->mediatorOffer['proposed_id'];
            $message = 'You have been assigned as the Mediator of a dispute.';
        }
        else {
            $partyID = (int) $this->mediationCentreOffer['proposed_id'];
            $message = 'Your Mediation Centre has been selected to mediate a dispute.';
        }

        $dispute = DBGet::instance()->dispute($this->disputeID);

        DBCreate::instance()->notification(array(
            'recipient_id' => $partyID,
            'message'      => $message,
            'url'          => $dispute->getUrl()
        ));
    }

}
