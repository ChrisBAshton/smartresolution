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

    $organisationId = AccountDetails::emailToId($org['account_details']['email']);

    if (isset($org['individuals'])) {
        foreach($org['individuals'] as $individual) {
            Register::individual(array(
                'email'           => $individual['account_details']['email'],
                'password'        => $individual['account_details']['password'],
                'organisation_id' => $organisationId,
                'type'            => $individual['details']['type'],
                'forename'        => $individual['details']['forename'],
                'surname'         => $individual['details']['surname']
            ));
        }
    }
}