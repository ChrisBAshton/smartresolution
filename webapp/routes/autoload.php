<?php
require 'functions.php';
require 'register.php';
require 'session.php';

$routes = array(
    // index pages
    'GET  /'                    => 'RouteSession->index',
    'GET  /home'                => 'RouteSession->home',

    // session handling
    'GET  /login'               => 'RouteSession->loginForm',
    'POST /login'               => 'RouteSession->loginPost',
    'GET  /logout'              => 'RouteSession->logout',

    // individual and organisation registration
    'GET  /register'            => 'RouteRegister->organisationForm',
    'POST /register'            => 'RouteRegister->organisationPost',
    'GET  /register/individual' => 'RouteRegister->individualForm',
    'POST /register/individual' => 'RouteRegister->individualPost'
);

foreach($routes as $request => $handler) {
    $f3->route($request, $handler);
}