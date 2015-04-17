<?php
require_once __DIR__ . '/../../webapp/autoload.php';

// global
$functionCalled = false;

class MyFirstTestClass {
    function functionToCall() {
        global $functionCalled;
        $functionCalled = true;
    }
    function functionToCallWithParams($arg1, $arg2) {
        global $functionCalled;
        $functionCalled = $arg1 . $arg2;
    }
}

class FunctionParserTest extends PHPUnit_Framework_TestCase
{
    public function testAnonymousFunction() {
        global $functionCalled;
        $functionCalled = false;

        new FunctionParser(function () {
            global $functionCalled;
            $functionCalled = true;
        });

        $this->assertTrue($functionCalled);
    }

    public function testGlobalFunction() {
        global $functionCalled;
        $functionCalled = false;

        function global_function_to_call() {
            global $functionCalled;
            $functionCalled = true;
        }

        new FunctionParser('global_function_to_call');

        $this->assertTrue($functionCalled);
    }

    public function testGlobalFunctionWithParameter() {
        global $functionCalled;
        $functionCalled = false;

        function global_function_to_call_with_param($myParameter) {
            global $functionCalled;
            $functionCalled = $myParameter;
        }

        new FunctionParser('global_function_to_call_with_param', array(1337));

        $this->assertEquals($functionCalled, 1337);
    }

    public function testGlobalFunctionWithParameters() {
        global $functionCalled;
        $functionCalled = false;

        function global_function_to_call_with_multiple_param($firstParam, $secondParam) {
            global $functionCalled;
            $functionCalled = array($firstParam, $secondParam);
        }

        new FunctionParser('global_function_to_call_with_multiple_param', array('foo', 'bar'));

        $this->assertEquals($functionCalled, array('foo', 'bar'));
    }

    public function testClassFunction() {
        global $functionCalled;
        $functionCalled = false;

        new FunctionParser('MyFirstTestClass->functionToCall');

        $this->assertTrue($functionCalled);
    }

    public function testClassFunctionWithParams() {
        global $functionCalled;
        $functionCalled = false;

        new FunctionParser('MyFirstTestClass->functionToCallWithParams', array('Hello', 'World'));

        $this->assertEquals($functionCalled, 'HelloWorld');
    }
}