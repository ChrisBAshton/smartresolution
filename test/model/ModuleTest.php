<?php
define('WEBAPP_DIR', __DIR__ . '/../../webapp');

require_once WEBAPP_DIR . '/autoload.php';

$eventFired = false;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    public static function tearDownAfterClass()
    {
        shell_exec('rm ' . WEBAPP_DIR . '/modules/config.json');
    }

    public function testDeclareModule()
    {

        $allModules = ModuleController::instance()->getAllModules();
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

        $allModules = ModuleController::instance()->getAllModules();
        $this->assertEquals(1, count($allModules));
    }

    public function testGetModuleByKey()
    {
        $module = ModuleController::instance()->getModuleByKey('unit_test');
        $this->assertTrue($module instanceof Module);
        $module = ModuleController::instance()->getModuleByKey('module that does not exist');
        $this->assertFalse($module);
    }

    public function testModuleBecomesActive()
    {
        $activeModules = ModuleController::instance()->getActiveModules();
        $this->assertEquals(0, count($activeModules));
        ModuleController::instance()->getModuleByKey('unit_test')->toggleActiveness();
        $activeModules = ModuleController::instance()->getActiveModules();
        $this->assertEquals(1, count($activeModules));
    }

    public function testHookedFunctionIsCalledWhenEventIsFired()
    {
        global $eventFired;
        $this->assertFalse($eventFired);

        ModuleController::instance()->emit('arbitrary_event--misspelled');
        $this->assertFalse($eventFired);

        ModuleController::instance()->emit('arbitrary_event');
        $this->assertTrue($eventFired);
    }

    public function testDashboardAPI()
    {
        global $dashboardActions;
        $dashboardActions = array();
        ModuleController::instance()->emit('test_dashboard');
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

    public function testPriority()
    {
        global $dashboardActions;
        $dashboardActions = array();
        ModuleController::instance()->emit('test_priority');
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

    public function testModuleFunctions()
    {
        $module = $this->createSimpleModule();
        $this->assertTrue($module instanceof Module);
        $this->assertFalse($module->active());
        $module->toggleActiveness();
        $this->assertTrue($module->active());
        $this->assertEquals('some_key', $module->key());
        $this->assertEquals('a title', $module->title());
        $this->assertEquals('my description', $module->description());
    }

    public function testModuleNOTCalledWhenInactive()
    {
        global $eventFired;
        $eventFired = false;

        $module = $this->createSimpleModule(function () {
            on('inactive_module_event', function () {
                global $eventFired;
                $eventFired = true;
            });
        });

        // inactive module should NOT have its subscribed function called
        $this->assertFalse($module->active());
        $this->assertFalse($eventFired);
        ModuleController::instance()->emit('inactive_module_event');
        $this->assertFalse($eventFired); // should still be false

        // sanity check - when we activate the module, the event SHOULD trigger the subscribed function
        $module->toggleActiveness();
        $this->assertTrue($module->active());
        ModuleController::instance()->emit('inactive_module_event');
        $this->assertTrue($eventFired);
    }

    public function testModuleNotifications()
    {
        $module = $this->createSimpleModule(function () {
            on('send_notification', function () {
                notify(1, 'some message', '/some-url');
            });
        });
        $module->toggleActiveness();

        ModuleController::instance()->emit('send_notification');

        $notification = Database::instance()->exec(
            'SELECT * FROM notifications WHERE recipient_id = :recipientID AND message = :message AND url = :url',
            array(
                ':recipientID' => 1,
                ':message'     => 'some message',
                ':url'         => '/some-url'
            )
        );

        $this->assertEquals(1, count($notification));
    }

    private function createSimpleModule($callbackFunction = false)
    {
        $callbackFunction = $callbackFunction ? $callbackFunction : function() {};
        return ModuleController::instance()->registerModule(array(
            'key'         => 'some_key',
            'title'       => 'a title',
            'description' => 'my description'
        ), $callbackFunction);
    }
}