<?php
require __DIR__ . '/../../webapp/classes/autoload.php';

use Symfony\Component\Yaml\Parser;
$yaml = new Parser();
$data = $yaml->parse(file_get_contents(__DIR__ . '/fixture_data.yml'));

Database::setEnvironment('test');
$db = Database::instance();

$crypt = \Bcrypt::instance();

// @TODO - remove the duplication
foreach($data['organisations'] as $org) {
    $db->exec('INSERT INTO account_details (email, password) VALUES (:email, :password)', array(
        ':email'    => $org['account_details']['email'],
        ':password' => $crypt->hash($org['account_details']['password'])
    ));
    $login_id = AccountDetails::emailToId($org['account_details']['email']);
    $db->exec('INSERT INTO organisations (organisation_id, login_id, type, name) VALUES (NULL, :login_id, :type, :name)', array(
        ':login_id' => $login_id,
        ':type'     => $org['details']['type'],
        ':name'     => $org['details']['name']
    ));
}

foreach($data['individuals'] as $org) {
    $db->exec('INSERT INTO account_details (email, password) VALUES (:email, :password)', array(
        ':email'    => $org['account_details']['email'],
        ':password' => $crypt->hash($org['account_details']['password'])
    ));
    $login_id = AccountDetails::emailToId($org['account_details']['email']);
    $db->exec('INSERT INTO individuals (individual_id, login_id, type, surname, forename) VALUES (NULL, :login_id, :type, :surname, :forename)', array(
        ':login_id' => $login_id,
        ':type'     => $org['details']['type'],
        ':surname'  => $org['details']['surname'],
        ':forename' => $org['details']['forename']
    ));
}