<?php

class LifespanMock implements LifespanInterface {

    function __construct($lifespan = array()) {
    }

    public function status() {
        return 'No lifespan set yet.';
    }

    public function isCurrent() {
        return false;
    }

    public function isEnded() {
        return false;
    }

    public function offered() {
        return false;
    }

    public function accepted() {
        return false;
    }

    public function declined() {
        return false;
    }

    public function disputeClosed() {
        // do nothing
    }

}
