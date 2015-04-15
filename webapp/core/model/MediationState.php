<?php

class MediationState {

    private $mediationCentreOffer;
    private $mediatorOffer;

    function __construct($disputeID) {
        $this->setVariables($disputeID);
    }

    private function setVariables($disputeID) {
        $this->disputeID            = $disputeID;
        $this->mediationCentreOffer = DBMediation::getMediationCentreOfferForDispute($disputeID);
        $this->mediatorOffer        = DBMediation::getMediatorOfferForDispute($disputeID);
    }

    public function mediationCentreProposed() {
        return $this->mediationCentreOffer !== false;
    }

    public function mediatorProposed() {
        return $this->mediatorOffer !== false;
    }

    public function mediationCentreDecided() {
        return (
            $this->mediationCentreProposed() &&
            $this->mediationCentreOffer['status'] === 'accepted'
        );
    }

    public function mediatorDecided() {
        return (
            $this->mediatorProposed() &&
            $this->mediatorOffer['status'] === 'accepted'
        );
    }

    public function inMediation() {
        return (
            $this->mediationCentreDecided() && $this->mediatorDecided()
        );
    }

    public function getMediationCentre() {
        return new MediationCentre((int) $this->mediationCentreOffer['proposed_id']);
    }

    public function getMediationCentreProposer() {
        return new Agent((int) $this->mediationCentreOffer['proposer_id']);
    }

    public function getMediator() {
        return new Mediator((int) $this->mediatorOffer['proposed_id']);
    }

    public function getMediatorProposer() {
        return new Agent((int) $this->mediatorOffer['proposer_id']);
    }

    public function acceptLatestProposal() {
        DBMediation::respondToMediationProposal($this->getLatestProposalId(), 'accepted');
        $this->setVariables($this->disputeID);
        $this->notifyAcceptedParty();
    }

    public function declineLatestProposal() {
        DBMediation::respondToMediationProposal($this->getLatestProposalId(), 'declined');
        $this->setVariables($this->disputeID);
    }

    private function getLatestProposalId() {
        if ($this->mediationCentreDecided()) {
            $mediationOfferId = $this->mediatorOffer['mediation_offer_id'];
        }
        else {
            $mediationOfferId = $this->mediationCentreOffer['mediation_offer_id'];
        }
        return (int) $mediationOfferId;
    }

    private function notifyAcceptedParty() {
        if ($this->mediatorDecided()) {
            $partyID = (int) $this->mediatorOffer['proposed_id'];
            $message = 'You have been assigned as the Mediator of a dispute.';
        }
        else {
            $partyID = (int) $this->mediationCentreOffer['proposed_id'];
            $message = 'Your Mediation Centre has been selected to mediate a dispute.';
        }

        $dispute = new Dispute($this->disputeID);

        DBCreate::notification(array(
            'recipient_id' => $partyID,
            'message'      => $message,
            'url'          => $dispute->getUrl()
        ));
    }

}
