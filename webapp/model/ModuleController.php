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
    private static $subscriptions = array();

    public static function registerModule($config) {
        ModuleController::$modules[] = new Module($config);
    }

    public static function getModules() {
        return ModuleController::$modules;
    }

    public static function subscribe($event, $action, $priority) {
        // @TODO add $priority
        if (!isset(ModuleController::$subscriptions[$event])) {
            ModuleController::$subscriptions[$event] = array();
        }

        $lastKnownModule = ModuleController::$modules[count(ModuleController::$modules) - 1]->key();

        ModuleController::$subscriptions[$event][] = array(
            'functionToCall' => $action,
            'priority'       => $priority,
            'module'         => $lastKnownModule
        );
    }

    /**
     * Emit an event - this is for private use within the core ODR platform, and is not intended for use by third-party modules.
     * This triggers all of the functions that have hooked into the event.
     *
     * @param  String $event    The name of the event to emit.
     * @param  Dispute $dispute The current dispute.
     */
    public static function emit($event, $dispute) {
        $actions = ModuleController::$subscriptions[$event];
        foreach ($actions as $action) {
            if ($action['module'] === $dispute->getType()) {
                ModuleController::tryToCallFunction($action['functionToCall']);
            }
        }
    }

    private static function tryToCallFunction($functionToCall) {
        if (function_exists($functionToCall)) {
            call_user_func($functionToCall);
        }
        else {
            ModuleController::tryToCallClassFunction($functionToCall);
        }
    }

    private static function tryToCallClassFunction($functionToCall) {
        $classFunction = strpos($functionToCall, '->') !== false;
        if ($classFunction) {
            $parts = explode('->', $functionToCall);
            $class = $parts[0];
            $method = $parts[1];
            $classInstance = new $class();
            $classInstance->$method();
        }
        else {
            throw new Exception('Invalid event handler: ' . $functionToCall);
        }
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