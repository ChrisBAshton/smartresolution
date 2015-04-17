<?php

class FunctionParser {

    function __construct($functionToCall, $parameters = array()) {
        // if $functionToCall is an anonymous function or a string representing a global function
        if (is_callable($functionToCall)) {
            call_user_func_array($functionToCall, $parameters);
        }
        else {
            // $functionToCall is the name of a class function (e.g. 'Foo->bar') and needs to be parsed
            $this->tryToCallClassFunction($functionToCall, $parameters);
        }
    }

    private function tryToCallClassFunction($functionToCall, $parameters) {
        $classFunction = strpos($functionToCall, '->') !== false;
        if ($classFunction) {
            $parts = explode('->', $functionToCall);
            $class = $parts[0];
            $method = $parts[1];
            $classInstance = new $class();
            call_user_func_array(array($classInstance, $method), $parameters);
        }
        else {
            throw new Exception('Invalid function: ' . $functionToCall);
        }
    }
}