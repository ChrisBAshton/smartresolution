<?php

interface AccountInterface {

    public function __construct($account);
    public function getLoginId();
    public function getEmail();
    public function getName();
    
}