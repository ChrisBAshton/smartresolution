<?php

class MediationState {

    private $mediationCentreOffer;
    private $mediatorOffer;

    function __construct($disputeID) {
        $this->setVariables($disputeID);
    }

    private function setVariables($disputeID) {
        $this->disputeID            = $disputeID;
        $this->mediationCentreOffer = $this->getOfferOfType('mediation_centre');
        $this->mediatorOffer        = $this->getOfferOfType('mediator');
    }

    private function getOfferOfType($type) {
        $offers = Database::instance()->exec(
            'SELECT * FROM mediation_offers
            WHERE type     = :type
            AND dispute_id = :dispute_id
            AND status    != "declined"
            ORDER BY mediation_offer_id DESC',
            array(
                ':type'       => $type,
                ':dispute_id' => $this->disputeID
            )
        );

        if (count($offers) !== 0) {
            return $offers[0];
        }

        return false;
    }

    public function mediationProposed() {
        return $this->mediationCentreOffer !== false;
    }

    public function mediationCentreDecided() {
        return (
            $this->mediationCentreOffer &&
            $this->mediationCentreOffer['status'] === 'accepted'
        );
    }

    public function mediatorDecided() {
        return (
            $this->mediatorOffer &&
            $this->mediatorOffer['status'] === 'accepted'
        );
    }

    public function getMediationCentre() {
        return new MediationCentre((int) $this->mediationCentreOffer['proposed_id']);
    }

    public function getMediationCentreProposer() {
        return new Agent((int) $this->mediationCentreOffer['proposer']);
    }

    public function getMediator() {
        return new Mediator((int) $this->mediatorOffer['proposed_id']);
    }

    public function getMediatorProposer() {
        return new Agent((int) $this->mediatorOffer['proposer']);
    }

    public function acceptLatestProposal() {
        $this->respondToLatestProposal('accepted');
    }

    public function declineLatestProposal() {
        $this->respondToLatestProposal('declined');
    }

    private function respondToLatestProposal($response) {
        Database::instance()->exec(
            'UPDATE mediation_offers SET status = :response WHERE mediation_offer_id = :offer_id',
            array(
                ':offer_id' => $this->getLatestProposalId(),
                ':response' => $response
            )
        );
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

}
