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
    And I am logged into an Agent account that is not associated with the Dispute
    Then I should NOT be able to send a message via the Dispute

  @clear
  Scenario: Uploading evidence to a Dispute
    Given the Dispute is fully underway
    And I am logged into an Agent account
    Then I should be able to upload evidence to the Dispute

  Scenario: Attempting to view evidence when not allowed
    Given the Dispute is NOT fully underway
    And I am logged into an Agent account
    When I attempt to view the Evidence page
    Then I should see the message 'You are not allowed to view these documents.'

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
