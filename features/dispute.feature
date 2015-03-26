Feature: Dispute
    The Dispute is underway, both Agents are free to communicate with one another,
    propose offers, attach evidence, etc.

  Background:
    Given I am logged into an Agent account
    And the Dispute is fully underway
    And the Dispute is not in Mediation

  @clear
  Scenario: Sending a message via an active dispute
    Then I should be able to send a message via the Dispute

  Scenario: Sending a message via an active dispute, as an unauthorised person
    Given I am logged into an Agent account that is not associated with the Dispute
    Then I should NOT be able to send a message via the Dispute

  @clear
  Scenario: Uploading evidence to a Dispute
    Then I should be able to upload evidence to the Dispute

  Scenario Outline: Attempting to view pages before you are allowed
    Given the Dispute is NOT fully underway
    When I attempt to view the '<page>' page
    Then I should see the message '<expected_message>'

  Examples:
    | page      | expected_message                              |
    | evidence  | You are not allowed to view these documents.  |
    | mediation | You do not have permission to view this page. |

  Scenario: Start the Mediation process
    Then I should be able to start the Mediation process

  @clear
  Scenario: Resolve a Dispute
    Then I should be able to mark the Dispute as resolved
    And the Dispute should close successfully

  @clear
  Scenario: Take the Dispute to Court
    Then I should be able to take the Dispute to court
    And the Dispute should close unsuccessfully
