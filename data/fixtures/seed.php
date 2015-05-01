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

$create = DBCreate::instance();

foreach($data['administrators'] as $admin) {
    $create->admin(array(
        'email'    => $admin['email'],
        'password' => $admin['password']
    ));
}

foreach($data['organisations'] as $org) {
    $organisation = $create->organisation(array(
        'email'    => $org['account_details']['email'],
        'password' => $org['account_details']['password'],
        'type'     => $org['details']['type'],
        'name'     => $org['details']['name']
    ));

    if (isset($org['details']['description'])) {
        $organisation->setDescription($org['details']['description']);
        DBUpdate::instance()->organisation($organisation);
    }

    if (isset($org['individuals'])) {
        foreach($org['individuals'] as $individual) {
            $account = $create->individual(array(
                'email'           => $individual['account_details']['email'],
                'password'        => $individual['account_details']['password'],
                'organisation_id' => $organisation->getLoginId(),
                'type'            => $individual['details']['type'],
                'forename'        => $individual['details']['forename'],
                'surname'         => $individual['details']['surname']
            ));
            if (isset($individual['details']['cv'])) {
                $account->setCV($individual['details']['cv']);
                DBUpdate::instance()->individual($account);
            }
        }
    }
}

foreach($data['disputes'] as $dataItem) {
    $dispute = $create->dispute(array(
        'title'      => $dataItem['title'],
        'law_firm_a' => DBQuery::instance()->emailToId($dataItem['law_firm_a']),
        'type'       => $dataItem['type']
    ));

    $agentAId = DBQuery::instance()->emailToId($dataItem['agent_a']);
    $dispute->getPartyA()->setAgent($agentAId);
    DBUpdate::instance()->disputeParty($dispute->getPartyA());

    if (isset($dataItem['law_firm_b'])) {
        $dispute->getPartyB()->setLawFirm(DBQuery::instance()->emailToId($dataItem['law_firm_b']));
        DBUpdate::instance()->disputeParty($dispute->getPartyB());
    }

    if (isset($dataItem['agent_b'])) {
        $dispute->getPartyB()->setAgent(DBQuery::instance()->emailToId($dataItem['agent_b']));
        DBUpdate::instance()->disputeParty($dispute->getPartyB());
    }

    if (isset($dataItem['summary_a'])) {
        $dispute->getPartyA()->setSummary($dataItem['summary_a']);
        DBUpdate::instance()->disputeParty($dispute->getPartyA());
    }

    if (isset($dataItem['summary_b'])) {
        $dispute->getPartyB()->setSummary($dataItem['summary_b']);
        DBUpdate::instance()->disputeParty($dispute->getPartyB());
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

        $create->lifespan(array(
            'dispute_id'  => $dispute->getDisputeId(),
            'proposer'    => $agentAId,
            'valid_until' => $validUntil,
            'start_time'  => $startTime,
            'end_time'    => $endTime
        ), true);

        DBUpdate::instance()->dispute($dispute);

        $lifespan = $dispute->getCurrentLifespan();
        if ($dataItem['lifespan'] === 'declined') {
            $lifespan->decline();
        }
        elseif ($dataItem['lifespan'] !== 'offered') {
            $lifespan->accept();
        }
        DBUpdate::instance()->lifespan($lifespan);
    }

    if (isset($dataItem['evidence'])) {

        shell_exec('echo "This is an example evidence document." > ' . __DIR__ . '/../../webapp/uploads/tmp.txt');

        if ($dataItem['evidence'] === 'one_item') {
            $create->evidence(array(
                'uploader_id' => $dispute->getPartyA()->getAgent()->getLoginId(),
                'dispute_id'  => $dispute->getDisputeId(),
                'filepath'    => '/uploads/tmp.txt'
            ));
        }
    }

    if (isset($dataItem['mediation_centre'])) {
        $mediationCentreLogin = DBQuery::instance()->emailToId($dataItem['mediation_centre']);
        $create->mediationCentreOffer(array(
            'dispute_id'  => $dispute->getDisputeId(),
            'proposer_id' => $dispute->getPartyA()->getAgent()->getLoginId(),
            'proposed_id' => DBGet::instance()->account($mediationCentreLogin)->getLoginId()
        ));

        DBUpdate::instance()->dispute($dispute);
        $dispute->getMediationState()->acceptLatestProposal();
    }

    if (isset($dataItem['mediator'])) {
        $mediatorLogin = DBQuery::instance()->emailToId($dataItem['mediator']);
        $create->mediatorOffer(array(
            'dispute_id'  => $dispute->getDisputeId(),
            'proposer_id' => $dispute->getPartyA()->getAgent()->getLoginId(),
            'proposed_id' => DBGet::instance()->account($mediatorLogin)->getLoginId()
        ));

        DBUpdate::instance()->dispute($dispute);
        $dispute->getMediationState()->acceptLatestProposal();
    }
}