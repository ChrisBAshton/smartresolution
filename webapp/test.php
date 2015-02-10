<?php

$f3 = require('lib/base.php');

$crypt = \Bcrypt::instance();
$db = new \DB\Jig('data/',\DB\Jig::FORMAT_Serialized);

// @TODO move test data to YAML file
$db->write('users',array(
  array('user_id' => 1, 'email'=>'cba1','password'=> $crypt->hash('test')),
  array('user_id' => 2, 'email'=>'cba2','password'=> $crypt->hash('lol'))
));
$db->write('users_data',array(
  array('user_id' => 1, 'name' => 'Chris Ashton'),
  array('user_id' => 2, 'name' => 'Someone else')
));

$login = array(
    'email'    => 'cba1',
    'password' => 'test'
);

$currentUser = $db->read('users')[0];

if ($crypt->verify($login['password'], $currentUser['password'])) {
    echo "Successful login as " . $currentUser['email'];
}
else {
    echo "meh";
}