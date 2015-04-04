<?php

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
 * Adds multiple items to the dashboard, in the order passed.
 *
 * @param  Array<$item> $items      Items to add to the dashboard.
 *         string $item['title']    Title of the dashboard item.
 *         string $item['image']    Icon to use.
 *         string $item['href']     URL to link to.
 */
function dashboard_add_items($items) {
    $items = array_reverse($items); // since each added item is pushed to the beginning of the array, and thus the beginning of the menu, if we want the items to appear in the menu in the order they were passed then we need to reverse the array.
    foreach($items as $item) {
        dashboard_add_item($item);
    }
}

/**
 * Adds an item to the dashboard.
 *
 * @param  array  $params           Item to add to the dashboard.
 *         string $params['title']  Title of the dashboard item.
 *         string $params['image']  Icon to use.
 *         string $params['href']   URL to link to.
 */
function dashboard_add_item($params) {
    // @TODO - call a method on DisputeStateCalculator rather than directly modifying its attribute.
    array_unshift(DisputeStateCalculator::$actions, array(
        'title' => $params['title'],
        'image' => $params['image'],
        'href'  => $params['href']
    ));
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

function get_dispute_property($property) {
    $moduleName = ModuleController::extractModuleNameFromStackTrace(debug_backtrace());
    // @TODO use moduleName in determining which table to draw data from
}

function set_dispute_property($property, $value) {
    $moduleName = ModuleController::extractModuleNameFromStackTrace(debug_backtrace());
    // @TODO use moduleName in determining which table to save data to
}