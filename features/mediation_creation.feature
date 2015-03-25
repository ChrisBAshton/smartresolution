Feature: Mediation
    At any point in a confirmed Dispute
    Either Agent can propose Mediation
    Whereby a Mediator is introduced to help to resolve the Dispute
    If complications arise during the Mediation Creation process - e.g. a
    list of Mediators being provided to the Agents but are not suitable,
    then either Agent can restart the Mediation process.

  Background:
    Given the Dispute is underway and a lifespan has been agreed

  @clear
  Scenario: Proposing Mediation
    Then I should be able to propose Mediation
    And my choice of Mediation Centre should be presented to the other Agent

  @clear
  Scenario: Declining a Mediation Centre proposal
    Given the other Agent has proposed a Mediation Centre
    Then I should be able to decline the proposal
    And I should see the message 'Make Mediation Proposal'

  @clear
  Scenario: Accepting a Mediation Centre proposal
    Given the other Agent has proposed a Mediation Centre
    Then I should be able to accept the proposal
    And I should see the message 'You are waiting for the Mediation Centre to provide a list of available Mediators. You will be notified when this happens.'

  @clear
  Scenario: Providing a list of available Mediators
    Given the Agents have agreed on a Mediation Centre
    And I am logged in as the Mediation Centre
    Then I should be able to provide a list of available Mediators

  @clear
  Scenario: Choosing a Mediator from the list
    Given the Mediation Centre we've agreed upon has provided a list of available Mediators
    And I am logged into an Agent account
    Then I should be able to propose a Mediator to the other Agent

  @clear
  Scenario: Declining a Mediator proposal
    Given the other Agent has proposed a Mediator
    Then I should be able to decline the proposal
    And I should see the message 'Propose Mediator'

  @clear
  Scenario: Accepting a Mediator proposal
    Given the other Agent has proposed a Mediator
    Then I should be able to accept the proposal
    And the Dispute should be in Mediation
