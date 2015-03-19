<?php

class Utils {

    /**
     * Gets the value of the given key from the given array, defaulting to the given default value if no value exists. If no default is provided and no value exists, an exception is raised.
     *
     * Example:
     *     $arr = array('foo' => 'bar');
     *     $val = getValue($arr, 'foo');        // $val === 'bar'
     *     $val = getValue($arr, 'abc', 'def'); // $val === 'def'
     *     $val = getValue($arr, 'abc');        // Exception raised
     *
     * @param  Array  $array   The array to search in.
     * @param  String $key     The key whose value we want to find.
     * @param  String $default (Optional) - the default value if no value is found.
     * @return Object          Returns the found value, the default value, or raises an exception.
     */
    public static function getValue($array, $key, $default = NULL) {
        if (!isset($default)) {
            if (!isset($array[$key])) {
                throw new Exception ($key . ' is a required index!');
            }
        }
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Semi-temporary function - used for DisputeStateTest.php. Maybe rethink the use of this function later down the line.
     * This function should NOT be called from within the application itself!
     *
     * @param  String $title The title of the dispute.
     * @return Dispute
     */
    public static function getDisputeByTitle($title) {
        $dispute = Database::instance()->exec(
            'SELECT * FROM disputes WHERE title = :title ORDER BY dispute_id DESC LIMIT 1',
            array('title' => $title)
        );
        if (count($dispute) !== 1) {
            throw new Exception("Dispute not found!!!");
        }
        else {
            return new Dispute((int) $dispute[0]['dispute_id']);
        }
    }

    public static function getOrganisations($params) {
        $type   = Utils::getValue($params, 'type');
        $class  = $type === 'law_firm' ? 'LawFirm' : 'MediationCentre';
        $except = Utils::getValue($params, 'except', false);

        $organisations = array();

        if ($except) {
            $orgDetails = Database::instance()->exec(
                'SELECT * FROM organisations INNER JOIN account_details ON organisations.login_id = account_details.login_id  WHERE type = :type AND organisations.login_id != :except ORDER BY name DESC',
                array(
                    ':type'   => $type,
                    ':except' => $except
                )
            );
        }
        else {
            $orgDetails = Database::instance()->exec(
                'SELECT * FROM organisations INNER JOIN account_details ON organisations.login_id = account_details.login_id  WHERE type = :type ORDER BY name DESC',
                array(
                    ':type' => $type
                )
            );
        }

        foreach($orgDetails as $details) {
            $organisations[] = new $class($details);
        }

        return $organisations;
    }
}
