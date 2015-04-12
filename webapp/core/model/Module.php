<?php

class Module {

    private $moduleDefinitionFunction;

    function __construct($config, $active, $moduleDefinitionFunction) {
        $this->key         = $config['key'];
        $this->title       = $config['title'];
        $this->description = $config['description'];
        $this->active      = $active;
        $this->moduleDefinitionFunction = $moduleDefinitionFunction;
    }

    public function callModuleDefinitionFunction() {
        call_user_func($this->moduleDefinitionFunction);
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
            global $modulesConfig;
            $configFilepath = __DIR__ . '/../../modules/config.json';
            $modulesConfig[$this->key()] = !$modulesConfig[$this->key()];
            file_put_contents($configFilepath, json_encode($modulesConfig));
            $this->active = !$this->active;

            if ($this->active()) {
                $this->callModuleDefinitionFunction();
            }
        }
    }
}