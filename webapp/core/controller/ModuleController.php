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
     * @param  string $event      The name of the event to emit.
     * @param  Dispute $dispute   The current dispute. This is needed so that we can check the dispute type, and therefore only trigger the functions that have been subscribed to from the corresponding module.
     * @param  array  $parameters Parameters to pass to the functions that have hooked into the event. The array gets converted to full parameters, i.e. [a, b] => func(a, b)
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
        // if $functionToCall is an anonymous function
        if (is_callable($functionToCall)) {
            $functionToCall($parameters);
        }
        // if $functionToCall is the name (string) of a global function
        else if (function_exists($functionToCall)) {
            call_user_func_array($functionToCall, $parameters);
        }
        else {
            // $functionToCall is the name of a class function (e.g. 'Foo->bar') and needs to be parsed
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

    public static function initModuleTable($moduleName, $tableName, $columns) {
        $sqlString = '';
        foreach($columns as $columnName => $type) {
            $sqlString = $sqlString . ', ' . $columnName . ' ' . $type;
        }
        $query = 'CREATE TABLE IF NOT EXISTS module__' . $moduleName . '__' . $tableName . '(dispute_id INTEGER NOT NULL' . $sqlString . ');';
        Database::instance()->exec($query);
    }

    public static function queryModuleTable($moduleName, $tableAndColumn, $disputeID) {
        $table  = ModuleController::extractTableName($tableAndColumn);
        $column = ModuleController::extractColumnName($tableAndColumn);

        $results = Database::instance()->exec(
            'SELECT ' . $column . ' FROM module__' . $moduleName . '__' . $table . ' WHERE dispute_id = :dispute_id', array(':dispute_id' => $disputeID)
        );

        if (count($results) === 1) {
            return $results[0][$column];
        }
        else if (count($results) > 1) {
            throw new Exception('Query returned multiple results, but SmartResolution does not support multiple results yet!!!');
        }

        return false; // no record was found
    }

    public static function setModuleTable($moduleName, $tableAndColumn, $value, $disputeID) {
        $table  = 'module__' . $moduleName . '__' . ModuleController::extractTableName($tableAndColumn);
        $column = ModuleController::extractColumnName($tableAndColumn);

        $results = Database::instance()->exec(
            'SELECT ' . $column . ' FROM ' . $table . ' WHERE dispute_id = :dispute_id',
            array(':dispute_id' => $disputeID)
        );

        if (count($results) === 0) {
            $query = 'INSERT INTO ' . $table . ' (dispute_id, ' . $column . ') VALUES (:dispute_id, :value)';
        }
        else {
            $query = 'UPDATE ' . $table . ' SET ' . $column . ' = :value WHERE dispute_id = :dispute_id';
        }

        Database::instance()->exec(
            $query,
            array(
                ':dispute_id' => $disputeID,
                ':value'      => $value
            )
        );

        return true; // query was successful. @TODO return false/raise exception if there is a problem
    }

    private static function extractTableName($tableAndColumn) {
        $parts  = explode('.', $tableAndColumn);
        return $parts[0];
    }

    private static function extractColumnName($tableAndColumn) {
        $parts  = explode('.', $tableAndColumn);
        return $parts[1];
    }
}