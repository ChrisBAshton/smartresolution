<?php
require 'functions.php';
require 'dispute.php';
require 'notifications.php';
require 'on_each_page_load.php';
require 'register.php';
require 'session.php';

$routes = array(
    // index pages
    'GET  /'                    => 'RouteSession->index',
    'GET  /dashboard'           => 'RouteSession->dashboard',

    // session handling
    'GET  /login'               => 'RouteSession->loginGet',
    'POST /login'               => 'RouteSession->loginPost',
    'GET  /logout'              => 'RouteSession->logout',

    // individual and organisation registration
    'GET  /register'            => 'RouteRegister->organisationGet',
    'POST /register'            => 'RouteRegister->organisationPost',
    'GET  /register/individual' => 'RouteRegister->individualGet',
    'POST /register/individual' => 'RouteRegister->individualPost',

    // disputes
    'GET  /disputes/new'               => 'RouteDispute->newDisputeGet',
    'POST /disputes/new'               => 'RouteDispute->newDisputePost',
    'GET  /disputes'                   => 'RouteDispute->viewDisputes',
    'GET  /disputes/@disputeID'        => 'RouteDispute->viewDispute',
    'GET  /disputes/@disputeID/open'   => 'RouteDispute->openDisputeGet',
    'POST /disputes/@disputeID/open'   => 'RouteDispute->openDisputePost',
    'GET  /disputes/@disputeID/assign' => 'RouteDispute->assignDisputeGet',
    'POST /disputes/@disputeID/assign' => 'RouteDispute->assignDisputePost',
    'GET  /disputes/@disputeID/close'  => 'RouteDispute->closeDisputeGet',
    'POST /disputes/@disputeID/close'  => 'RouteDispute->closeDisputePost',

    // notifications
    'GET /notifications' => 'notificationsList'
);

foreach($routes as $request => $handler) {
    $f3->route($request, $handler);
}