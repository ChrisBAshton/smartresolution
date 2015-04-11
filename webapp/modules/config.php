<?php
$configFilepath     = __DIR__ . '/config.json';
$configFileContents = @file_get_contents($configFilepath);

if (!$configFileContents) {
    $moduleNames = scandir(__DIR__);
    $modulesConfig = array();
    foreach($moduleNames as $name) {
        if ($name !== '.' && $name !== '..' && $name !== 'config.php') {
            $active = $name === 'other';
            $modulesConfig[$name] = $active;
        }
    }
    file_put_contents($configFilepath, json_encode($modulesConfig));
}
else {
    $modulesConfig = json_decode($configFileContents, true);
}

foreach($modulesConfig as $moduleKey => $active) {
    require __DIR__ . '/' . $moduleKey . '/index.php';
}