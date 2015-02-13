<?php
$session = new Session();

$f3->route('GET /',
    function($f3) {
        global $session;
        if ($session->loggedIn()) {
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
        try {
            $email    = $f3->get('POST.email');
            $password = $f3->get('POST.password');
            $user = new Agent($email, $password, true);
        }
        catch (Exception $e) {
            $f3->set('error_message', $e->getMessage());
            $f3->set('user_email', $f3->get('POST.email'));
            $f3->set('content','register.html');
            echo View::instance()->render('layout.html');
        }
    }
);

$f3->route('GET /login',
    function($f3) {
        global $session;
        $f3->set('user_email', $session->lastKnownEmail());
        $f3->set('content','login.html');
        echo View::instance()->render('layout.html');
    }
);

$f3->route('POST /login',
    function($f3) {
        global $session;

        try {
            $email = $f3->get('POST.email');
            $password = $f3->get('POST.password');
            $user = new Agent($email, $password);
            $session->create($email, $password);
            header('Location: /home');
        }
        catch (Exception $e) {
            $f3->set('error_message', $e->getMessage());
            $f3->set('user_email', $f3->get('POST.email'));
            $f3->set('content','login.html');
            echo View::instance()->render('layout.html');
        }
    }
);

$f3->route('GET /logout',
    function ($f3) {
        global $session;
        $session->clear();
        header('Location: /');
    }
);