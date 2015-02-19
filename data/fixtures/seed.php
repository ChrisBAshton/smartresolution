<?php
require __DIR__ . '/../../webapp/classes/autoload.php';
Database::setEnvironment('test');

use Symfony\Component\Yaml\Parser;
$yaml = new Parser();
$data = $yaml->parse(file_get_contents(__DIR__ . '/fixture_data.yml'));

foreach($data['organisations'] as $org) {
    Register::organisation(array(
        'email'    => $org['account_details']['email'],
        'password' => $org['account_details']['password'],
        'type'     => $org['details']['type'],
        'name'     => $org['details']['name']
    ));
}

foreach($data['individuals'] as $single) {
    Register::individual(array(
        'email'    => $single['account_details']['email'],
        'password' => $single['account_details']['password'],
        'type'     => $single['details']['type'],
        'forename' => $single['details']['forename'],
        'surname'  => $single['details']['surname']
    ));
}