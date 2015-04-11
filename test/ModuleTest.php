<?php
require_once __DIR__ . '/../webapp/autoload.php';

$eventFired = false;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
        Database::setEnvironment('test');
        Database::clear();

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

            declare_table('my_test_table', array(
                'a_text_field' => 'TEXT NOT NULL',
                'an_int_field' => 'INTEGER DEFAULT 0'
            ));

            on('test_database', function () {
                createRow('my_test_table', array(
                    'a_text_field' => 'This is a test value',
                    'an_int_field' => 1337
                ));
            });
        });
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

    // @TODO
    public function testDatabaseInsertAndSelect() {
        //ModuleController::emit('test_database');
    }
}