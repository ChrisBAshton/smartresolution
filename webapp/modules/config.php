<?php
$configFilepath     = __DIR__ . '/config.json';
$configFileContents = @file_get_contents($configFilepath);

if (!$configFileContents) {
    $moduleNames = scandir(__DIR__);
    $modulesConfig = array();
    $ignoreFiles   = array('.', '..', '.DS_Store', '__MACOSX', 'config.php');
    foreach($moduleNames as $name) {
        if (!(in_array($name, $ignoreFiles))) {
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

// we need to make the Test module available to the Cucumber test suite.
if (IN_TEST_MODE) {
    $testModule = ModuleController::getModuleByKey('test');

    if (!$testModule->active()) {
        $testModule->toggleActiveness();
    }
}