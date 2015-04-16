<?php

class Utils extends Prefab {

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
    public function getValue($array, $key, $default = NULL) {
        if (!isset($default)) {
            if (!isset($array[$key])) {
                throw new Exception ($key . ' is a required index!');
            }
        }
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Extracts parameters from an array, ready for creating database queries etc.
     * Strips out parameters that are not required, and allows you to specify which parameters
     * are REQUIRED and which are optional.
     *
     * Example usage:
     *
     *      $inputs = array(
     *          'foo' => 'hello',
     *          'bar' => 123,
     *          'obj' => $object
     *      );
     *
     *      $params = Utils::instance()->requiredParams(array(
     *          'foo' => true, // if 'foo' is missing, raises an exception
     *          'bar' => false // if 'bar' is missing, it simply isn't included
     *      ), $inputs);
     *
     *      // OUTPUT:
     *      array(
     *          'foo' => 'hello',
     *          'bar' => 123
     *      );
     *
     * @param  array $requiredParams An array of the required parameters in the form array('key_name' => boolean), where the boolean dictates whether the key is required or not.
     * @param  array $array          The array to extract the parameters from.
     * @return array                 The array of extracted parameters.
     */
    public function requiredParams($requiredParams, $array) {
        $filteredParams = array();
        foreach($requiredParams as $fieldName => $required) {
            if (!isset($array[$fieldName])) {
                if ($required) {
                    throw new Exception('Missing required field: ' . $fieldName);
                }
            }
            else {
                $filteredParams[$fieldName] = $array[$fieldName];
            }
        }
        return $filteredParams;
    }
}
