<?php
require_once __DIR__ . '/classes/autoload.php';

$f3 = \Base::instance();
$f3->config('config.ini');
$f3->set('DEBUG',1);

require __DIR__ . '/routes.php';

$f3->run();