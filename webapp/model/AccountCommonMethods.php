<?php

/**
 * This abstract class defines the functions which are identical in all account types, be they Organisation or Individual and be they Agent or Mediator, Law Firm or Mediation Centre.
 *
 * This does not implement the AccountInterface because some methods in the interface require different definitions depending on the account type.
 *
 */
abstract class AccountCommonMethods {

    /**
     * @see AccountInterface::getLoginId() Implements the corresponding function in AccountInterface.
     */
    public function getLoginId() {
        return $this->loginId;
    }

    /**
     * @see AccountInterface::getEmail() Implements the corresponding function in AccountInterface.
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @see AccountInterface::getNotifications() Implements the corresponding function in AccountInterface.
     */
    public function getNotifications() {
        $notifications = array();

        $notificationsDetails = Database::instance()->exec('SELECT notification_id FROM notifications WHERE recipient_id = :login_id AND read = "false" ORDER BY notification_id DESC',
            array(':login_id' => $this->getLoginId()));

        foreach ($notificationsDetails as $details) {
            $notifications[] = new Notification($details['notification_id']);
        }

        return $notifications;
    }

    /**
     * @see AccountInterface::getAllDisputes() Implements the corresponding function in AccountInterface.
     */
    public function getAllDisputes() {
        return AccountDetails::getAllDisputes($this);
    }

    /**
     * @see AccountInterface::getUrl()  Implements the corresponding function in AccountInterface.
     */
    public function getUrl() {
        return '/accounts/' . $this->getLoginId();
    }

    /**
     * @see AccountInterface::getUrl()  Implements the corresponding function in AccountInterface.
     */
    public function getRole() {
        throw new Exception('ACCOUNT TYPE MUST BE SET IN SUBCLASS');
    }

    /**
     * @see AccountInterface::toString() Implements the corresponding function in AccountInterface.
     */
    public function __toString() {
        return '<a href="' . $this->getUrl() . '">' . $this->getName() . '</a>';
    }

}
