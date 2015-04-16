<?php

class RegisterController {

    function organisationGet ($f3) {
        if (Session::instance()->loggedIn()) {
            header('Location: /dashboard');
        }
        $f3->set('content','register_organisation.html');
        echo View::instance()->render('layout.html');
    }

    function organisationPost($f3) {
        $email    = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $orgName  = $f3->get('POST.organisation_name');
        $orgType  = $f3->get('POST.organisation_type');

        if (!$email || !$password || !$orgName || !$orgType) {
            $f3->set('error_message', 'Please fill in all fields.');
        }
        else {
            try {
                DBCreate::instance()->organisation(array(
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

    function individualGet ($f3) {
        mustBeLoggedInAsAn('Organisation');
        $f3->set('content','register_individual.html');
        echo View::instance()->render('layout.html');
    }

    function individualPost ($f3) {
        mustBeLoggedInAsAn('Organisation');

        $email    = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $surname  = $f3->get('POST.surname');
        $forename = $f3->get('POST.forename');

        if (!$email || !$password || !$surname || !$forename) {
            $f3->set('error_message', 'Please fill in all fields.');
        }
        else {
            try {
                $organisation = Session::instance()->getAccount();

                DBCreate::instance()->individual(array(
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
}
