<?php

/**
 * Global variable, used internally for defining the Home/Dispute dashboards. You should interact with it through the functions provided, e.g. dashboard_add_item().
 * @var array
 */
$dashboardActions = array();

/**
 * api.php contains all of the global methods exposed to the modules. Internally, these global methods call classes that are contained in the core platform, but modules should refrain from calling those classes. Modules should ONLY interact with the systme through the global methods defined here.
 */

/**
 * Defines the module in the system.
 * Decorator pattern. Syntactic sugar instead of calling ModuleController static function directly. Improves decoupling.
 * @param  array  $config                       Parameters:
 *         string $config['key']                Module unique ID, e.g. 'maritime_collision'
 *         string $config['title']              Module name, e.g. 'Maritime Collision'
 *         string $config['description']        Module description.
 * @param  Function $moduleDefinitionFunction   The module definition. This function should hook into events exposed by the SmartResolution platform and specify which functions to call on those events.
 */
function declare_module($config, $moduleDefinitionFunction) {
    ModuleController::registerModule($config);
    $moduleDefinitionFunction();
}

/**
 * Subscribes an anonymous function (defined within a module) to a given event.
 * @param  string           $eventName  Name of the event to hook into.
 * @param  String|function  $action     Action to perform.
 *         This can be a global function, e.g. 'hello' -> hello().
 *         Or it can be a public function inside a named class, e.g. 'MyClass->hello' -> new MyClass(); hello();
 *         Or it can be an anonymous function, e.g. function () { // do something }
 * @param  String|Int       $priority   (optional) The priority of the hooked function.
 *         If multiple functions hook into the event, the functions marked as the highest priority are executed first, e.g. in the case of `on('event', 'a', 'medium')` and `on('event', 'b', 'high')`, function `b` would be executed before function `a`, even though function `a` was the first to hook into the event.
 *         Possible values: 'low', 'medium', 'high', or an integer between 1 and 100 (where 1 is low priority and 100 is high).
 */
function on($eventName, $action, $priority = 'medium') {
    ModuleController::subscribe($eventName, $action, $priority);
}

/**
 * Defines a top-level route, e.g. '/example', and what to do when a user accesses that route via GET or POST.
 * If you want to define a route relative to the current dispute, e.g. '/disputes/1371/example', you should use the `route` function.
 *
 * @param  string          $route   GET|POST route relative to the root directory, e.g. '/example'
 * @param  String|Function $handler If String, should be the name of function to call (e.g. 'helloWorld') or the class name and public function, e.g. 'foo->helloWorld'. Could instead pass an anonymous function, e.g. function () {}
 */
function top_level_route($route, $handler) {
    ModuleController::defineRoute('GET|POST ' . $route, $handler);
}

/**
 * Defines a route relative to the current dispute. E.g. if you specify '/example', you'll actually create a route for '/disputes/DISPUTE_ID/example'. If you want to define a top-level route (i.e. simply '/example' without the disputes prefix), you should use `top_level_route`.
 *
 * @param  string          $route   GET|POST route relative to the dispute, e.g. '/example', which would correspond to '/disputes/DISPUTE_ID/example'
 * @param  String|Function $handler If String, should be the name of function to call (e.g. 'helloWorld') or the class name and public function, e.g. 'foo->helloWorld'. Could instead pass an anonymous function, e.g. function () {}
 */
function route($route, $handler) {
    ModuleController::defineRoute('GET|POST /disputes/@disputeID' . $route, $handler);
}

/**
 * Adds multiple items to the end of the dashboard, in the order passed. For full description and parameter list, see `dashboard_add_item`.
 */
function dashboard_add_items($items, $addToFront = false) {
    $items = array_reverse($items); // since each added item is pushed to the beginning of the array, and thus the beginning of the menu, if we want the items to appear in the menu in the order they were passed then we need to reverse the array.
    foreach($items as $item) {
        dashboard_add_item($item, $addToFront);
    }
}

/**
 * Adds an item to the end of the dashboard. The dashboard context depends on the event hooked into. For example, if called after hooking into the 'homescreen_dashboard' event, you will affect the homescreen dashboard, whereas if you call it after hooking into the 'dispute_dashboard' event, it will affect the dispute dashboard.
 *
 * @param  array   $params           Item to add to the dashboard.
 *         string  $params['title']  Title of the dashboard item.
 *         string  $params['image']  Icon to use.
 *         string  $params['href']   URL to link to.
 *
 * @param  boolean $addToFront      (Optional) If set to true, item will be added to beginning of dashboard. If false, it will be added to the end. Defaults to false.
 */
function dashboard_add_item($params, $addToFront = false) {
    global $dashboardActions;
    $item =  array(
        'title' => $params['title'],
        'image' => $params['image'],
        'href'  => $params['href']
    );
    if ($addToFront) {
        array_unshift($dashboardActions, $item);
    }
    else {
        array_push($dashboardActions, $item);
    }
}

/**
 * Gets the URL of the module directory. Useful for linking to module-specific assets.
 *
 * Example:
 * <code>
 * get_module_url() . '/assets/my_image.png';
 * </code>
 *
 * @return string URL to the module directory.
 */
function get_module_url() {
    $moduleName = ModuleController::extractModuleNameFromStackTrace(debug_backtrace());
    return '/modules/' . $moduleName;
}

/**
 * Gets the URL of the dispute that the module is hooked into.
 *
 * @return string  Dispute URL.
 */
function get_dispute_url() {
    $dispute = new Dispute(get_dispute_id());
    return $dispute->getUrl();
}

/**
 * Gets the ID of the dispute that the module is hooked into.
 *
 * @return int  Dispute ID.
 */
function get_dispute_id() {
    global $f3;
    return (int) $f3->get('PARAMS')['disputeID'];
}

function get_login_id() {
    return Session::getAccount()->getLoginId();
}

/**
 * Renders a HTML template.
 *
 * @param  string $template  Path to the template, e.g. get_module_url() . '/views/index.html'
 * @param  array  $variables (optional) Values to pass to the template, e.g. array('foo' => 'bar'), which would be accessible as $foo in the template.
 */
function render($template, $variables = array()) {
    mustBeLoggedIn(); // sets user's top menu, etc.
    global $f3;
    foreach($variables as $key => $value) {
        $f3->set($key, $value);
    }
    $f3->set('content', $template);
    echo View::instance()->render('layout.html');
}

/**
 * Renders a markdown file, within the website template.
 *
 * @param  string $template Path to the markdown file, e.g. get_module_url() . '/docs/about.md'
 */
function render_markdown($template) {
    mustBeLoggedIn(); // sets user's top menu, etc.
    global $f3;
    $f3->set('markdownFile', $template);
    $f3->set('content', 'markdown.html');
    echo View::instance()->render('layout.html');
}

/**
 * Creates a number of module-specific tables in the database.
 * @see    declare_table  The elements of the array you pass could instead be passed individually to the declare_table function.
 * @param  array $tables  Array of tables to create, in the form array('table_name' => array(columns))
 */
function declare_tables($tables) {
    $moduleName = ModuleController::extractModuleNameFromStackTrace(debug_backtrace());
    foreach($tables as $tableName => $columns) {
        ModuleController::initModuleTable($moduleName, $tableName, $columns);
    }
}

/**
 * Creates a module-specific table in the database, if the table does not already exist. If the table already exists, you'll need to manually delete it before your new declaration takes effect.
 * No need to worry about namespacing: SmartResolution does this automatically, so that your table name becomes module__[module_name]__[table_name] internally. This could change, however, and you should not ever need to know this.
 *
 * @param  string $tableName The name of the table you'd like to create.
 * @param  array  $columns   Array of columns describing your table, in the format 'column_name' => 'type', e.g. 'question_number' => 'INTEGER'
 */
function declare_table($tableName, $columns) {
    $moduleName = ModuleController::extractModuleNameFromStackTrace(debug_backtrace());
    ModuleController::initModuleTable($moduleName, $tableName, $columns);
}

/**
 * Gets the value of a column in a module-specific table.
 *
 * @param  string $tableAndColumn Dot-separated table and column, e.g. 'table_name.column_name'
 * @return Unknown|boolean        Returns the value as it is stored in the database. Beware: this does not cast to integer or boolean, so you'll need to manually cast type where appropriate. Returns boolean false if no record is found.
 */
function get($tableAndColumn) {
    $moduleName = ModuleController::extractModuleNameFromStackTrace(debug_backtrace());
    return ModuleController::queryModuleTable($moduleName, $tableAndColumn, get_dispute_id());
}

/**
 * Sets a value in the database.
 * @param  string $tableAndColumn Dot-separated table and column, e.g. 'table_name.column_name'
 * @param  Unknown $value         The value to set. Depending on the field type, you may pass a string, integer, etc.
 * @return true                   @TODO. Right now, this always returns true. In future this may change to be more useful.
 */
function set($tableAndColumn, $value) {
    $moduleName = ModuleController::extractModuleNameFromStackTrace(debug_backtrace());
    return ModuleController::setModuleTableValue($moduleName, $tableAndColumn, $value, get_dispute_id());
}

function createRow($table, $values = array()) {
    $moduleName = ModuleController::extractModuleNameFromStackTrace(debug_backtrace());
    return ModuleController::createModuleTableRow($moduleName, $table, $values, get_dispute_id());
}