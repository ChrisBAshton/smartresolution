Feature: Dispute (under Mediation)
    The rules of the Dispute have now changed. All communication must be done through the Mediator.
    It is at this point that the business logic specific evidence-gathering can be applied, so that
    the artifical intelligence in the module can provide a second opinion to the Mediator.
    The Mediator, being a specialised and trained individual, can choose to ignore or amend the given
    advice.

  Background:
    Given the Dispute is in Mediation

  Scenario: Block Agent A and B from communicating with one another
    Given we have not activated round-table communication
    Then I should not be able to communicate with the other Agent

  Scenario: Communication between Agent and Mediator
    Given we have not activated round-table communication
    And I am logged into a Mediator account
    Then I should be able to communicate with the Agents in individual threads

  Scenario: Sending an offer for round-table communication
    Given I am a Mediator
    Then I should be able to offer round-table communication

  Scenario: Accepting the offer for round-table communication
    Given the Mediator has suggested round-table communication
    Then I should be able to accept the offer
    And the Dispute should go into round-table communication mode

  Scenario: Mediation is in round-table communication
    Given the Dispute is in round-table communication mode
    Then all parties should be able to communicate freely

  Scenario: Declining the offer for round-table communication
    Given the Mediator has suggested round-table communication
    Then I should be able to decline the offer
    And the Dispute should remain open and under Mediation
