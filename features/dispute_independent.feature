Feature: Processes relevant to the Dispute but that are not dependent on the current state of the Dispute
    There are various functionalities that do not depend on the current state of the Dispute
    And should be accessible at any point in the Dispute

  Scenario: Start the Mediation process
    Given the Mediation process has not begun
    Then I should be able to start the Mediation process

  Scenario: Take the Dispute to Court
    Given the Dispute has not yet been resolved
    Then I should be able to Take the Dispute to Court
    And the Dispute should close unsuccessfully

  # Low priority
  Scenario: Communication with the Law Firm
    Given I am an Agent
    Then I should be able to communicate freely with my Law Firm