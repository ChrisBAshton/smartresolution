Feature: Dispute
    The Dispute is underway, both Agents are free to communicate with one another,
    propose offers, attach evidence, etc.

  @clear
  Scenario: Sending a message via an active dispute
    Given the Dispute is fully underway
    And the Dispute is not in Mediation
    And I am logged into an Agent account
    Then I should be able to send a message via the Dispute

  Scenario: Sending a message via an active dispute, as an unauthorised person
    Given the Dispute is fully underway
    And the Dispute is not in Mediation
    And I am logged into a Mediator account
    Then I should NOT be able to send a message via the Dispute

  Scenario: Start the Mediation process
    Given the Dispute is fully underway
    And the Dispute is not in Mediation
    Then I should be able to start the Mediation process

  @clear
  Scenario: Resolve a Dispute
    Given the Dispute is fully underway
    Then I should be able to mark the Dispute as resolved
    And the Dispute should close successfully

  @clear
  Scenario: Take the Dispute to Court
    Given the Dispute is fully underway
    Then I should be able to take the Dispute to court
    And the Dispute should close unsuccessfully
