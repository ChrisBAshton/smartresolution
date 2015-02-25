<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class NotificationTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        Database::setEnvironment('test');
        Database::clear();
    }

    private function createNotification() {
        return Notification::create(array(
            'recipient_id' => 1,
            'message'      => 'Test message',
            'url'          => 'http://example.com/disputes/view/147'
        ));
    }

    public function testNotificationCreatedCorrectly() {
        $notification = $this->createNotification();
        $this->assertTrue($notification instanceof Notification);
        $this->assertEquals('Test message', $notification->getMessage());
        $this->assertEquals('http://example.com/disputes/view/147', $notification->getUrl());
    }

    public function testNotificationBehavesCorrectly() {
        $notification = $this->createNotification();
        $this->assertEquals(false, $notification->hasBeenRead());
        $notification->markAsRead();
        $this->assertEquals(true, $notification->hasBeenRead());
    }
}