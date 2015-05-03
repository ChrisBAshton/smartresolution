<?php

/**
 * Links admin-related HTTP requests to admin-related functions and views.
 */
class AdminController {

    private $moduleDirectory;

    /**
     * AdminController constructor.
     */
    function __construct() {
        $this->moduleDirectory = __DIR__ . '/../../modules';
    }

    /**
     * Shows the SmartResolution Marketplace.
     * @param  F3 $f3         The base F3 object
     */
    function showMarketplace($f3) {
        $account = mustBeLoggedInAsAn('Admin');
        $modules = json_decode(file_get_contents('http://smartresolution.org/marketplace/feed'), true);
        $f3->set('modules', $modules);
        $f3->set('content', 'admin_marketplace.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * Shows modules page, showing which modules are installed and whether or not they are active.
     * @param  F3 $f3 The base F3 object.
     */
    function showModulesPage($f3) {
        $account = mustBeLoggedInAsAn('Admin');

        $modules = ModuleController::instance()->getAllModules();

        $f3->set('modules', $modules);
        $f3->set('content', 'admin_modules.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * Shows the Admin Customise page.
     * @param  F3 $f3 The base F3 object.
     */
    function showCustomisePage($f3) {
        $account = mustBeLoggedInAsAn('Admin');
        $f3->set('content', 'admin_customise.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * POST function; toggles a given module's activity state. Then redirects to modules page.
     * @param  F3 $f3 The base F3 object.
     */
    function toggleModule($f3) {
        $moduleName = $f3->get('POST.module');
        $module = ModuleController::instance()->getModuleByKey($moduleName);
        $module->toggleActiveness();
        header('Location: /admin-modules');
    }

    /**
     * POST function; deletes the given module. Then redirects to modules page.
     * @param  F3 $f3 The base F3 object.
     */
    function deleteModule($f3) {
        mustBeLoggedInAsAn('Admin');
        $moduleName = $f3->get('GET.id');
        $moduleDirectory = $this->moduleDirectory . '/' . $moduleName;
        $this->rrmdir($moduleDirectory);
        $this->updateModuleConfig();
        header('Location: /admin-modules');
    }

    /**
     * POST function; downloads the given module to the system. Then redirects to modules page.
     * If the module could not be installed, an error page is triggered.
     *
     * @param  F3 $f3 The base F3 object.
     */
    function downloadModule($f3) {
        $account      = mustBeLoggedInAsAn('Admin');
        $downloadLink = $f3->get('GET.url');
        $zipLocation  = $this->moduleDirectory . '/tmp.zip';

        file_put_contents($zipLocation, file_get_contents($downloadLink));

        $zip = new ZipArchive;
        $response = $zip->open($zipLocation);
        if ($response === TRUE) {
            $zip->extractTo($this->moduleDirectory);
            $zip->close();
            unlink($zipLocation);
            $this->updateModuleConfig();
            header('Location: /admin-modules');
        } else {
            errorPage('There was a problem installing your module.');
        }
    }

    /**
     * Removes the config.json representing the installed modules and their activity states.
     * On the next page load, config.json will be re-initialised; it will look for every directory
     * in the modules folder and put the declared module in the JSON. All modules will be inactive
     * (except for special modules). One day, this should be improved so that installed modules that
     * were already active remain active after updating the module config.
     */
    function updateModuleConfig() {
        unlink($this->moduleDirectory . '/config.json');
    }

    /**
     * Recursively removes a directory. Copied from:
     *     http://php.net/rmdir#98622
     *
     * @param  string $dir Filepath to directory to remove.
     */
    function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        $this->rrmdir($dir."/".$object);
                    }
                    else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

}
