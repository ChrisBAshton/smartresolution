<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testUserPasswordCheck()
    {   
        // pull user info from the test database
        Database::setEnvironment('test');

        // only setting user email and pass here because I have to. @TODO - allow empty constructor params
        $user = new Agent('cba1@aber.ac.uk', 'test');
        $this->assertEquals($user->email, 'cba1@aber.ac.uk');

        // this is where the testing starts.
        $this->assertFalse($user->correctPassword('test', 'test'));
        $this->assertFalse($user->correctPassword('test', ''));
        $this->assertTrue($user->correctPassword('test', '$2y$10$md2.JKnCBFH5IGU9MeV50OUtx35VdVcThXeeQG9QUbpm9DwYmBlq.'));
    }
}
?>