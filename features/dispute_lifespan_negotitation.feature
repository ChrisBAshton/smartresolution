Feature: Negotiating a Dispute lifespan
    When a Dispute is opened and each Company has allocated an Agent
    The Agents need to negotiate a Dispute lifespan
    i.e. the maximum length of time the Dispute can continue without resolution 
    before being automatically taken to Court.

  Scenario: Creating a Dispute lifespan offer
    Given both Agents have submitted the Dispute
    Then I should be able to make a lifespan offer # regardless of who submitted the Dispute first

  Scenario: Accepting a Dispute lifespan offer
    Given the other Agent has sent me a Dispute lifespan offer
    Then I should be able to accept the offer
    And the Dispute should start

  Scenario: Create a counter Dispute lifespan offer
    Given the other Agent has sent me a Dispute lifespan offer
    Then I should be able to make a lifespan offer
    And therefore decline their original offer

  Scenario: Renegotiating the Dispute lifespan mid-Dispute
    Given the Dispute is fully underway
    Then I should be able to make a lifespan offer
    And the Dispute should continue normally despite the renegotiation offer