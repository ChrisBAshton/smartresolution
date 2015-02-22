<?php
require 'functions.php';
require 'dispute.php';
require 'register.php';
require 'session.php';

$routes = array(
    // index pages
    'GET  /'                    => 'RouteSession->index',
    'GET  /dashboard'           => 'RouteSession->dashboard',

    // session handling
    'GET  /login'               => 'RouteSession->loginForm',
    'POST /login'               => 'RouteSession->loginPost',
    'GET  /logout'              => 'RouteSession->logout',

    // individual and organisation registration
    'GET  /register'            => 'RouteRegister->organisationForm',
    'POST /register'            => 'RouteRegister->organisationPost',
    'GET  /register/individual' => 'RouteRegister->individualForm',
    'POST /register/individual' => 'RouteRegister->individualPost',

    // disputes
    'GET  /disputes/new'        => 'RouteDispute->newDisputeForm',
    'POST /disputes/new'        => 'RouteDispute->newDisputePost'
);

foreach($routes as $request => $handler) {
    $f3->route($request, $handler);
}