<?php
require __DIR__ . '/../../webapp/autoload.php';

$env = 'test';
if (isset($argv[1])) {
    $env = $argv[1];
}
Database::setEnvironment($env);

use Symfony\Component\Yaml\Parser;
$yaml = new Parser();
$data = $yaml->parse(file_get_contents(__DIR__ . '/fixture_data.yml'));

foreach($data['organisations'] as $org) {
    $organisation = DBL::createOrganisation(array(
        'email'    => $org['account_details']['email'],
        'password' => $org['account_details']['password'],
        'type'     => $org['details']['type'],
        'name'     => $org['details']['name']
    ));

    if (isset($org['details']['description'])) {
        $organisation->setDescription($org['details']['description']);
    }

    if (isset($org['individuals'])) {
        foreach($org['individuals'] as $individual) {
            $account = DBL::createIndividual(array(
                'email'           => $individual['account_details']['email'],
                'password'        => $individual['account_details']['password'],
                'organisation_id' => $organisation->getLoginId(),
                'type'            => $individual['details']['type'],
                'forename'        => $individual['details']['forename'],
                'surname'         => $individual['details']['surname']
            ));
            if (isset($individual['details']['cv'])) {
                $account->setCV($individual['details']['cv']);
            }
        }
    }
}

foreach($data['disputes'] as $dataItem) {
    $dispute = DBL::createDispute(array(
        'title'      => $dataItem['title'],
        'law_firm_a' => AccountDetails::emailToId($dataItem['law_firm_a']),
        'type'       => $dataItem['type']
    ));
    $agentAId = AccountDetails::emailToId($dataItem['agent_a']);
    $dispute->setAgentA($agentAId);

    if (isset($dataItem['law_firm_b'])) {
        $dispute->setLawFirmB(AccountDetails::emailToId($dataItem['law_firm_b']));
    }

    if (isset($dataItem['agent_b'])) {
        $dispute->setAgentB(AccountDetails::emailToId($dataItem['agent_b']));
    }

    if (isset($dataItem['summary_a'])) {
        $dispute->setSummaryForPartyA($dataItem['summary_a']);
    }

    if (isset($dataItem['summary_b'])) {
        $dispute->setSummaryForPartyB($dataItem['summary_b']);
    }

    if (isset($dataItem['lifespan'])) {

        $currentTime = time();

        switch($dataItem['lifespan']) {
            case 'offered':
            case 'declined':
                $validUntil = $currentTime + 3600;
                $startTime  = $currentTime + 7200;
                $endTime    = $currentTime + 12000;
                break;
            case 'accepted':
                $validUntil = $currentTime - 3600;
                $startTime  = $currentTime - 1000;
                $endTime    = $currentTime + 12000;
                break;
            case 'ended':
                $validUntil = $currentTime - 12000;
                $startTime  = $currentTime - 7200;
                $endTime    = $currentTime - 3600;
                break;
        }

        DBL::createLifespan(array(
            'dispute_id'  => $dispute->getDisputeId(),
            'proposer'    => $agentAId,
            'valid_until' => $validUntil,
            'start_time'  => $startTime,
            'end_time'    => $endTime
        ), true);

        $dispute->refresh();

        if ($dataItem['lifespan'] === 'declined') {
            $dispute->getCurrentLifespan()->decline();
        }
        elseif ($dataItem['lifespan'] !== 'offered') {
            $dispute->getCurrentLifespan()->accept();
        }
    }

    if (isset($dataItem['evidence'])) {

        shell_exec('echo "This is an example evidence document." > ' . __DIR__ . '/../../webapp/uploads/tmp.txt');

        if ($dataItem['evidence'] === 'one_item') {
            DBL::createEvidence(array(
                'uploader' => $dispute->getAgentA(),
                'dispute'  => $dispute,
                'filepath' => '/uploads/tmp.txt'
            ));
        }
    }

    if (isset($dataItem['mediation_centre'])) {
        DBL::createMediationCentreOffer(array(
            'dispute'          => $dispute,
            'proposed_by'      => $dispute->getAgentA(),
            'mediation_centre' => AccountDetails::getAccountByEmail($dataItem['mediation_centre'])
        ));

        $dispute->refresh();
        $dispute->getMediationState()->acceptLatestProposal();
    }

    if (isset($dataItem['mediator'])) {
        DBL::createMediatorOffer(array(
            'dispute'     => $dispute,
            'proposed_by' => $dispute->getAgentA(),
            'mediator'    => AccountDetails::getAccountByEmail($dataItem['mediator'])
        ));

        $dispute->refresh();
        $dispute->getMediationState()->acceptLatestProposal();
    }
}
