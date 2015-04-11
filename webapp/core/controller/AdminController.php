<?php

class AdminController {

    function showMarketplace($f3, $params) {
        $account = mustBeLoggedInAsAn('Admin');
        $modules = json_decode(file_get_contents('http://smartresolution.org/marketplace/feed'), true);
        $f3->set('modules', $modules);
        $f3->set('content', 'admin_marketplace.html');
        echo View::instance()->render('layout.html');
    }

    function showModulesPage($f3, $params) {
        $account = mustBeLoggedInAsAn('Admin');

        $modules = ModuleController::getAllModules();

        $f3->set('modules', $modules);
        $f3->set('content', 'admin_modules.html');
        echo View::instance()->render('layout.html');
    }

    function toggleModule($f3, $params) {
        $moduleName = $f3->get('POST.module');
        $module = ModuleController::getModuleByKey($moduleName);
        $module->toggleActiveness();
        header('Location: /admin-modules');
    }

    function deleteModule($f3, $params) {
        $account = mustBeLoggedInAsAn('Admin');
        $moduleName = $f3->get('GET.id');
        global $modulesConfig;
        unset($modulesConfig[$moduleName]);
        file_put_contents(__DIR__ . '/../../modules/config.json', json_encode($modulesConfig));
        shell_exec('rm -r ' . __DIR__ . '/../../modules/' . $moduleName);
        header('Location: /admin-modules');
    }

    function showCustomisePage($f3, $params) {
        $account = mustBeLoggedInAsAn('Admin');
        $f3->set('content', 'admin_customise.html');
        echo View::instance()->render('layout.html');
    }
}
