Feature: Dispute (pre-Mediation)
    The Dispute is underway, both Agents are free to communicate with one another,
    propose offers, attach evidence, etc.
  
  Background:
    Given the Dispute is fully underway
    And the Dispute is not in Mediation

  Scenario: Free communication
    Then I should be able to communicate with the other Agent freely

  # The "Propose Resolution" mechanism outlined below is a separate facility to above.
  # Think of the free communication as a private messaging system (which gets blocked when
  # we go into Mediation, then re-opened with the additional Mediator person when entering
  # round-table communication).
  # The offer mechanism is a more formalised communication, where you offer a certain amount,
  # under X conditions - Accept | Deny | Propose Counter Offer

  Scenario: Make an offer
    Then I should be able to send the other Agent an offer

  Scenario: Accept the offer
    Given I have been sent an offer
    Then I should be able to accept the offer
    And the Dispute should close successfully

  Scenario: Propose counter-offer
    Given I have been sent an offer
    Then I should be able to propose a different offer

  # Note:
  # I can also Decline an offer by taking the Case to Court - see dispute_independent.feature "Take the Dispute to Court".
  # I can also propose Mediation. See dispute_independent.feature "Start the Mediation Process"