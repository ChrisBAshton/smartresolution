<?php
require_once __DIR__ . '/../../webapp/autoload.php';

class NotificationTest extends PHPUnit_Framework_TestCase
{
    private function createNotification() {
        return new Notification(array(
            'notification_id' => 1,
            'recipient_id'    => 1,
            'read'            => false,
            'message'         => 'Test message',
            'url'             => 'http://example.com/disputes/view/147'
        ));
    }

    public function testNotificationCreatedCorrectly() {
        $notification = $this->createNotification();
        $this->assertTrue($notification instanceof Notification);
        $this->assertEquals(1, $notification->getNotificationId());
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
