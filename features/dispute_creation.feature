Feature: Dispute creation
    Given I am logged into an authorised account
    Then I should be able to create a Dispute
    
  Scenario: Creating a Dispute
    Given I am logged into a Law Firm account
    And I have created at least one Agent account
    Then I should be able to create a new Dispute

  Scenario: Attempting to create a Dispute with no Agent
    Given I am logged into a Law Firm account
    And I have created NO Agent accounts
    Then I should see the message 'You must create an Agent account before you can create a Dispute!'

  # Scenario: Allocating a Dispute to an Agent
  #   Given I have created a Dispute
  #   And I have created an Agent
  #   Then I should be able to allocate the Agent to the Dispute # this should also be possible AT the Dispute creation stage

  # Scenario: Submitting a Dispute
  #   Given a Dispute has been assigned to me # by my Law Firm, regardless of who instigated the Dispute
  #   When I write a Dispute summary
  #   And I choose to submit the Dispute to the system
  #   Then the Dispute should be submitted

  # Scenario: Initiating a Dispute against a Law Firm
  #   Given I have submitted a Dispute
  #   Then I should be able to initiate it against another Law Firm
  #   # We pick from a drop-down list of Law Firms in the system
  #   # or provide a Law Firm email address inviting them to register.

  # Scenario: Being initiated a Dispute
  #   Given a Dispute has been initiated against my Law Firm
  #   And I have created an Agent
  #   Then I should be able to allocate the Agent to the Dispute