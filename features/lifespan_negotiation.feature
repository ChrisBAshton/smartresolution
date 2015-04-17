Feature: Negotiating a Dispute lifespan
    When a Dispute is opened and each Law Firm has allocated an Agent
    The Agents need to negotiate a Dispute lifespan
    i.e. the maximum length of time the Dispute can continue without resolution
    before being automatically taken to Court.

  @clear
  Scenario: Creating a Dispute lifespan offer
    Given both Agents have submitted the Dispute
    Then I should be able to make a lifespan offer

  @clear
  Scenario: Accepting a Dispute lifespan offer
    Given the other Agent has sent me a Dispute lifespan offer
    Then I should be able to Accept the offer
    And I should see the message 'Dispute starts in'

  @clear
  Scenario: Create a counter Dispute lifespan offer
    Given the other Agent has sent me a Dispute lifespan offer
    Then I should be able to Decline the offer
    And I should be able to make a lifespan offer

  @clear
  Scenario: Making a lifespan offer mid-Dispute
    Given I am logged into an Agent account
    And the Dispute is fully underway
    Then I should be able to make a lifespan offer
    And the Dispute should continue normally despite the renegotiation offer

  @clear
  Scenario: Renegotiating the Dispute lifespan mid-Dispute
    Given I am logged into an Agent account
    And the Dispute is fully underway
    When I make a new lifespan offer
    And the other Agent accepts the offer
    Then the new lifespan should take immediate effect
    And I should see the message 'The dispute is currently on hold until the new lifespan comes into effect.'
