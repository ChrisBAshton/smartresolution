<?php

class Module {

    function __construct($config) {
        $this->key         = $config['key'];
        $this->title       = $config['title'];
        $this->description = $config['description'];
    }

    public function key() {
        return $this->key;
    }

    public function title() {
        return $this->title;
    }

    public function description() {
        return $this->description;
    }
}