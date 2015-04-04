<?php

interface LifespanInterface {
    public function __construct($lifespanID);
    public function status();
    public function isCurrent();
    public function offered();
    public function accepted();
    public function declined();
    public function isEnded();
    public function disputeClosed();
}
