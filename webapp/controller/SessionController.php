<?php

class SessionController {

    function index ($f3) {
        if (Session::loggedIn()) {
            header('Location: /dashboard');
        }
        else {
            $f3->set('content','index.html');
            echo View::instance()->render('layout.html');
        }
    }

    function dashboard ($f3) {
        if (Session::loggedIn()) {
            $account = Session::getAccount();
            $f3->set('account', $account);
        }
        else {
            header('Location: /logout');
        }

        if ($account instanceof LawFirm) {
            $dashboardActions = array(
                array(
                    'href'  => '/disputes',
                    'image' => '/view/images/disputes.png',
                    'title' => 'View Disputes'
                ),
                array(
                    'href'  => '/disputes/new',
                    'image' => '/view/images/dispute.png',
                    'title' => 'Create Dispute'
                ),
                array(
                    'href'  => '/register/individual',
                    'image' => '/view/images/security.png',
                    'title' => 'Register Agent account'
                )
            );
        }
        elseif ($account instanceof MediationCentre) {
            $dashboardActions = array(
                array(
                    'href'  => '/register/individual',
                    'image' => '/view/images/security.png',
                    'title' => 'Register Mediator account'
                )
            );
        }
        elseif ($account instanceof Agent) {
            $dashboardActions = array(
                array(
                    'href'  => '/disputes',
                    'image' => '/view/images/disputes.png',
                    'title' => 'View Disputes'
                )
            );
        }
        elseif ($account instanceof Mediator) {
            $dashboardActions = array(
                array(
                    'title' => 'Mediators can do nothing yet.'
                )
            );
        }

        $f3->set('dashboardActions', $dashboardActions);
        $f3->set('content','dashboard.html');

        echo View::instance()->render('layout.html');
    }

    function loginGet ($f3) {
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
            header('Location: /dashboard');
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
