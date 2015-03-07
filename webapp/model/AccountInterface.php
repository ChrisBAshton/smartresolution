<?php

interface AccountInterface {

    public function __construct($account);
    public function getLoginId();
    public function getEmail();
    public function getName();
    public function getNotifications();
    public function __toString();
}

class MockAccount implements AccountInterface {
    public function __construct($account = NULL) {

    }
    public function getLoginId() {
        return false;
    }
    public function getEmail() {
        return false;
    }
    public function getName() {
        return false;
    }
    public function getNotifications() {
        return false;
    }
    public function __toString() {
        return 'Mock Account';
    }
}