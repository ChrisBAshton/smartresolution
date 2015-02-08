Feature: Account Creation
    I should be able to register an account of a certain type, e.g. Company/Agent
    And I should be able to log into said account

  # @TODO - it seems strange to separate out the Company and Agent concepts. What about the one-person companies who only have one Agent? Would it not make more sense to have a Company Details option in the Agent creation process (where the Agent can choose from an existing Company in the system, or create a new one)?
  # The difficulty then would be in what happens when a Dispute is opened against the Company. Who gets the notification? It could be all Agents, and an Agent could allocate the Dispute to themselves. Or it could be to the administrators of the Company (i.e. the Agent who created the Company details, and any Agents they've since marked as admin status) - the admins would have the ability to allocate to any Agent in the Company.
  # The advantage of this is the single-registration step. If an Agent is responsible for describing which Company they're from (by creating the Company or choosing from a list), we only have to test the Agent registration and login functionalities, not those functionalities for both the Company AND the Agent account type.
  # it would get around some complications, e.g. what happens if a Dispute is opened against a Company but the Company has
  # no Agents?
  Scenario: Company registration
    Given I am an authorised representative of the Company
    When I attempt to create a new Company account
    Then the account should be created
    # @TODO - is there some form of remote verification required before the account is "activated"?

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
    # @TODO - should incorrect login attempts be logged? If so, should they be capped at a certain number of tries before the account is locked?

  ####################################################################################
  ########### Until some of the above questions are answered, we can't really define the features below in more depth.
  ####################################################################################
  
  # @TODO - should the Agent be sent an email? Have a password generated for it? Require "activation" through email verification?
  Scenario: Create Agent account

  # @TODO - is it possible to combine this and the Company registration? Or must the two concepts be kept separate?
  # Is it ever possible that a Company might have both Agents AND Mediators? Right now, looks like Mediation Centre is a sub type of Company.
  Scenario: Mediation Centre registration

  # @TODO - is the Mediator creation process the same as the Agent creation process (i.e. Mediation Centre account must create it) or can a Mediator register an account independently? In any case, it seems strange to separate out the Mediation Centre/Mediator concepts, in the same way as it seems strange to separate out the Company/Agent concepts
  Scenario: Create Mediator account

  Scenario: Agent login
  Scenario: Mediator login
  Scenario: Mediation Centre login