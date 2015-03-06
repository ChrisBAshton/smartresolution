Feature: Dispute creation
    Given I am logged into an authorised account
    Then I should be able to create a Dispute
    
  @clear
  Scenario: Creating a Dispute
    Given I am logged into a Law Firm account
    And I have created at least one Agent account
    Then I should be able to create a new Dispute
    # As far as we're concerned in these Cucumber tests, we're not bothered about the TYPE of dispute. All of these Disputes will default to 'Other', which adds nothing to the core functionality of the system. For specialised dispute types, it is up to the module developer to test their modules however they see fit... be it unit tests, integration tests or some combination.

  Scenario: Attempting to create a Dispute with no Agent
    Given I am logged into a Law Firm account
    And I have created NO Agent accounts
    Then I should see the message 'You must create an Agent account before you can create a Dispute!'

  Scenario: Trying to view a dispute I shouldn't have access to
    Given I am logged into a Mediation Centre account
    When I try to view a Dispute I've not been allocated to yet
    Then I should see the following message: 'You do not have permission to view this Dispute!'
  
  Scenario: Trying to view a dispute that does not exist
    Given I am logged into a Law Firm account
    When I try to view a Dispute that does not exist
    Then I should see the following message: 'The dispute you are trying to view does not exist.'

  @clear
  Scenario: Initiating a Dispute against a Law Firm
    Given I have submitted a Dispute
    Then I should be able to initiate it against another Law Firm
    And I shouldn't be able to reinitiate it against a different Law Firm

  @clear
  Scenario: A Dispute is opened against my Law Firm
    Given a Dispute has been initiated against my Law Firm
    And I have created an Agent
    Then I should be able to allocate the Agent to the Dispute