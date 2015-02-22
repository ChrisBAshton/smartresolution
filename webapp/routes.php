<?php

function mustBeLoggedIn() {
    global $f3;
    if (Session::loggedIn()) {
        $f3->set('account', Session::getAccount());
    }
    else {
        $f3->set('error_message', 'You do not have permission to see this page. Please log in first.');
        $f3->set('content','error.html');
        echo View::instance()->render('layout.html');
        die();
    }
}

function mustBeLoggedInAsAnOrganisation() {
    global $f3;
    mustBeLoggedIn();
    if ( ! (Session::getAccount() instanceof Organisation) ) {
        $f3->set('error_message', 'You do not have permission to see this page. You must be logged into an Organisation account.');
        $f3->set('content','error.html');
        echo View::instance()->render('layout.html');
        die();
    }
}

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

        if (Session::getAccount() instanceof Organisation) {
            $f3->set('content','home_organisation.html');
        }
        else {
            $f3->set('content','home_individual.html');
        }

        echo View::instance()->render('layout.html');
    }
);

$f3->route('GET /register',
    function($f3) {
        $f3->set('content','register_organisation.html');
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
        }
        else {
            try {
                Register::organisation(array(
                    'email'       => $email,
                    'password'    => $password,
                    'name'        => $orgName,
                    'type'        => $orgType
                ));

                $f3->set('success_message', 'You have successfully registered an account.');
            } catch(Exception $e) {
                $f3->set('error_message', $e->getMessage());
            }
        }

        $f3->set('content','register_organisation.html');
        echo View::instance()->render('layout.html');
    }
);

$f3->route('GET /register/individual',
    function($f3) {
        mustBeLoggedInAsAnOrganisation();
        $f3->set('content','register_individual.html');
        echo View::instance()->render('layout.html');
    }
);

$f3->route('POST /register/individual',
    function($f3) {

        mustBeLoggedInAsAnOrganisation();

        $email    = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $surname  = $f3->get('POST.surname');
        $forename = $f3->get('POST.forename');

        if (!$email || !$password || !$surname || !$forename) {
            $f3->set('error_message', 'Please fill in all fields.');
        }
        else {
            try {
                $organisation = Session::getAccount();

                Register::individual(array(
                    'email'           => $email,
                    'password'        => $password,
                    'organisation_id' => $organisation->getLoginId(),
                    'type'            => $organisation instanceof LawFirm ? 'agent' : 'mediator',
                    'surname'         => $surname,
                    'forename'        => $forename
                ));

                $f3->set('success_message', 'You have successfully registered an account.');
            } catch(Exception $e) {
                $f3->set('error_message', $e->getMessage());
            }
        }

        $f3->set('content','register_individual.html');
        echo View::instance()->render('layout.html');
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