<?php

class FunctionParser {

    function __construct($functionToCall, $parameters) {
        // if $functionToCall is an anonymous function
        if (is_callable($functionToCall)) {
            $functionToCall($parameters);
        }
        // if $functionToCall is the name (string) of a global function
        else if (function_exists($functionToCall)) {
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