<?php

class ModuleController {

    private static $modules = array();
    private static $routes  = array();
    private static $subscriptions = array();

    public static function extractModuleNameFromStackTrace($trace) {
        $moduleLocation = $trace[0]['file'];
        preg_match('/modules\/([^\/]+)/', $moduleLocation, $results);
        $moduleName = $results[1];
        return $moduleName;
    }

    public static function registerModule($config) {
        ModuleController::$modules[] = new Module($config);
    }

    public static function getModules() {
        return ModuleController::$modules;
    }

    public static function defineRoute($route, $handler) {
        ModuleController::$routes[] = array(
            'route'   => $route,
            'handler' => $handler
        );
    }

    public static function getRoutes() {
        return ModuleController::$routes;
    }

    public static function subscribe($event, $action, $priority = 20) {

        if (!isset(ModuleController::$subscriptions[$event])) {
            ModuleController::$subscriptions[$event] = array();
        }

        $lastKnownModule = ModuleController::$modules[count(ModuleController::$modules) - 1]->key();

        $priority = ModuleController::getPriority($priority);

        // @TODO use $priority to determine where this item is pushed in the array.
        ModuleController::$subscriptions[$event][] = array(
            'functionToCall' => $action,
            'priority'       => $priority,
            'module'         => $lastKnownModule
        );
    }

    /**
     * Converts a string/int/float into an int representing priority.
     * @param  String|int|float $priority Priority, e.g. 'high'
     * @return int                        Priority as an integer
     */
    private static function getPriority($priority) {
        switch($priority) {
            case 'high':
                return 80;
            case 'medium':
                return 40;
            case 'low':
                return 10;
            default:
                return (int) $priority;
        }
    }

    /**
     * Emit an event - this is for private use within the core ODR platform, and is not intended for use by third-party modules.
     * This triggers all of the functions that have hooked into the event.
     *
     * @param  String $event      The name of the event to emit.
     * @param  Dispute $dispute   The current dispute. This is needed so that we can check the dispute type, and therefore only trigger the functions that have been subscribed to from the corresponding module.
     * @param  Array  $parameters Parameters to pass to the functions that have hooked into the event. The array gets converted to full parameters, i.e. [a, b] => func(a, b)
     */
    public static function emit($event, $dispute, $parameters = array()) {
        $actions = @ModuleController::$subscriptions[$event];
        // if no modules have hooked into the event, $actions will be null
        if ($actions) {
            foreach ($actions as $action) {
                if ($action['module'] === $dispute->getType()) {
                    ModuleController::tryToCallFunction($action['functionToCall'], $parameters);
                }
            }
        }
    }

    private static function tryToCallFunction($functionToCall, $parameters) {
        if (function_exists($functionToCall)) {
            call_user_func_array($functionToCall, $parameters);
        }
        else {
            ModuleController::tryToCallClassFunction($functionToCall, $parameters);
        }
    }

    private static function tryToCallClassFunction($functionToCall, $parameters) {
        $classFunction = strpos($functionToCall, '->') !== false;
        if ($classFunction) {
            $parts = explode('->', $functionToCall);
            $class = $parts[0];
            $method = $parts[1];
            $classInstance = new $class();
            call_user_func_array(array($classInstance, $method), $parameters);
        }
        else {
            throw new Exception('Invalid event handler: ' . $functionToCall);
        }
    }
}