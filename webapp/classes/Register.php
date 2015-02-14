<?php
require_once __DIR__ . '/autoload.php';

class RegisterOrganisation {

    function __construct($orgObject) {
        $db = Database::instance();
        $db->begin();
        $login_id = AccountDetails::register($orgObject, $db);
        $db->exec('INSERT INTO organisations (login_id, type, name, description) VALUES (:login_id, :type, :name, :description)', array(
            ':login_id'    => $login_id,
            ':type'        => $orgObject['type'],
            ':name'        => $orgObject['name'],
            ':description' => $orgObject['description']
        ));
        $db->commit();
    }
}