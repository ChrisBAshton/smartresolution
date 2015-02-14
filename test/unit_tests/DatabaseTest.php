<?php
require_once __DIR__ . '/../../webapp/classes/autoload.php';

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    private $db;

    protected function setUp()
    {
        Database::setEnvironment('test');
        Database::clear();
        $this->db = Database::instance();
        $this->db->exec(
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
        $this->db->exec('INSERT INTO organisations (type) VALUES ("fail")');
    }

    /**
     * This should raise an exception because we don't have a corresponding entry in account_details
     * @expectedException PDOException
     */
    public function testOrganisationsForeignKeyConstraint()
    {
        $this->db->exec('INSERT INTO organisations (login_id) VALUES (1337)');
    }

    /**
     * This test should raise an exception because we're defining an invalid value for 'type'.
     * @expectedException PDOException
     */
    public function testIndividualsTypeConstraint()
    {
        $this->db->exec('INSERT INTO individuals (type) VALUES ("fail")');
    }

    /**
     * This should raise an exception because we don't have a corresponding entry in account_details
     * @expectedException PDOException
     */
    public function testIndividualsForeignKeyConstraint()
    {
        $this->db->exec('INSERT INTO individuals (login_id) VALUES (1337)');
    }

    public function testCorrectOrganisationsEntry()
    {
        $this->db->exec(
            'INSERT INTO organisations (organisation_id, login_id, type, name, description) VALUES (NULL, :login_id, :type, :name, :description)',
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
        $this->db->exec(
            'INSERT INTO individuals (individual_id, login_id, type, surname, forename) VALUES (NULL, :login_id, :type, :surname, :forename)',
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