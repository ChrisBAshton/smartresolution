<?php

/**
 * Represents a SmartResolution module, encapsulating information such as module name, activity state, and so on.
 */
class Module {

    private $key;
    private $title;
    private $description;
    private $active;
    private $moduleDefinitionFunction;

    /**
     * Module constructor.
     * @param array  $config                      Configuration array
     *        string $config['key']               Unique ID of the module, e.g. maritime_collision
     *        string $config['title']             Module title, e.g. Maritime Collision
     *        string $config['description']       Module description, e.g. Defines a 'Maritime Collision' custom dispute type.
     * @param boolean $active                    Denotes whether or not the module is active.
     * @param function $moduleDefinitionFunction Module definition function to be called if module is active.
     */
    function __construct($config, $active, $moduleDefinitionFunction) {
        $this->key         = $config['key'];
        $this->title       = $config['title'];
        $this->description = $config['description'];
        $this->active      = $active === true;
        $this->moduleDefinitionFunction = $moduleDefinitionFunction;
    }

    /**
     * Calls the module definition function.
     */
    public function callModuleDefinitionFunction() {
        call_user_func($this->moduleDefinitionFunction);
    }

    /**
     * Returns the module's unique ID.
     * @return string The key.
     */
    public function key() {
        return $this->key;
    }

    /**
     * Returns the module title.
     * @return string Module title.
     */
    public function title() {
        return $this->title;
    }

    /**
     * Returns the module description.
     * @return string Module description.
     */
    public function description() {
        return $this->description;
    }

    /**
     * Returns the module's activity state.
     * @return boolean True if active, false if not.
     */
    public function active() {
        return $this->active;
    }

    /**
     * Returns true or false depending on whether or not the module is required by the system.
     * If false, it is safe to deactivate and delete this module. If true, module is required by
     * the system and must be installed and active.
     * @return boolean True if module is special, false if not.
     */
    public function special() {
        return $this->key === 'other';
    }

    /**
     * Reverses the module's activity state, so that if the module is active it is made inactive, and if
     * it is inactive then it is made active. If the module is special, this raises an exception.
     */
    public function toggleActiveness() {
        if ($this->special()) {
            Utils::instance()->throwException("Tried to change active status of module " . $this->title() . " but it is a special module and cannot be changed!");
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