<?php

/**
 * Defines the module in the system.
 * Decorator pattern. Syntactic sugar instead of calling ModuleController static function directly. Improves decoupling.
 * @param  Array $config
 * @param  Function $moduleDefinitionFunction
 */
function declare_module($config, $moduleDefinitionFunction) {
    ModuleController::registerModule($config);
    $moduleDefinitionFunction();
}

/**
 * Subscribes an anonymous function (defined within a module) to a given event.
 * Decorator pattern. Syntactic sugar instead of calling ModuleController static function directly. Improves decoupling.
 * @param  String     $event    
 * @param  String     $action   
 * @param  String|Int $priority
 */
function on($event, $action, $priority = 'medium') {
    ModuleController::subscribe($event, $action, $priority);
}

class ModuleController {

    private static $modules = array();

    public static function registerModule($config) {
        ModuleController::$modules[] = new Module($config);
    }

    public static function getModules() {
        return ModuleController::$modules;
    }

    public static function subscribe($event, $action, $priority) {

    }
}

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