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
    Given I am logged into the 'agent_a@t.co' account
    When I send a message to the other Agent in the 'Smith versus Jones' dispute
    Then 'agent_b@t.co' should get a notification that 'Chris Ashton' has sent them a message

  Scenario: Notification when agent sends message to mediator
    Given I am logged into the 'agent_a@t.co' account
    When I send a message to the mediator in the 'Dispute that is in mediation' dispute
    Then 'john.smith@we-mediate.co.uk' should get a notification that 'Chris Ashton' has sent them a message

  Scenario: Notification when mediator sends message to agent
    Given I am logged into the 'john.smith@we-mediate.co.uk' account
    When I send a message to 'agent_a@t.co' in the 'Dispute that is in mediation' dispute
    Then 'agent_a@t.co' should get a notification that 'John Smith' has sent them a message

  # # @TODO - in a later version of SmartResolution.
  # Scenario: Notification when anyone sends message via round-table communication
  #   Given a dispute is in round-table communication
  #   And I am logged into an Agent account
  #   When I send a message to to the round-table
  #   Then everyone else should get a notification