<?php
require __DIR__ . '/../webapp/lib/autoload.php';

use Symfony\Component\Yaml\Parser;

$yaml = new Parser();
$users = $yaml->parse(file_get_contents(__DIR__ . '/users.yml'));
$users = $users['users'];
$crypt = \Bcrypt::instance();

$db= new \DB\SQL('sqlite:data/test.db');

// truncate
$db->exec('DELETE FROM users');

foreach($users as $user) {
    $db->exec('INSERT INTO users (email, password) VALUES (:email, :password)', array(
        ':email'    => $user['email'],
        ':password' => $crypt->hash($user['password'])
    ));
}

$sql = $db->exec('SELECT * FROM users');
var_dump($sql);