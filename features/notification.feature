Feature: Notifications
    As a logged in user,
    When something interesting happens
    Then I should have some sort of notification so that I know about it
    And I should be able to mark that notification as read

  @clear
  Scenario: Being notified of a Dispute
    Given a Dispute has been assigned to me
    And I am logged into an Agent account
    Then I should get a notification about the Dispute

  Scenario: Marking the Dispute as read
    Given I am logged into an Agent account
    And I have a new notification
    When I click on the associated link
    Then the notification should be marked as read
    And the URL should be clean, with no notification parameters