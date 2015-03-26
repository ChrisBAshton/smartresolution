Feature: Profile
    Profiles are key to SmartResolution, allowing Agents to view the CVs of Mediators
    Which helps them to choose a Mediator when the Dispute requires Mediation.
    Similarly, Organisations can specify their description, linking to external sites etc.
    This helps Agents to choose a Mediation Centre in the first place.

  Background:
    Given I am logged into an Agent account

  Scenario: Viewing the profile of an Organisation
    Given I am looking at an Organisation's profile
    Then I should see the Organisation description

  Scenario: Viewing the profile of an Individual
    Given I am looking at an Individual's profile
    Then I should see the Individual's CV
    And I should see which Organisation the Individual works for

  Scenario: Editing an Organisation profile
    Given I am logged into an Organisation account
    Then I should be able to edit my Organisation's description

  Scenario: Editing an Individual profile
    Given I am logged into an Individual account
    Then I should be able to edit my CV
