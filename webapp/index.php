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

require __DIR__ . '/../modules/config.php';
require __DIR__ . '/on_each_page_load.php';
require __DIR__ . '/routes.php';

$f3->run();