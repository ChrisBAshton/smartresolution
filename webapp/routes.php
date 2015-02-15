<?php

$f3->route('GET /',
    function($f3) {
        if (Session::loggedIn()) {
            header('Location: /home');
        }
        else {
            $f3->set('content','index.html');
            echo View::instance()->render('layout.html');
        }
    }
);

$f3->route('GET /home',
    function($f3) {
        if (Session::loggedIn()) {
            $f3->set('account', Session::getAccount());
        }
        else {
            header('Location: /logout');
        }

        $f3->set('content','home.html');
        echo View::instance()->render('layout.html');
    }
);

$f3->route('GET /register',
    function($f3) {
        $f3->set('content','register.html');
        echo View::instance()->render('layout.html');
    }
);

$f3->route('POST /register',
    function($f3) {

        $email    = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $orgName  = $f3->get('POST.organisation_name');
        $orgType  = $f3->get('POST.organisation_type');

        if (!$email || !$password || !$orgName || !$orgType) {
            $f3->set('error_message', 'Please fill in all fields.');
            $f3->set('content','register.html');
            echo View::instance()->render('layout.html');
        }
        else {
            
        }
        // try {
        //     $email    = $f3->get('POST.email');
        //     $password = $f3->get('POST.password');
        //     $user = new Agent($email, $password, true);
        // }
        // catch (Exception $e) {
        //     $f3->set('error_message', $e->getMessage());
        //     $f3->set('user_email', $f3->get('POST.email'));
        //     $f3->set('content','register.html');
        //     echo View::instance()->render('layout.html');
        // }
    }
);

$f3->route('GET /login',
    function($f3) {
        $f3->set('user_email', Session::lastKnownEmail());
        $f3->set('content','login.html');
        echo View::instance()->render('layout.html');
    }
);

$f3->route('POST /login',
    function($f3) {
        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $validCredentials = AccountDetails::validCredentials($email, $password);
        
        if ($validCredentials) {
            Session::create($email, $password);
            header('Location: /home');
        }
        else {
            $f3->set('error_message', 'Invalid login details.');
            $f3->set('user_email', $email);
            $f3->set('content','login.html');
            echo View::instance()->render('layout.html');
        }
    }
);

$f3->route('GET /logout',
    function ($f3) {
        Session::clear();
        header('Location: /');
    }
);