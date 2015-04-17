<?php
/**
 * The code in this file should run on every page load, regardless of which page we're on.
 */

// In production, this should be called from regular cron job (every 1-10 mins) instead. For now, as a temporary solution, I'm running it on every page load. This is not very efficient!
// @TODO - REMOVE THIS LINE. This is a temporary solution - we run our cron script on every page load.
require 'cron.php';

if ($f3->get('GET.mark_notification_as_read')) {
    $notificationID = (int) $f3->get('GET.mark_notification_as_read');
    $notification = new Notification($notificationID);
    $notification->markAsRead(); // @TODO - should probably add checks to see if user is logged in and authorised
    header('Location: ' . $notification->getUrl());
}

if (Session::instance()->loggedIn()) {
    $f3->set('notifications', Session::instance()->getAccount()->getNotifications());
}
