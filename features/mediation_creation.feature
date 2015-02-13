Feature: Mediation
    At any point in a confirmed Dispute
    Either Agent can propose Mediation
    Whereby a Mediator is introduced to help to resolve the Dispute
    If complications arise during the Mediation Creation process - e.g. a
    list of Mediators being provided to the Agents but are not suitable,
    then either Agent can restart the Mediation process.

  Background:
    Given the Dispute is underway and a lifespan has been agreed

  Scenario: Choosing a Mediation Centre
    Given both Agents have agreed to start the Mediation process
    Then I should be able to select the Mediation Centres I'm happy with
    # We do this by choosing the Mediation Centres we want AND an order of preference.

  Scenario: No mutually chosen Mediation Centres
    Given both Agents have selected the Mediation Centres they want
    And there are no matches in their choices
    Then both Agents should have the opportunity to choose again

  Scenario: One mutually chosen Mediation Centre
    Given both Agents have selected the Mediation Centres they want
    And there is only one match in their choices
    Then that should be the chosen Mediation Centre

  Scenario: Multiple mutually chosen Mediation Centres
    Given both Agents have selected the Mediation Centres they want
    And there are several matching choices
    Then one Mediation Centre must be chosen upon by both Agents
    # It's been suggested we could make Agents choose (before this step)
    # the order of preference for the Mediators, then the system could suggest
    # a Mediator based on a points system.

  Scenario: Mediation Centre is notified of the Agents' decision
    Given my Mediation Centre has been chosen by both Agents of a Dispute
    Then I should be notified that my Mediation Centre has been chosen
    And I should have the facility to offer a list of Mediators to the Agents

  Scenario: Mediation Centre provides list of Mediators
    Given a Mediation Centre has provided a list of available Mediators
    Then I should be able to view the details of each Mediator # including CV, etc
    And I should be able to select the Mediators I'm happy with

  Scenario: No mutually chosen Mediators
    Given both Agents have selected the Mediators they want
    And there are no matches in their choices
    Then both Agents should have the opportunity to choose again

  Scenario: One mutually chosen Mediator
    Given both Agents have selected the Mediators they want
    And there is only one match in their choices
    Then that should be the chosen Mediator

  Scenario: Multiple mutually chosen Mediators
    Given both Agents have selected the Mediators they want
    And there are several matching choices
    Then one Mediator must be chosen upon by both Agents

  Scenario: Mediator is notified of the Agents' decision
    Given I am a Mediator
    And I have been chosen by both Agents of a Dispute
    Then I should be notified that I have been chosen
    And I should be made to sign a confidentiality agreement

  Scenario: Mediator signs confidentiality agreement
    Given I am a mutually-chosen Mediator for a given Dispute
    And I sign the confidentiality agreement
    Then the Dispute should now be in Mediation Mode
