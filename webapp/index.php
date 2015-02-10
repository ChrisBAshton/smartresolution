<?php

// Kickstart the framework
$f3 = require('lib/base.php');

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION<7.9) {
	trigger_error('PCRE version is out of date');
}

// Load configuration
$f3->config('config.ini');

$f3->route('GET /',
	function($f3) {
		$f3->set('content','welcome.htm');
		echo View::instance()->render('layout.htm');
	}
);

$f3->route('GET|POST /login',
	function($f3) {
		if ($f3->get('POST.email')) {
			$crypt = \Bcrypt::instance();
			$db = new \DB\Jig('data/',\DB\Jig::FORMAT_Serialized);

			// @TODO - get user by username. For now, just username 'cba1' and password 'test'
			$currentUser = $db->read('users')[0];

			if ($crypt->verify($_POST['password'], $currentUser['password'])) {
				$f3->set('user_logged_in', true);
			}
			else {
				$f3->set('user_logged_in', false);
			}
		}

		$f3->set('content','test.htm');
		echo View::instance()->render('layout.htm');
	}
);

$f3->run();