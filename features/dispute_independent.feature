Feature: Processes relevant to the Dispute but that are not dependent on the current state of the Dispute
    There are various functionalities that do not depend on the current state of the Dispute
    And should be accessible at any point in the Dispute

  Scenario: Start the Mediation process
    Given the Mediation process has not begun
    Then I should be able to start the Mediation process
    # @TODO - what happens if this process is started before the Dispute lifespan has been agreed (as has been suggested is possible in the initial requirements)? Does the Mediator set a default Dispute lifespan, or does the Dispute continue indefinitely?

  Scenario: Take the Dispute to Court
    Given the Dispute has not yet been resolved
    Then I should be able to Take the Dispute to Court
    And the Dispute should close unsuccessfully