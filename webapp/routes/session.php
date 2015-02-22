<?php

class RouteSession {

    function index ($f3) {
        if (Session::loggedIn()) {
            header('Location: /home');
        }
        else {
            $f3->set('content','index.html');
            echo View::instance()->render('layout.html');
        }
    }

    function home ($f3) {
        if (Session::loggedIn()) {
            $f3->set('account', Session::getAccount());
        }
        else {
            header('Location: /logout');
        }

        if (Session::getAccount() instanceof Organisation) {
            $f3->set('content','home_organisation.html');
        }
        else {
            $f3->set('content','home_individual.html');
        }

        echo View::instance()->render('layout.html');
    }

    function loginForm ($f3) {
        $f3->set('user_email', Session::lastKnownEmail());
        $f3->set('content','login.html');
        echo View::instance()->render('layout.html');
    }

    function loginPost ($f3) {
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

    function logout ($f3) {
        Session::clear();
        header('Location: /');
    }
}
