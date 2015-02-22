<?php

class RouteSession {

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
                    'href'  => '/register/individual',
                    'image' => '/ui/images/mail.png',
                    'title' => 'Register Agent account'
                ),
                array(
                    'href'  => '/disputes/new',
                    'image' => '/ui/images/mail.png',
                    'title' => 'Create Dispute'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                )
            );
        }
        elseif ($account instanceof MediationCentre) {
            $dashboardActions = array(
                array(
                    'href'  => '/register/individual',
                    'image' => '/ui/images/mail.png',
                    'title' => 'Register Mediator account'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                )
            );
        }
        elseif ($account instanceof Agent) {
            $dashboardActions = array(
                array(
                    'title' => 'Communication'
                ),
                array(
                    'title' => 'Propose Resolution'
                ),
                array(
                    'title' => 'Propose Mediation'
                ),
                array(
                    'title' => 'Renegotiate Dispute Lifespan'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                )
            );
        }
        elseif ($account instanceof Mediator) {
            $dashboardActions = array(
                array(
                    'title' => 'Something'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                ),
                array(
                    'title' => 'Something else'
                )
            );
        }

        $f3->set('dashboardActions', $dashboardActions);
        $f3->set('content','dashboard.html');

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
