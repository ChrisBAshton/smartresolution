<?php
require 'functions.php';
require 'register.php';
require 'session.php';

$f3->route('GET  /',       'RouteSession->index');
$f3->route('GET  /home',   'RouteSession->home');
$f3->route('GET  /login',  'RouteSession->loginForm');
$f3->route('POST /login',  'RouteSession->loginPost');
$f3->route('GET  /logout', 'RouteSession->logout');

$f3->route('GET  /register',            'RouteRegister->organisationForm');
$f3->route('POST /register',            'RouteRegister->organisationPost');
$f3->route('GET  /register/individual', 'RouteRegister->individualForm');
$f3->route('POST /register/individual', 'RouteRegister->individualPost');
