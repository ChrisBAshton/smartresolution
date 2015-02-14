<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class RegisterTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        Database::setEnvironment('test');
        Database::clear();
        $db = Database::instance();
    }

    public function testRegisterOrganisation()
    {
        // $org = new Organisation();

        // $this->assertTrue($org->valid());

        new RegisterOrganisation(array(
            'email'       => 'cba12@aber.ac.uk',
            'password'    => 'test',
            'name'        => 'Webdapper',
            'type'        => 'law_firm',
            'description' => 'A law firm'
        ));
    }
}
?>