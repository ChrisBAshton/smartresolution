<?php
require_once __DIR__ . '/../webapp/autoload.php';

class UtilsTest extends PHPUnit_Framework_TestCase
{
    public function testGetValueFunction()
    {
        $testArray = array(
            'exists' => 'this value exists'
        );

        $result = Utils::getValue($testArray, 'exists', 'default value');
        $this->assertEquals('this value exists', $result);

        $result = Utils::getValue($testArray, 'does not exist', 'default value');
        $this->assertEquals('default value', $result);
    }

    /**
     * This test should raise an exception because we're trying to retrieve a value
     * that MUST exist, and it does not.
     * @expectedException Exception
     */
    public function testGetValueFunctionExceptionRaising()
    {
        $testArray = array();
        $result = Utils::getValue($testArray, 'this index must exist or else we raise an exception');
    }
}
