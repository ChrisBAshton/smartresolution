<?php

class SessionController {

    function dashboard ($f3) {
        if (Session::loggedIn()) {
            $account = Session::getAccount();
            $f3->set('account', $account);
        }
        else {
            header('Location: /logout');
        }

        $dashboardActions = Dashboard::getTopLevelActions($account);

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
        $validCredentials = DBAccount::validCredentials($email, $password);

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
