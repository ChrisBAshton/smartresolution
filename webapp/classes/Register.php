<?php
require_once __DIR__ . '/autoload.php';

class Register {

    public static function organisation($orgObject) {
        $type        = Register::getValue($orgObject, 'type');
        $name        = Register::getValue($orgObject, 'name', '');
        $description = Register::getValue($orgObject, 'description', '');

        Database::instance()->begin();
        $login_id = AccountDetails::register($orgObject);
        Database::instance()->exec('INSERT INTO organisations (login_id, type, name, description) VALUES (:login_id, :type, :name, :description)', array(
            ':login_id'    => $login_id,
            ':type'        => $type,
            ':name'        => $name,
            ':description' => $description
        ));
        Database::instance()->commit();
    }

    public static function getValue($array, $key, $default = NULL) {
        if (!isset($default)) {
            if (!isset($array[$key])) {
                throw new Exception ($key . ' is a required index!');
            }
        }
        return isset($array[$key]) ? $array[$key] : $default;
    }
}