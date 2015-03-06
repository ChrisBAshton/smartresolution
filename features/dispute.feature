Feature: Dispute
    The Dispute is underway, both Agents are free to communicate with one another,
    propose offers, attach evidence, etc.

  Scenario: Free communication
    Given the Dispute is fully underway
    And the Dispute is not in Mediation
    Then I should be able to communicate with the other Agent freely

  Scenario: Start the Mediation process
    Given the Dispute is fully underway
    And the Dispute is not in Mediation
    Then I should be able to start the Mediation process

  Scenario: Take the Dispute to Court
    Given the Dispute is fully underway
    Then I should be able to take the Dispute to court
    And the Dispute should close unsuccessfully

  # Low priority
  Scenario: Communication with the Law Firm
    Given I am an Agent
    Then I should be able to communicate freely with my Law Firm