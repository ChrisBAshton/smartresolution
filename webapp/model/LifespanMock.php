<?php

class LifespanMock implements LifespanInterface {

    function __construct($lifespanID = 0) {
        $this->lifespanID = $lifespanID;
    }

    public function status() {
        return 'No lifespan set yet.';
    }

    public function isCurrent() {
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

}
