<?php
require_once __DIR__ . '/../webapp/autoload.php';

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public static function setUpBeforeClass()
    {
        Database::setEnvironment('test');
        Database::clear();
        Database::instance()->exec(
            'INSERT INTO account_details (login_id, email, password) VALUES (NULL, :email, :password)',
            array(
                ':email'    => 'test@test.com',
                ':password' => 'password'
            )
        );
    }

    /**
     * This test should raise an exception because we're defining an invalid value for 'type'.
     * @expectedException PDOException
     */
    public function testOrganisationsTypeConstraint()
    {
        Database::instance()->exec('INSERT INTO organisations (type) VALUES ("fail")');
    }

    /**
     * This should raise an exception because we don't have a corresponding entry in account_details
     * @expectedException PDOException
     */
    public function testOrganisationsForeignKeyConstraint()
    {
        Database::instance()->exec('INSERT INTO organisations (login_id) VALUES (1337)');
    }

    /**
     * This test should raise an exception because we're defining an invalid value for 'type'.
     * @expectedException PDOException
     */
    public function testIndividualsTypeConstraint()
    {
        Database::instance()->exec('INSERT INTO individuals (type) VALUES ("fail")');
    }

    /**
     * This should raise an exception because we don't have a corresponding entry in account_details
     * @expectedException PDOException
     */
    public function testIndividualsForeignKeyConstraint()
    {
        Database::instance()->exec('INSERT INTO individuals (login_id) VALUES (1337)');
    }

    public function testCorrectOrganisationsEntry()
    {
        Database::instance()->exec(
            'INSERT INTO organisations (login_id, type, name, description) VALUES (:login_id, :type, :name, :description)',
            array(
                ':login_id'    => '1',
                ':type'        => 'law_firm',
                ':name'        => 'Webdapper',
                ':description' => 'This is a law firm specialising in Maritime Law.'
            )
        );
    }

    public function testCorrectIndividualsEntry()
    {
        Database::instance()->exec(
            'INSERT INTO individuals (login_id, type, surname, forename) VALUES (:login_id, :type, :surname, :forename)',
            array(
                ':login_id' => '1',
                ':type'     => 'mediator',
                ':surname'  => 'Ashton',
                ':forename' => 'Chris'
            )
        );
    }
}
?>
