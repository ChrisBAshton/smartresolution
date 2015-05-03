<?php

/**
 * Parses a function name and calls the function. Copes with anonymous, global and public class functions.
 * Used in conjunction with the publish-subscribe module mechanisms.
 */
class FunctionParser {

    /**
     * FunctionParser constructor: parses the function and executes it immediately.
     * @param string|function $functionToCall Anonymous/global/named class function.
     * @param array           $parameters     (Optional) Parameters to pass to the function. These parameters are extracted from the array, e.g. array('foo', 'bar') => myFunction($foo, $bar) rather than myFunction(array($foo, $bar))
     */
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

    /**
     * The passed function was not an anonymous function nor a global function, so is probably a named class function, e.g. MyClass->myFunction.
     * We need to parse the string to deduce which class to instantiate, then instantiate the class and call its function.
     * @param  string $functionToCall Named class function.
     * @param  array  $parameters     (Optional) Parameters to pass to the function. These parameters are extracted from the array, e.g. array('foo', 'bar') => myFunction($foo, $bar) rather than myFunction(array($foo, $bar))
     */
    private function tryToCallClassFunction($functionToCall, $parameters) {
        $this->checkIfFunctionToCallIsValid($functionToCall);
        $className     = $this->extractClassName($functionToCall);
        $methodName    = $this->extractMethodName($functionToCall);
        $classInstance = new $className();
        call_user_func_array(array($classInstance, $methodName), $parameters);
    }

    /**
     * Attempts to validate that the class method string at least looks like a class method string, even if the class method itself might not exist. If it doesn't look like a class function, an exception is raised.
     * @param  string $functionToCall Class method, e.g. 'MyClass->myFunction'
     */
    private function checkIfFunctionToCallIsValid($functionToCall) {
        $classFunction = strpos($functionToCall, '->') !== false;
        if (!$classFunction) {
            Utils::instance()->throwException('Invalid function: ' . $functionToCall);
        }
    }

    /**
     * Extracts the class name from the class/method string. 'MyClass->myFunction' => 'MyClass'
     * @param  string $functionToCall Class/method string.
     * @return string                 Class name.
     */
    private function extractClassName($functionToCall) {
        $parts = explode('->', $functionToCall);
        return $parts[0];
    }

    /**
     * Extracts the method name from the class/method string. 'MyClass->myFunction' => 'myFunction'
     * @param  string $functionToCall Class/method string.
     * @return string                 Method name.
     */
    private function extractMethodName($functionToCall) {
        $parts = explode('->', $functionToCall);
        return $parts[1];
    }
}