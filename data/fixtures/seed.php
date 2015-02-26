<?php
require __DIR__ . '/../../webapp/autoload.php';
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

foreach($data['disputes'] as $dataItem) {
    $dispute = Dispute::create(array(
        'title'      => $dataItem['title'],
        'law_firm_a' => AccountDetails::emailToId($dataItem['law_firm_a']),
        'agent_a'    => AccountDetails::emailToId($dataItem['agent_a']),
        'type'       => $dataItem['type']
    ));

    Notification::create(array(
        'recipient_id' => AccountDetails::emailToId($dataItem['agent_a']),
        'message'      => 'A notification should be made when a dispute is created and assigned to an Agent',
        'url'          => $dispute->getUrl()
    ));
}