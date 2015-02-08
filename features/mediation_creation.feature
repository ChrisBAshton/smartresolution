Feature: Mediation
    At any point in a confirmed Dispute
    Either Agent can propose Mediation
    Whereby a Mediator is introduced to help to resolve the Dispute

  Scenario: Choosing a Mediation Centre
    Given both Agents have agreed to start the Mediation process
    Then I should be able to select the Mediation Centres I'm happy with

  Scenario: No mutually chosen Mediation Centres
    Given both Agents have selected the Mediation Centres they want
    And there are no matches in their choices
    Then both Agents should have the opportunity to choose again

  Scenario: No mutually chosen Mediation Centres - twice.
    Given both Agents have selected the Mediation Centres they want
    And there are no matches in their choices
    And this is already the second time they've tried to select mutual choices
    Then the Dispute should close unsuccessfully

  Scenario: One mutually chosen Mediation Centre
    Given both Agents have selected the Mediation Centres they want
    And there is only one match in their choices
    Then that should be the chosen Mediation Centre # @TODO - check this - do we need to get the Agents to confirm?

  Scenario: Multiple mutually chosen Mediation Centres
    Given both Agents have selected the Mediation Centres they want
    And there are several matching choices
    Then one Mediation Centre must be chosen upon by both Agents # @TODO - how???

  Scenario: Mediation Centre is notified of the Agents' decision
    Given my Mediation Centre has been chosen by both Agents of a Dispute
    Then I should be notified that my Mediation Centre has been chosen
    And I should have the facility to offer a list of Mediators to the Agents

  # @TODO - what happens if none of the Mediators are suitable? What happens if the Mediation Centre has no Mediators? Should the Agents have the option to choose a different Mediation Centre - if so, how? Or should the Dispute be taken to Court?

  Scenario: Mediation Centre provides list of Mediators
    Given a Mediation Centre has provided a list of available Mediators
    Then I should be able to view the details of each Mediator # including CV, etc
    And I should be able to select the Mediators I'm happy with

  Scenario: No mutually chosen Mediators
    Given both Agents have selected the Mediators they want
    And there are no matches in their choices
    Then both Agents should have the opportunity to choose again

  Scenario: No mutually chosen Mediators - twice.
    Given both Agents have selected the Mediators they want
    And there are no matches in their choices
    And this is already the second time they've tried to select mutual choices
    Then the Dispute should close unsuccessfully

  Scenario: One mutually chosen Mediator
    Given both Agents have selected the Mediators they want
    And there is only one match in their choices
    Then that should be the chosen Mediator

  Scenario: Multiple mutually chosen Mediators
    Given both Agents have selected the Mediators they want
    And there are several matching choices
    Then one Mediator must be chosen upon by both Agents # @TODO - how???

  Scenario: Mediator is notified of the Agents' decision
    Given I am a Mediator
    And I have been chosen by both Agents of a Dispute
    Then I should be notified that I have been chosen
    And I should be made to sign a confidentiality agreement # @TODO - what happens if I refuse? Or take a long time to get around to it, thereby pushing the Dispute lifespan?

  Scenario: Mediator signs confidentiality agreement
    Given I am a mutually-chosen Mediator for a given Dispute
    And I sign the confidentiality agreement
    Then the Dispute should now be in Mediation Mode
