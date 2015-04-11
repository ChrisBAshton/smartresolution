<?php

class ModuleController {

    private static $modules = array();
    private static $routes  = array();
    private static $subscriptions = array();

    /**
     * Extracts the name of the module from the results of the `debug_backtrace` function.
     * This means we don't have to manually pass the module name from inside a module definition, making the API cleaner from the perspective of the module developers.
     *
     * @param  array $stackTrace The stack trace.
     * @return string            The module name.
     */
    public static function extractModuleNameFromStackTrace($stackTrace) {
        foreach($stackTrace as $trace) {
            if ($trace['function'] === 'declare_module') {
                return $trace['args'][0]['key'];
            }
        }

        // $moduleLocation = $stackTrace[0]['file'];
        // preg_match('/modules\/([^\/]+)/', $moduleLocation, $results);
        // $moduleName = $results[1];
        // return $moduleName;

        throw new Exception('Could not detect module name.');
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
        $priority        = ModuleController::getPriority($priority);

        $newElement = array(
            'functionToCall' => $action,
            'priority'       => $priority,
            'module'         => $lastKnownModule
        );

        ModuleController::insertSubscriptionIntoArray($event, $newElement);
    }

    private static function insertSubscriptionIntoArray($event, $newElement) {
        $existingSubscriptions = ModuleController::$subscriptions[$event];

        $inserted = false;
        $count = 0;
        foreach($existingSubscriptions as $subscription) {
            if ($subscription['priority'] < $newElement['priority']) {
                // insert our new subscription at this point
                array_splice(ModuleController::$subscriptions[$event], $count, 0, array($newElement));
                $inserted = true;
                break;
            }
            $count++;
        }

        if (!$inserted) { // our new element had the lowest priority (or was the first element) so we just append.
            ModuleController::$subscriptions[$event][] = $newElement;
        }
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
     * @param  Dispute $dispute   (Optional) The current dispute. This is needed so that we can check the dispute type, and therefore only trigger the functions that have been subscribed to from the corresponding module. If the event has nothing to do with a dispute, e.g. the module simply wants to display a message on the homescreen, then nothing needs to be passed.
     * @param  array  $parameters (Optional) Parameters to pass to the functions that have hooked into the event. The array gets converted to full parameters, i.e. [a, b] => func(a, b)
     */
    public static function emit($event, $dispute = false, $parameters = array()) {
        $actions = @ModuleController::$subscriptions[$event];
        // if no modules have hooked into the event, $actions will be null
        if ($actions) {
            foreach ($actions as $action) {
                if (!$dispute || $action['module'] === $dispute->getType()) {
                    new FunctionParser($action['functionToCall'], $parameters);
                }
            }
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

    public static function queryModuleTable($moduleName, $tableAndColumn, $disputeID, $andClause) {
        $column  = ModuleController::extractColumnName($tableAndColumn);
        $results = ModuleController::getRowsFromModuleTable($moduleName, $tableAndColumn, $disputeID, $andClause);

        if (count($results) === 1) {
            return $results[0][$column];
        }
        else if (count($results) > 1) {
            throw new Exception('Query returned multiple results. If you were expecting this, please call get_multiple() instead.');
        }

        return false; // no record was found
    }

    public static function getRowsFromModuleTable($moduleName, $tableAndColumn, $disputeID, $andClause) {
        $table  = ModuleController::extractTableName($tableAndColumn);
        $column = ModuleController::extractColumnName($tableAndColumn);
        $values = ModuleController::createQueryValuesArray($disputeID, $andClause);

        $condition = '';
        foreach($andClause as $key => $value) {
            $condition = $condition . ' AND ' . $key . ' = :' . $key;
        }

        $query = 'SELECT ' . $column . ' FROM module__' . $moduleName . '__' . $table . ' WHERE dispute_id = :dispute_id' . $condition;
        $results = Database::instance()->exec($query, $values);

        return $results;
    }

    public static function createModuleTableRow($moduleName, $table, $valuesToSet, $disputeID) {
        $table   = 'module__' . $moduleName . '__' . $table;
        $columns = 'dispute_id';
        $values  = ModuleController::createQueryValuesArray($disputeID, $valuesToSet);

        $placeholders = ':dispute_id';
        foreach($valuesToSet as $column => $value) {
            $columns      = $columns . ', ' . $column;
            $placeholders = $placeholders  . ', :' . $column;
        }

        $query = 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $placeholders . ')';
        Database::instance()->exec($query, $values);
    }

    private static function createQueryValuesArray($disputeID, $columnValues) {
        $values = array(
            ':dispute_id' => $disputeID
        );
        foreach($columnValues as $key => $value) {
            $values[':' . $key] = $value;
        }
        return $values;
    }

    public static function setModuleTableValue($moduleName, $tableAndColumn, $value, $disputeID) {
        $table  = 'module__' . $moduleName . '__' . ModuleController::extractTableName($tableAndColumn);
        $column = ModuleController::extractColumnName($tableAndColumn);

        Database::instance()->exec(
            'UPDATE ' . $table . ' SET ' . $column . ' = :value WHERE dispute_id = :dispute_id',
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