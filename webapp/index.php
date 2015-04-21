<?php
require_once __DIR__ . '/autoload.php';

date_default_timezone_set('Europe/London');

$f3 = \Base::instance();
$f3->config('f3_config.ini');

// support for Cucumber features
if ($f3->get('AGENT') === 'Poltergeist--clear') {
    Database::setEnvironment('test');
    Database::clear();
}
else if ($f3->get('AGENT') === 'Poltergeist') {
    Database::setEnvironment('test');
}
else {
    Database::setEnvironment('production');
}

require __DIR__ . '/modules/config.php';
require __DIR__ . '/routes.php';

// In production, this should be called from regular cron job (every 1-10 mins) instead. For now, as a temporary solution, I'm running it on every page load. This is not very efficient!
// @TODO - REMOVE THIS LINE. This is a temporary solution - we run our cron script on every page load.
require 'cron.php';

// @TODO - move to a URLController class?
if ($f3->get('GET.mark_notification_as_read')) {
    $notificationID = (int) $f3->get('GET.mark_notification_as_read');
    $notificationDetails = DBGet::instance()->notification($notificationID);
    $notification = new Notification($notificationDetails);
    $notification->markAsRead(); // @TODO - should probably add checks to see if user is logged in and authorised
    DBUpdate::instance()->notification($notification); // make the 'mark as read' change persistent
    header('Location: ' . $notification->getUrl());
}

$f3->run();