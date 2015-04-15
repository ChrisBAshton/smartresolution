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
     * @param  array  $array   The array to search in.
     * @param  string $key     The key whose value we want to find.
     * @param  string $default (Optional) - the default value if no value is found.
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
     * @param  string $title The title of the dispute.
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
}
