Feature: Notifications
    As a logged in user,
    When something interesting happens
    Then I should have some sort of notification so that I know about it
    And I should be able to mark that notification as read

  @clear
  Scenario: Being notified of a Dispute
    Given a Dispute has been assigned to me
    And I am logged into a one-dispute Agent account
    Then I should get a notification about the Dispute

  Scenario: Marking the Dispute as read
    Given I am logged into a one-dispute Agent account
    And I have a new notification
    When I click on the associated link
    Then the notification should be marked as read
    And the URL should be clean, with no notification parameters

  @clear
  Scenario: Notification when agent sends message to agent
    Given I am logged into an Agent account
    When I send a message to the other Agent in a dispute
    Then they should get a notification

  Scenario: Notification when agent sends message to mediator
    Given I am logged into an Agent account
    And my dispute is in mediation
    When I send a message to the mediator of a dispute
    Then they should get a notification

  Scenario: Notification when mediator sends message to agent
    Given I am logged into a Mediator account
    And my dispute is in mediation
    When I send a message to an agent of a dispute
    Then they should get a notification

  # # @TODO - in a later version of SmartResolution.
  # Scenario: Notification when anyone sends message via round-table communication
  #   Given a dispute is in round-table communication
  #   And I am logged into an Agent account
  #   When I send a message to to the round-table
  #   Then everyone else should get a notification