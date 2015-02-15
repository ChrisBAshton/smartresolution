<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class RegisterTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        Database::setEnvironment('test');
        Database::clear();
    }

    public function testRegisterOrganisation()
    {
        Register::organisation(array(
            'email'       => 'cba12@aber.ac.uk',
            'password'    => 'test',
            'name'        => 'Webdapper',
            'type'        => 'law_firm',
            'description' => 'A law firm'
        ));
    }

    public function testGetValueFunction()
    {
        $testArray = array(
            'exists' => 'this value exists'
        );

        $result = Register::getValue($testArray, 'exists', 'default value');
        $this->assertEquals('this value exists', $result);

        $result = Register::getValue($testArray, 'does not exist', 'default value');
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
        $result = Register::getValue($testArray, 'this index must exist or else we raise an exception');
    }
}
?>