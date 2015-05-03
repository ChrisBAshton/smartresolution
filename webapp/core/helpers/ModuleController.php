<?php

/**
 * The adaptor between the global module API functions and the core platform.
 */
class ModuleController extends Prefab {

    private $modules = array();
    private $routes  = array();
    private $subscriptions = array();

    /**
     * Registers a module to the system.
     * @see declare_module
     */
    public function registerModule($config, $moduleDefinitionFunction) {
        global $modulesConfig;
        $module = new Module($config, $modulesConfig[$config['key']], $moduleDefinitionFunction);
        array_push($this->modules, $module);
        if ($module->active()) {
            $module->callModuleDefinitionFunction();
        }
        return $module;
    }

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

    /**
     * Returns an array of all of the modules in the system.
     * @return array<Module>
     */
    public function getAllModules() {
        return $this->modules;
    }

    /**
     * Returns an array of all of the active modules in the system.
     * @return array<Module>
     */
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

    /**
     * Returns the Module object corresponding to the unique ID (the key) provided, or false if the module could not be found.
     * @param  string $key Module key.
     * @return Module|false
     */
    public function getModuleByKey($key) {
        $modules = $this->modules;
        foreach($modules as $module) {
            if ($key === $module->key()) {
                return $module;
            }
        }
        return false;
    }

    /**
     * Defines an F3 route.
     * @param  string          $route   HTTP request, e.g. GET /disputes/@disputeID/custom-page
     * @param  string|function $handler Anonymous function, global function or class function to handle the HTTP request.
     */
    public function defineRoute($route, $handler) {
        array_push($this->routes, array(
            'route'   => $route,
            'handler' => $handler
        ));
    }

    /**
     * Returns an array of all of the module-specific routes in the following form:
     *
     *  array(
     *      array(
     *          'route'  => $route,
     *          'handler'=> $handler
     *      ),
     *      // and so on
     *  )
     *
     * @return array Array of routes.
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Subscribes an action to an event. Optionally, if a priority is provided that is taken into account when deciding where to insert the action in the array of subscribed actions.
     * @param  string           $event    Event name, e.g. 'dispute_dashboard'
     * @param  string|function  $action   Anonymous or global function, or class function, to call when event is emitted.
     * @param  string|integer   $priority Integer between 0 and 100, or a string value of 'high', 'medium' or 'low'.
     */
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

    /**
     * Inserts the subscribed action into an internal array of actions, ready to be executed later.
     * @param  string $event                  Event name, e.g. 'dispute_dashboard'
     * @param  array  $newElement             Array representing subscribed action.
     *         int    $newElement['priority'] Priority of the subscription, converted to int.
     *         string $newElement['module']   Name of the module that the subscription is coming from.
     *         string|function $newElement['functionToCall'] Anonymous, global or class function to call when event is emitted.
     */
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
     * @param  string|int|float $priority Priority, e.g. 'high'
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

    /**
     * Creates a module-specific table, e.g.
     *
     *   initModuleTable('module_name', 'table_name', array(
     *       'id_of_first_agent'  => 'INTEGER DEFAULT 0',
     *       'id_of_second_agent' => 'INTEGER DEFAULT 0'
     *   ))
     *
     *  This doesn't create a table called 'table_name' - it is actually namespaced to 'module__module_name__table_name', but the module developer need not know this.
     *
     * @param  string $moduleName Name of the module.
     * @param  string $tableName  Name of the table which the module developer wishes to refer to throughout their module.
     * @param  array  $columns    Associative array of column names => column types.
     */
    public function initModuleTable($moduleName, $tableName, $columns) {
        $sqlString = '';
        foreach($columns as $columnName => $type) {
            $sqlString = $sqlString . ', ' . $columnName . ' ' . $type;
        }
        $query = 'CREATE TABLE IF NOT EXISTS module__' . $moduleName . '__' . $tableName . '(dispute_id INTEGER NOT NULL' . $sqlString . ');';
        Database::instance()->exec($query);
    }

    /**
     * Returns a single row from the given table, or if nothing was found, the boolean `false`.
     * @param  string $moduleName     Name of the module.
     * @param  string $tableAndColumn Name of the table and column, separated by a period, e.g. 'my_table.my_column'
     * @param  int    $disputeID      ID of the dispute.
     * @param  array  $andClause      Associative array of additional WHERE/AND constraints.
     * @return array|false
     */
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

    /**
     * Returns multiple rows from the database. If nothing is found, an empty array is returned.
     *
     * @param  string $moduleName     Name of the module.
     * @param  string $tableAndColumn Name of the table and column, separated by a period, e.g. 'my_table.my_column'
     * @param  int    $disputeID      ID of the dispute.
     * @param  array  $andClause      Associative array of additional WHERE/AND constraints.
     * @return array
     */
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

    /**
     * Creates a row in the database.
     * @param  string $moduleName     Name of the module.
     * @param  string $table          Name of the table.
     * @param  array  $valuesToSet    Associative array of $columnName => $value to set.
     * @param  int    $disputeID      ID of the dispute.
     */
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

    /**
     * Takes an associative array and converts to a PDO-friendly query, also adding the neccessary disputeID row. Example:
     *
     *  array('foo' => 'bar', 'name' => 123);
     *
     *  => array(':dispute_id' => 1337, ':foo' => 'bar', ':name' => 123)
     *
     * @param  int   $disputeID    ID of the current dispute.
     * @param  array $columnValues Associative array of $columnName => $value
     * @return array               Query values array.
     */
    private function createQueryValuesArray($disputeID, $columnValues) {
        $values = array(
            ':dispute_id' => $disputeID
        );
        foreach($columnValues as $key => $value) {
            $values[':' . $key] = $value;
        }
        return $values;
    }

    /**
     * Sets a value in the module table. Only intended on tables that only have one row per dispute ID, i.e. persistent configuration. Example:
     *
     *  setModuleTableValue('my_module', 'my_table.user_agrees', true, 1337);
     *
     * @param string $moduleName     Name of the module.
     * @param string $tableAndColumn Name of the table and column, separated by a period, e.g. 'my_table.my_column'
     * @param mixed  $value          Value to set.
     * @param int    $disputeID      ID of the dispute.
     */
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

    /**
     * Extracts the table name from a table and column pair. 'table.column' => 'table'
     * @param  string $tableAndColumn Table and column pair.
     * @return string                 Table name.
     */
    private function extractTableName($tableAndColumn) {
        $parts  = explode('.', $tableAndColumn);
        return $parts[0];
    }

    /**
     * Extracts the column name from a table and column pair. 'table.column' => 'column'
     * @param  string $tableAndColumn Table and column pair.
     * @return string                 Column name.
     */
    private function extractColumnName($tableAndColumn) {
        $parts  = explode('.', $tableAndColumn);
        return $parts[1];
    }
}