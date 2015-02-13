Feature: Account Creation
    I should be able to register an account of a certain type, e.g. Company/Agent
    And I should be able to log into said account

  Scenario: Company registration
    Given I am an authorised representative of the Company
    When I attempt to create a new Company account
    Then the account should be created
    # @TODO - as discussed in the supervisor meeting, we could add an admin verification stage at this stage.
    # This could be as complicated as we want to make it, so for now, let's add a boolean in the database that
    # says if the Company is verified or not. Make it verified by default, but carry out isVerified checks at login.
    # We can add the verification steps later and make Companies unverified by default.
    # At that stage, we need to add additional features, e.g. Given I am unverified, When I try to log in, Then I shoul
    # Not be allowed to do anything.

  Scenario: Agent initiates Case against a Company not registered to the system
    Given I have not yet registered a Company account
    And a Dispute has been initiated against my Company
    When I attempt to create a new Company account
    Then the account should be created
    And the my Company should be automatically linked to the dispute

  Scenario: Company login with valid credentials
    Given I have registered a Company account
    When I attempt to log in with valid credentials
    Then I should be logged into the system

  Scenario: Company login with invalid credentials
    Given I have registered a Company account
    When I attempt to log in with invalid credentials
    Then an authentication error should be displayed
    # @TODO - as discussed, correct and incorrect login attempts should be logged so that we can later add
    # additional security such as locking out accounts after a threshold of unsuccessful attempts is reached.

  Scenario: Agent login
  Scenario: Mediator login
  Scenario: Mediation Centre login

  Scenario: Create Agent account
    Given I have logged into a Company account
    Then I should be able to create an Agent account
    And the Agent should be sent an email notifying them they've been registered

  # Exactly the same process as for Company registration. There should be a drop-down list that lets registering users
  # select whether they're registering as a Company or a Mediation Centre
  Scenario: Mediation Centre registration
  Scenario: Create Mediator account