<?php

/**
 * Routes all login/logout/session-related HTTP requests with the appropriate actions.
 */
class SessionController {

    /**
     * Loads the account's logged in dashboard. This dashboard will have different actions available on it according to the account type.
     * @param  F3 $f3         The base F3 object.
     */
    function dashboard ($f3) {
        if (Session::instance()->loggedIn()) {
            $account = Session::instance()->getAccount();
            $f3->set('account', $account);
        }
        else {
            header('Location: /logout');
        }

        $dashboardActions = Dashboard::instance()->getTopLevelActions($account);

        $f3->set('dashboardActions', $dashboardActions);
        $f3->set('content','dashboard.html');

        echo View::instance()->render('layout.html');
    }

    /**
     * Loads the login screen.
     * @param  F3 $f3         The base F3 object.
     */
    function loginGet ($f3) {
        $f3->set('user_email', Session::instance()->lastKnownEmail());
        $f3->set('content','login.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * POST method; sends the login data and tries to instantiate a session. If the account credentials are incorrect or if the account has not been verified yet, the login attempt fails and an error message is displayed.
     * @param  F3 $f3         The base F3 object.
     */
    function loginPost ($f3) {
        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $validCredentials = DBQuery::instance()->validCredentials($email, $password);

        if ($validCredentials) {
            $loginID = DBQuery::instance()->emailToId($email);
            $account = DBGet::instance()->account($loginID);
            if ($account->isVerified()) {
                Session::instance()->create($email, $password);
                header('Location: /dashboard');
            }
            else {
                $f3->set('error_message', 'Your account still needs to be verified before you can log in.');
            }
        }
        else {
            $f3->set('error_message', 'Invalid login details.');
        }

        $f3->set('user_email', $email);
        $f3->set('content','login.html');
        echo View::instance()->render('layout.html');
    }

    /**
     * Clears the user's login session and redirects to homepage.
     */
    function logout () {
        Session::instance()->clear();
        header('Location: /');
    }
}
