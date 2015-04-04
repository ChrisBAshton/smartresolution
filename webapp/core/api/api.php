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

function top_level_route($route, $handler) {
    ModuleController::defineRoute('GET|POST ' . $route, $handler);
}

function route($route, $handler) {
    ModuleController::defineRoute('GET|POST /disputes/@disputeID' . $route, $handler);
}

/**
 * Adds multiple items to the dashboard, in the order passed.
 */
function dashboard_add_items($items) {
    $items = array_reverse($items); // since each added item is pushed to the beginning of the array, and thus the beginning of the menu, if we want the items to appear in the menu in the order they were passed then we need to reverse the array.
    foreach($items as $item) {
        dashboard_add_item($item);
    }
}

/**
 * Adds an item to the dashboard.
 */
function dashboard_add_item($params) {
    // @TODO - call a method on DisputeStateCalculator rather than directly modifying its attribute.
    array_unshift(DisputeStateCalculator::$actions, array(
        'title' => $params['title'],
        'image' => $params['image'],
        'href'  => $params['href']
    ));
}

function get_module_url() {
    $moduleLocation = debug_backtrace()[0]['file'];
    preg_match('/modules\/([^\/]+)/', $moduleLocation, $results);
    $moduleName = $results[1];
    return '/modules/' . $moduleName;
}

function get_dispute_url() {
    // @TODO - call a method on DisputeStateCalculator rather than directly retrieving its attribute.
    return DisputeStateCalculator::$dispute->getUrl();
}

function render($template, $variables = array()) {
    mustBeLoggedIn(); // sets user's top menu, etc.
    global $f3;
    foreach($variables as $key => $value) {
        $f3->set($key, $value);
    }
    $f3->set('content', $template);
    echo View::instance()->render('layout.html');
}

function render_markdown($template) {
    mustBeLoggedIn(); // sets user's top menu, etc.
    global $f3;
    $f3->set('markdownFile', $template);
    $f3->set('content', 'markdown.html');
    echo View::instance()->render('layout.html');
}