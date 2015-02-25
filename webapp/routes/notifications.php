<?php

function notificationsList ($f3) {
    $f3->set('content','notifications.html');
    echo View::instance()->render('layout.html');
}