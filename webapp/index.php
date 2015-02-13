<?php
require_once __DIR__ . '/classes/autoload.php';

// globals
$f3 = \Base::instance();
// Load configuration
$f3->set('DEBUG',1);
$f3->config('config.ini');

require __DIR__ . '/routes.php';

$f3->run();