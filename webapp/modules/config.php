<?php
/**
 * This file is a temporary solution to telling the system which modules are 'active' and which are not.
 * In the long term, I'd like to have an admin account and dashboard facility as in WordPress, to install, uninstall, activate and deactivate modules using a nice GUI. And that information would live in the database.
 */
$modulesConfig = array(
    'other'              => true,
    'test'               => true,
    'maritime_collision' => false
);
foreach($modulesConfig as $moduleKey => $active) {
    require __DIR__ . '/' . $moduleKey . '/index.php';
}