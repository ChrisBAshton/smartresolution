<?php
/**
 * The code in this file should run on every page load, regardless of which page we're on.
 */

// @TODO - REMOVE THIS LINE. This is a temporary solution - we run our cron script on every page load.
// In production, we definitely want this being called from a regular cronjob instead.
require 'cron.php';

if ($f3->get('GET.mark_notification_as_read')) {
    $notificationID = (int) $f3->get('GET.mark_notification_as_read');
    $notification = new Notification($notificationID);
    $notification->markAsRead(); // @TODO - need to add checks to see if user is logged in and authorised
    header('Location: ' . $notification->getUrl());
}

if (Session::instance()->loggedIn()) {
    $f3->set('notifications', Session::instance()->getAccount()->getNotifications());
}
