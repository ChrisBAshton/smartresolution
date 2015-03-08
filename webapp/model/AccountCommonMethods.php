<?php

/**
 * This abstract class defines the functions which are identical in all account types, be they Organisation or Individual and be they Agent or Mediator, Law Firm or Mediation Centre.
 *
 * This does not implement the AccountInterface because some methods in the interface require different definitions depending on the account type.
 */
abstract class AccountCommonMethods {

    public function getLoginId() {
        return $this->loginId;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getNotifications() {
        $notifications = array();
        
        $notificationsDetails = Database::instance()->exec('SELECT notification_id FROM notifications WHERE recipient_id = :login_id AND read = "false" ORDER BY notification_id DESC',
            array(':login_id' => $this->getLoginId()));

        foreach ($notificationsDetails as $details) {
            $notifications[] = new Notification($details['notification_id']);
        }

        return $notifications;
    }

    public function __toString() {
        return '<a href="">' . $this->getName() . '</a>';
    }

    public function getAllDisputes() {
        $disputes = array();
        $disputesDetails = Database::instance()->exec(
            'SELECT dispute_id FROM disputes

            INNER JOIN dispute_parties
            ON disputes.party_a     = dispute_parties.party_id
            OR disputes.party_b     = dispute_parties.party_id
            OR disputes.third_party = dispute_parties.party_id

            WHERE organisation_id = :login_id OR individual_id = :login_id
            ORDER BY party_id DESC',
            array(':login_id' => $this->getLoginId())
        );
        foreach($disputesDetails as $dispute) {
            $disputes[] = new Dispute($dispute['dispute_id']);
        }
        return $disputes;
    }
}