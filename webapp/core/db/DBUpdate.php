<?php

/**
 * Database connector class responsible for applying object updates to the persistence layer.
 */
class DBUpdate extends Prefab {

    /**
     * Updates the given Notification's record in the database.
     * @param  Notification $notification
     */
    public function notification($notification) {
        Database::instance()->exec('UPDATE notifications
            SET read = :read
            WHERE notification_id = :notification_id',
            array(
                ':notification_id' => $notification->getNotificationId(),
                ':read' => true
            )
        );
    }

    /**
     * Updates the given Organisation's record in the database.
     * @param  Organisation $organisation
     */
    public function organisation($organisation) {
        Database::instance()->exec(
            'UPDATE organisations SET description = :description WHERE login_id = :uid',
            array(
                ':description' => $organisation->getRawDescription(),
                ':uid'         => $organisation->getLoginId()
            )
        );
    }

    /**
     * Updates the given Individual's record in the database.
     * @param  Individual $individual
     */
    public function individual($individual) {
        Database::instance()->exec(
            'UPDATE individuals SET cv = :cv WHERE login_id = :uid',
            array(
                ':cv'  => $individual->getRawCV(),
                ':uid' => $individual->getLoginId()
            )
        );
    }

    /**
     * Updates the given Lifespan's record in the database.
     * @param  Lifespan $lifespan
     */
    public function lifespan($lifespan) {
        if (!$lifespan instanceof LifespanMock) {
            Database::instance()->exec(
                'UPDATE lifespans SET status = :status, end_time = :end_time WHERE lifespan_id = :lifespan_id',
                array(
                    ':lifespan_id' => $lifespan->getLifespanId(),
                    ':end_time'    => $lifespan->endTime(),
                    ':status'      => $lifespan->getRawStatus()
                )
            );
        }
    }

    /**
     * Updates the given Dispute's record in the database.
     * @param  Dispute $dispute
     */
    public function dispute($dispute) {
        Database::instance()->exec(
            'UPDATE disputes SET round_table_communication = :rtc, status = :status, type = :type WHERE dispute_id = :dispute_id',
            array(
                ':dispute_id' => $dispute->getDisputeId(),
                ':rtc'        => $dispute->inRoundTableCommunication(),
                ':status'     => $dispute->getStatus(),
                ':type'       => $dispute->getType()
            )
        );
    }


    /**
     * Updates the 'party_b' attribute of the given dispute with the given party.
     * @todo  remove - this should be handled in DBUpdate::instance()->dispute().
     * @deprecated
     * @param  int $partyID   ID of the party.
     * @param  int $disputeID ID of the dispute.
     */
    public function updateDisputePartyB($partyID, $disputeID) {
        Database::instance()->exec(
            'UPDATE disputes SET party_b = :party_id WHERE dispute_id = :dispute_id',
            array(
                ':party_id'   => $partyID,
                ':dispute_id' => $disputeID
            )
        );
    }

    /**
     * Updates the given DisputeParty's record in the database.
     * Special case: unlike other update methods, this method might actually CREATE the dispute party, if it does not already exist.
     * @param  DisputeParty $disputeParty
     */
    public function disputeParty($disputeParty) {
        if (!$disputeParty->getPartyId()) {
            $createdParty = DBCreate::instance()->disputeParty(array(
                'organisation_id' => $disputeParty->getLawFirmID()
            ));
            $partyID = $createdParty->getPartyId();
            $disputeParty->setPartyId($partyID);
            $this->updateDisputePartyB($partyID, $disputeParty->getDisputeId());
        }
        elseif ($disputeParty->getPartyId() !== 0) {
            $lawFirmID = ($disputeParty->getLawFirmID() === 0) ? NULL : $disputeParty->getLawFirmID();
            $agentID   = ($disputeParty->getAgentID() === 0) ? NULL : $disputeParty->getAgentID();

            if (is_null($lawFirmID)) {
                throw new Exception('Tried updating law firm ID to NULL.' . $disputeParty->getLawFirmID());
            }

            Database::instance()->exec(
                'UPDATE dispute_parties SET organisation_id = :organisation_id, individual_id = :individual_id, summary = :summary WHERE party_id = :party_id',
                array(
                    ':party_id'        => $disputeParty->getPartyId(),
                    ':organisation_id' => $lawFirmID,
                    ':individual_id'   => $agentID,
                    ':summary'         => $disputeParty->getRawSummary()
                )
            );
        }
        else {
            Utils::instance()->throwException("Tried setting something other than Law Firm when the record for the party has not been created yet.");
        }
    }
}