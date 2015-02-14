<?php
require __DIR__ . '/../../webapp/classes/autoload.php';

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();
$users = $yaml->parse(file_get_contents(__DIR__ . '/users.yml'));
$users = $users['users'];
$crypt = \Bcrypt::instance();

$db= new \DB\SQL('sqlite:data/test.db');

foreach($users as $user) {
    $db->exec('INSERT INTO account_details (email, password) VALUES (:email, :password)', array(
        ':email'    => $user['email'],
        ':password' => $crypt->hash($user['password'])
    ));
}