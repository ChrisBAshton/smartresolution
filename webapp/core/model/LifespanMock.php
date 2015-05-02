<?php

/**
 * Lifespan mock object. If no lifespan has been proposed in a dispute, a lifespan mock object is returned so that
 * we don't have to complicate the dispute view with additional business logic.
 */
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

    public function endLifespan() {
    }

}
