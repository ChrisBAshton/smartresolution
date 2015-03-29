Feature: Dispute (under Mediation)
    The rules of the Dispute have now changed. All communication must be done through the Mediator.
    It is at this point that the business logic specific evidence-gathering can be applied, so that
    the artifical intelligence in the module can provide a second opinion to the Mediator.
    The Mediator, being a specialised and trained individual, can choose to ignore or amend the given
    advice.

  Background:
    Given I am logged into a Mediator account
    And the Dispute is in Mediation

  @clear
  Scenario: Block Agent A and B from communicating with one another
    Given I am logged into an Agent account
    And we have not activated round-table communication
    Then I should not be able to communicate with the other Agent

  Scenario: Communication between Agent and Mediator
    Given we have not activated round-table communication
    Then I should be able to communicate with the Agents in individual threads
    And there should be no way for either Agent to see the messages of the other

  Scenario: Trying to send a message to someone not involved in the dispute
    Given I try to send a message to someone not involved in the dispute
    Then I should see the message 'The account you're trying to send a message to is not involved in this dispute!'

  Scenario: Enabling round-table communication
    Then I should be able to enable round-table communication

  @clear
  Scenario: Mediation is in round-table communication
    Given the Dispute is in round-table communication mode
    Then all parties should be able to communicate freely

  @clear
  Scenario: Disabling round-table communication mode
    Given the Dispute is in round-table communication mode
    Then I should be able to disable round-table communication
    And free communication should no longer be allowed between all parties
