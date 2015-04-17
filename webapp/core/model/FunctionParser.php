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
        $this->checkIfFunctionToCallIsValid($functionToCall);
        $className     = $this->extractClassName($functionToCall);
        $methodName    = $this->extractMethodName($functionToCall);
        $classInstance = new $className();
        call_user_func_array(array($classInstance, $methodName), $parameters);
    }

    private function checkIfFunctionToCallIsValid($functionToCall) {
        $classFunction = strpos($functionToCall, '->') !== false;
        if (!$classFunction) {
            throw new Exception('Invalid function: ' . $functionToCall);
        }
    }

    private function extractClassName($functionToCall) {
        $parts = explode('->', $functionToCall);
        return $parts[0];
    }

    private function extractMethodName($functionToCall) {
        $parts = explode('->', $functionToCall);
        return $parts[1];
    }
}