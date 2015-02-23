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
        ModuleController::$modules[] = $config;
    }

    public static function getModules() {
        return ModuleController::$modules;
    }

    public static function subscribe($event, $action, $priority) {

    }
}