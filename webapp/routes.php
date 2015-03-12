<?php

$routes = array(
    // index pages
    'GET  /'                    => 'SessionController->index',
    'GET  /dashboard'           => 'SessionController->dashboard',

    // session handling
    'GET  /login'               => 'SessionController->loginGet',
    'POST /login'               => 'SessionController->loginPost',
    'GET  /logout'              => 'SessionController->logout',

    // individual and organisation registration
    'GET  /register'            => 'RegisterController->organisationGet',
    'POST /register'            => 'RegisterController->organisationPost',
    'GET  /register/individual' => 'RegisterController->individualGet',
    'POST /register/individual' => 'RegisterController->individualPost',

    // disputes
    'GET  /disputes/new'                  => 'DisputeController->newDisputeGet',
    'POST /disputes/new'                  => 'DisputeController->newDisputePost',
    'GET  /disputes'                      => 'DisputeController->viewDisputes',
    'GET  /disputes/@disputeID'           => 'DisputeController->viewDispute',
    'GET  /disputes/@disputeID/open'      => 'DisputeController->openDisputeGet',
    'POST /disputes/@disputeID/open'      => 'DisputeController->openDisputePost',
    'GET  /disputes/@disputeID/assign'    => 'DisputeController->assignDisputeGet',
    'POST /disputes/@disputeID/assign'    => 'DisputeController->assignDisputePost',
    'GET  /disputes/@disputeID/close'     => 'DisputeController->closeDisputeGet',
    'POST /disputes/@disputeID/close'     => 'DisputeController->closeDisputePost',
    'GET  /disputes/@disputeID/summary'   => 'SummaryController->view',
    'POST /disputes/@disputeID/summary'   => 'SummaryController->edit',
    'GET  /disputes/@disputeID/evidence'  => 'EvidenceController->view',
    'GET  /disputes/@disputeID/mediation' => 'MediationController->view',

    // messaging
    'GET  /disputes/@disputeID/chat'    => 'MessageController->view',
    'POST /disputes/@disputeID/chat'    => 'MessageController->newMessage',

    // lifespans
    'GET  /disputes/@disputeID/lifespan'          => 'LifespanController->view',
    'GET|POST /disputes/@disputeID/lifespan/new'  => 'LifespanController->newLifespan',
    'POST /disputes/@disputeID/lifespan/respond'  => 'LifespanController->acceptOrDecline',

    // notifications
    'GET /notifications' => 'notificationsList'
);

foreach($routes as $request => $handler) {
    $f3->route($request, $handler);
}
