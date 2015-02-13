Feature: Dispute (under Mediation)
    The rules of the Dispute have now changed. All communication must be done through the Mediator.
    It is at this point that the business logic specific evidence-gathering can be applied, so that
    the artifical intelligence in the module can provide a second opinion to the Mediator.
    The Mediator, being a specialised and trained individual, can choose to ignore or amend the given
    advice.
  
  Background:
    Given the Dispute is fully underway
    And the Dispute is in Mediation

  Scenario: Block Agent A and B from communicating with one another
    Given we have not activated round-table communication
    Then I should not be able to communicate with the other Agent

  Scenario: Mediator requires further information
    Given a Dispute type was selected at the beginning of the Dispute # e.g. "maritime collision"
    Then the type-specific module should offer custom forms to the Agents to fill in

  # business logic specific stuff relating to Maritime Collisions etc MUST be put into a separate feature file
  # (in the plugin directory). This set of features must be as abstract and generic as possible.

  Scenario: Filling in the type-specific forms
    Given I am an Agent
    And I have filled in the forms provided by the type-specific module
    Then there should be no more forms to fill in
    And I should see that the system is awaiting a response from the other Agent

  Scenario: Filling in the type-specific forms as the second Agent
    Given I am an Agent
    And I have filled in the forms provided by the type-specific module
    And the other Agent has also filled in the forms
    Then there should be no more forms to fill in
    And I should see that the system is awaiting a response from the Mediator

  Scenario: AI logic is applied
    Given I am a Mediator
    And both Agents have completed the type-specific module forms
    Then I should see the results of the AI in the type-specific module
    #And I should be able to advise each Agent individually
    # Commented out the above line because it isn't testable. Essentially, the Mediator can send a
    # private message to either Agent, negotiating a resolution. It is up to the Agents to formally
    # send an offer through the "Propose Resolution" facility.

  Scenario: Accepting the Mediator's offer
    Given the Mediator has given me an offer
    Then I should be able to accept the offer
    And the Dispute should close successfully

  Scenario: Declining the Mediator's offer
    Given the Mediator has given me an offer
    Then I should be able to decline the offer
    And the Dispute should remain open

  Scenario: Sending an offer for round-table communication
    Given I am a Mediator
    Then I should be able to offer round-table communication
    # The Mediator should (through a dedicated facility) be able to propose round-table negotation,
    # whereby the free communication of all parties is enabled.

  Scenario: Accepting the offer for round-table communication
    Given the Mediator has suggested round-table communication
    Then I should be able to accept the offer
    And the Dispute should go into Round Table Mediation mode

  Scenario: Declining the offer for round-table communication
    Given the Mediator has suggested round-table communication
    Then I should be able to decline the offer
    And the Dispute should remain open and under Mediation
