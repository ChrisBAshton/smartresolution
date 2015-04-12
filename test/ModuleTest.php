<?php
require_once __DIR__ . '/../webapp/autoload.php';

$eventFired = false;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();
    }

    public static function tearDownAfterClass() {
        shell_exec('rm ' . __DIR__ . '/../webapp/modules/config.json');
    }

    public function testDeclareModule() {

        $allModules = ModuleController::getAllModules();
        $this->assertEquals(0, count($allModules));

        declare_module(array(
            'key'         => 'unit_test',
            'title'       => 'Test Module used by unit tests',
            'description' => ''
        ), function () {

            on('arbitrary_event', function () {
                global $eventFired;
                $eventFired = true;
            });

            on('test_dashboard', function () {
                dashboard_add_item(array(
                    'title' => 'item2', 'image' => '', 'href'  => ''
                ));
                // item should be added on to the end
                dashboard_add_item(array(
                    'title' => 'item3', 'image' => '', 'href'  => ''
                ));
                // item should be pushed to front. Order should now be 1,2,3.
                dashboard_add_item(array(
                    'title' => 'item1', 'image' => '', 'href'  => ''
                ), true);
            });

            // these two combined test the priority.
            // despite the low priority one being declared first, the high priority one should be
            // executed first and therefore the order of the dashboard items should be 'high', 'med1', 'med2', 'low',
            on('test_priority', function () {
                dashboard_add_item(array(
                    'title' => 'this was added with LOW priority', 'image' => '', 'href'  => ''
                ));
            }, 'low');
            on('test_priority', function () {
                dashboard_add_item(array(
                    'title' => 'this was added with HIGH priority', 'image' => '', 'href'  => ''
                ));
            }, 'high');
            on('test_priority', function () {
                dashboard_add_item(array(
                    'title' => 'this was added with MEDIUM priority (1)', 'image' => '', 'href'  => ''
                ));
            }, 'medium');
            on('test_priority', function () {
                dashboard_add_item(array(
                    'title' => 'this was added with MEDIUM priority (2)', 'image' => '', 'href'  => ''
                ));
            }, 'medium');
        });

        $allModules = ModuleController::getAllModules();
        $this->assertEquals(1, count($allModules));
    }

    public function testGetModuleByKey() {
        $module = ModuleController::getModuleByKey('unit_test');
        $this->assertTrue($module instanceof Module);
        $module = ModuleController::getModuleByKey('module that does not exist');
        $this->assertFalse($module);
    }

    public function testModuleBecomesActive() {
        $activeModules = ModuleController::getActiveModules();
        $this->assertEquals(0, count($activeModules));
        ModuleController::getModuleByKey('unit_test')->toggleActiveness();
        $activeModules = ModuleController::getActiveModules();
        $this->assertEquals(1, count($activeModules));
    }

    public function testHookedFunctionIsCalledWhenEventIsFired() {
        global $eventFired;
        $this->assertFalse($eventFired);

        ModuleController::emit('arbitrary_event--misspelled');
        $this->assertFalse($eventFired);

        ModuleController::emit('arbitrary_event');
        $this->assertTrue($eventFired);
    }

    public function testDashboardAPI() {
        global $dashboardActions;
        $dashboardActions = array();
        ModuleController::emit('test_dashboard');
        $this->assertEquals(array(
            array(
                'title' => 'item1', 'image' => '', 'href' => ''
            ),
            array(
                'title' => 'item2', 'image' => '', 'href' => ''
            ),
            array(
                'title' => 'item3', 'image' => '', 'href' => ''
            )
        ), $dashboardActions);
    }

    public function testPriority() {
        global $dashboardActions;
        $dashboardActions = array();
        ModuleController::emit('test_priority');
        $this->assertEquals(array(
            array(
                'title' => 'this was added with HIGH priority', 'image' => '', 'href' => ''
            ),
            array(
                'title' => 'this was added with MEDIUM priority (1)', 'image' => '', 'href' => ''
            ),
            array(
                'title' => 'this was added with MEDIUM priority (2)', 'image' => '', 'href' => ''
            ),
            array(
                'title' => 'this was added with LOW priority', 'image' => '', 'href' => ''
            )
        ), $dashboardActions);
    }

    public function testModuleFunctions() {
        $module = ModuleController::registerModule(array(
            'key'         => 'some_key',
            'title'       => 'a title',
            'description' => 'my description'
        ), function () {});
        $this->assertTrue($module instanceof Module);
        $this->assertFalse($module->active() === true);
        $module->toggleActiveness();
        $this->assertTrue($module->active());
        $this->assertEquals('some_key', $module->key());
        $this->assertEquals('a title', $module->title());
        $this->assertEquals('my description', $module->description());
    }
}