<?php

class MediationState {

    private $mediationCentreOffer;
    private $mediatorOffer;

    function __construct($data) {
        // these default to 0 if key does not exist
        $this->mediationCentreOffer = (int) $data['mediation_centre_offer'];
        $this->mediatorOffer = (int) $data['mediator_offer'];
    }

    public function mediationProposed() {
        return $this->mediationCentreOffer !== 0;
    }

    public function isInMediation() {
        $mediatorOfferData = $this->getParty($this->mediatorOffer);
        return ($mediatorOfferData && $mediatorOfferData['status'] === 'accepted');
    }

    public function getMediationCentre() {
        if (!$this->mediationProposed()) {
            return false;
        }

        $mediatorOfferData = $this->getParty($this->mediationCentreOffer);
        if (!$mediatorOfferData) {
            throw new Exception('Mediation was marked as "In Progress" but no Mediation Centre has been offered!');
        }

        return new MediationCentre((int) $mediatorOfferData['proposed_id']);
    }

    private function getParty($mediationOfferId) {

        $mediatorOfferData = Database::instance()->exec(
            'SELECT * FROM mediation_offers WHERE mediation_offer_id = :mediation_offer_id',
            array(
                ':mediation_offer_id' => $mediationOfferId
            )
        );

        if (count($mediatorOfferData) === 1) {
            return $mediatorOfferData[0];
        }

        return false;
    }

}
