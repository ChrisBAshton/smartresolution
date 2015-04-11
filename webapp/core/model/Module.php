<?php

class Module {

    function __construct($config, $active) {
        $this->key         = $config['key'];
        $this->title       = $config['title'];
        $this->description = $config['description'];
        $this->active      = $active;
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

    public function active() {
        return $this->active;
    }

    public function special() {
        return $this->key === 'other';
    }

    public function toggleActiveness() {
        if ($this->special()) {
            throw new Exception("Tried to change active status of module " . $this->title() . " but it is a special module and cannot be changed!");
        }
        else {
            // @TODO - make active change persistent
            $this->active = !$this->active;
        }
    }
}