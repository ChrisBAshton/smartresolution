<?php
require_once __DIR__ . '/classes/autoload.php';

$f3 = \Base::instance();
$f3->config('config.ini');
$f3->set('DEBUG',1);

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

require __DIR__ . '/routes.php';

$f3->run();