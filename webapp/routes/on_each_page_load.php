<?php
/**
 * The code in this file should run on every page load, regardless of which page we're on.
 */

if ($f3->get('GET.mark_notification_as_read')) {
    $notificationID = (int) $f3->get('GET.mark_notification_as_read');
    $notification = new Notification($notificationID);
    $notification->markAsRead(); // @TODO - need to add checks to see if user is logged in and authorised
    header('Location: ' . $notification->getUrl());
}

if (Session::loggedIn()) {
    $f3->set('notifications', Session::getAccount()->getNotifications());
}