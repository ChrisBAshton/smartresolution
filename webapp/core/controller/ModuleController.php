<?php

class ModuleController extends Prefab {

    private $modules = array();
    private $routes  = array();
    private $subscriptions = array();

    /**
     * Extracts the name of the module from the results of the `debug_backtrace` function.
     * This means we don't have to manually pass the module name from inside a module definition, making the API cleaner from the perspective of the module developers.
     *
     * @param  array $stackTrace The stack trace.
     * @return string            The module name.
     */
    public function extractModuleNameFromStackTrace($stackTrace) {
        // the module name can be detected in one of two ways.
        // The first and most reliable way is to get the module name argument
        // from the declare_module() function call.
        foreach($stackTrace as $trace) {
            if ($trace['function'] === 'declare_module') {
                return $trace['args'][0]['key'];
            }
        }

        // if the declare_module function isn't in the stack trace, our next
        // best bet is to get the name of the file where the function was triggered,
        // as this ought to be modules/NAME/index.php.
        $moduleLocation = $stackTrace[0]['file'];
        preg_match('/modules\/([^\/]+)/', $moduleLocation, $results);
        $moduleName = $results[1];
        return $moduleName;

        Utils::instance()->throwException('Could not detect module name.');
    }

    public function registerModule($config, $moduleDefinitionFunction) {
        global $modulesConfig;
        $module = new Module($config, $modulesConfig[$config['key']], $moduleDefinitionFunction);
        array_push($this->modules, $module);
        if ($module->active()) {
            $module->callModuleDefinitionFunction();
        }
        return $module;
    }

    public function getActiveModules() {
        $modules    = array();
        $allModules = $this->modules;
        foreach($allModules as $module) {
            if ($module->active()) {
                array_push($modules, $module);
            }
        }
        return $modules;
    }

    public function getAllModules() {
        return $this->modules;
    }

    public function getModuleByKey($key) {
        $modules = $this->modules;
        foreach($modules as $module) {
            if ($key === $module->key()) {
                return $module;
            }
        }
        return false;
    }

    public function defineRoute($route, $handler) {
        array_push($this->$routes, array(
            'route'   => $route,
            'handler' => $handler
        ));
    }

    public function getRoutes() {
        return $this->$routes;
    }

    public function subscribe($event, $action, $priority = 20) {

        if (!isset($this->subscriptions[$event])) {
            $this->subscriptions[$event] = array();
        }

        $lastKnownModule = $this->modules[count($this->modules) - 1]->key();
        $priority        = $this->getPriority($priority);

        $newElement = array(
            'functionToCall' => $action,
            'priority'       => $priority,
            'module'         => $lastKnownModule
        );

        $this->insertSubscriptionIntoArray($event, $newElement);
    }

    private function insertSubscriptionIntoArray($event, $newElement) {
        $existingSubscriptions = $this->subscriptions[$event];

        $inserted = false;
        $count = 0;
        foreach($existingSubscriptions as $subscription) {
            if ($subscription['priority'] < $newElement['priority']) {
                // insert our new subscription at this point
                array_splice($this->subscriptions[$event], $count, 0, array($newElement));
                $inserted = true;
                break;
            }
            $count++;
        }

        if (!$inserted) { // our new element had the lowest priority (or was the first element) so we just append.
            array_push($this->subscriptions[$event], $newElement);
        }
    }

    /**
     * Converts a string/int/float into an int representing priority.
     * @param  String|int|float $priority Priority, e.g. 'high'
     * @return int                        Priority as an integer
     */
    private function getPriority($priority) {
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
    public function emit($event, $dispute = false, $parameters = array()) {
        $actions = @$this->subscriptions[$event];
        // if no modules have hooked into the event, $actions will be null
        if ($actions) {
            foreach ($actions as $action) {
                if (!$dispute || $action['module'] === $dispute->getType()) {
                    new FunctionParser($action['functionToCall'], $parameters);
                }
            }
        }
    }

    public function initModuleTable($moduleName, $tableName, $columns) {
        $sqlString = '';
        foreach($columns as $columnName => $type) {
            $sqlString = $sqlString . ', ' . $columnName . ' ' . $type;
        }
        $query = 'CREATE TABLE IF NOT EXISTS module__' . $moduleName . '__' . $tableName . '(dispute_id INTEGER NOT NULL' . $sqlString . ');';
        Database::instance()->exec($query);
    }

    public function queryModuleTable($moduleName, $tableAndColumn, $disputeID, $andClause) {
        $column  = $this->extractColumnName($tableAndColumn);
        $results = $this->getRowsFromModuleTable($moduleName, $tableAndColumn, $disputeID, $andClause);

        if (count($results) === 1) {
            return $results[0][$column];
        }
        else if (count($results) > 1) {
            Utils::instance()->throwException('Query returned multiple results. If you were expecting this, please call get_multiple() instead.');
        }

        return false; // no record was found
    }

    public function getRowsFromModuleTable($moduleName, $tableAndColumn, $disputeID, $andClause) {
        $table  = $this->extractTableName($tableAndColumn);
        $column = $this->extractColumnName($tableAndColumn);
        $values = $this->createQueryValuesArray($disputeID, $andClause);

        $condition = '';
        foreach($andClause as $key => $value) {
            $condition = $condition . ' AND ' . $key . ' = :' . $key;
        }

        $query = 'SELECT ' . $column . ' FROM module__' . $moduleName . '__' . $table . ' WHERE dispute_id = :dispute_id' . $condition;
        $results = Database::instance()->exec($query, $values);

        return $results;
    }

    public function createModuleTableRow($moduleName, $table, $valuesToSet, $disputeID) {
        $table   = 'module__' . $moduleName . '__' . $table;
        $columns = 'dispute_id';
        $values  = $this->createQueryValuesArray($disputeID, $valuesToSet);

        $placeholders = ':dispute_id';
        foreach($valuesToSet as $column => $value) {
            $columns      = $columns . ', ' . $column;
            $placeholders = $placeholders  . ', :' . $column;
        }

        $query = 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $placeholders . ')';
        Database::instance()->exec($query, $values);
    }

    private function createQueryValuesArray($disputeID, $columnValues) {
        $values = array(
            ':dispute_id' => $disputeID
        );
        foreach($columnValues as $key => $value) {
            $values[':' . $key] = $value;
        }
        return $values;
    }

    public function setModuleTableValue($moduleName, $tableAndColumn, $value, $disputeID) {
        $table  = 'module__' . $moduleName . '__' . $this->extractTableName($tableAndColumn);
        $column = $this->extractColumnName($tableAndColumn);

        Database::instance()->exec(
            'UPDATE ' . $table . ' SET ' . $column . ' = :value WHERE dispute_id = :dispute_id',
            array(
                ':dispute_id' => $disputeID,
                ':value'      => $value
            )
        );

        // @TODO - put above in a try-catch and return false if exception thrown.
        return true;
    }

    private function extractTableName($tableAndColumn) {
        $parts  = explode('.', $tableAndColumn);
        return $parts[0];
    }

    private function extractColumnName($tableAndColumn) {
        $parts  = explode('.', $tableAndColumn);
        return $parts[1];
    }
}