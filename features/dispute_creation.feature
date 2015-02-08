Feature: Dispute creation
    Given I am logged into an authorised account
    Then I should be able to create a Dispute
    
  Scenario: Creating a Dispute
    Given I am logged into an authorised account
    # @TODO - according to the initial requirements, this should be a Company, NOT an Agent
    # But I'm hoping both are allowed! If we allow Agents to create Disputes, they needn't 
    # necessarily be allocated the Dispute - they could allocate it to their Company, and 
    # the "Allocating a Dispute to an Agent" step would follow. Or, the Agent creating the
    # Dispute could assign it to an Agent (including themselves) as part of the creation
    # process. Need to clarify if this would be OK - I think it would make for a more
    # intuitive system.
    Then I should be able to create a new Dispute

  Scenario: Allocating a Dispute to an Agent
    Given I have created a Dispute
    And I have created an Agent
    Then I should be able to allocate the Agent to the Dispute
    # @TODO - can Companies allocate Agents at the same time as creating the Dispute?

  Scenario: Submitting a Dispute
    Given a Dispute has been assigned to me # by my Company, regardless of whether we're "prosecuting" or "defending"
    When I write a Dispute summary
    And I choose to submit the Dispute to the system
    Then the Dispute should be submitted

  Scenario: Initiating a Dispute against a Company
    Given I have submitted a Dispute
    Then I should be able to initiate it against another Company
    # @TODO how? If they have an account, maybe we pick from a drop-down list of Companies.
    # If they don't have an account, maybe we provide an email and they need to register at this point.

  Scenario: Being initiated a Dispute
    Given a Dispute has been initiated against my Company
    And I have created an Agent
    Then I should be able to allocate the Agent to the Dispute