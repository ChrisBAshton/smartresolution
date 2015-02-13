Feature: Dispute creation
    Given I am logged into an authorised account
    Then I should be able to create a Dispute
    
  Scenario: Creating a Dispute
    Given I am logged into a Company account
    Then I should be able to create a new Dispute

  Scenario: Allocating a Dispute to an Agent
    Given I have created a Dispute
    And I have created an Agent
    Then I should be able to allocate the Agent to the Dispute # this should also be possible AT the Dispute creation stage

  Scenario: Submitting a Dispute
    Given a Dispute has been assigned to me # by my Company, regardless of who instigated the Dispute
    When I write a Dispute summary
    And I choose to submit the Dispute to the system
    Then the Dispute should be submitted

  Scenario: Initiating a Dispute against a Company
    Given I have submitted a Dispute
    Then I should be able to initiate it against another Company
    # We pick from a drop-down list of Companies in the system
    # or provide a Company email address inviting them to register.

  Scenario: Being initiated a Dispute
    Given a Dispute has been initiated against my Company
    And I have created an Agent
    Then I should be able to allocate the Agent to the Dispute