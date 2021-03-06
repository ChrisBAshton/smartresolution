<?php

/**
 * This abstract class defines the functions which are identical in all account types, be they Organisation or Individual and be they Agent or Mediator, Law Firm or Mediation Centre.
 *
 * This does not implement the AccountInterface because some methods in the interface require different definitions depending on the account type.
 *
 */
abstract class Account {

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
     * @see AccountInterface::isVerified() Implements the corresponding function in AccountInterface.
     */
    public function isVerified() {
        return $this->verified;
    }

    /**
     * @see AccountInterface::getUnreadNotifications() Implements the corresponding function in AccountInterface.
     */
    public function getUnreadNotifications() {
        return DBQuery::instance()->getUnreadNotificationsForLoginId($this->getLoginId());
    }

    /**
     * @see AccountInterface::getAllNotifications() Implements the corresponding function in AccountInterface.
     */
    public function getAllNotifications() {
        return DBQuery::instance()->getAllNotificationsForLoginId($this->getLoginId());
    }

    /**
     * @see AccountInterface::getAllDisputes() Implements the corresponding function in AccountInterface.
     */
    public function getAllDisputes() {
        return DBQuery::instance()->getAllDisputes($this);
    }

    /**
     * @see AccountInterface::getUrl()  Implements the corresponding function in AccountInterface.
     */
    public function getUrl() {
        return '/accounts/' . $this->getLoginId();
    }

    /**
     * @see AccountInterface::getRole()  Implements the corresponding function in AccountInterface.
     */
    public function getRole() {
        Utils::instance()->throwException('ACCOUNT TYPE MUST BE SET IN SUBCLASS');
    }

    /**
     * @see AccountInterface::__toString() Implements the corresponding function in AccountInterface.
     */
    public function __toString() {
        return '<a href="' . $this->getUrl() . '">' . $this->getName() . '</a>';
    }

}
