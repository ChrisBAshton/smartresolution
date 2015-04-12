Feature: Module
    To extend SmartResolution with additional functionality,
    The software should support modules which can be plugged into the system.

    I've developed a Test Module specifically for this feature, so that I can
    write regression tests to make sure dashboard items are added, custom routing
    works, etc. This is for the purposes of regression testing only: by no means
    is a module REQUIRED to define custom routes and dashboard items, etc.

  Background:
    Given the Test Module has been installed and activated
    And I am logged into an Agent account
    And the Dispute is fully underway
    And the Dispute type has been set to 'Test Module'

  Scenario: Dispute dashboard item added
    Then I should see a custom dashboard item

  Scenario: Custom dispute-level-routing
    When I click on the custom dashboard item
    Then I should see the message 'Hello world!'
    And this message should have been passed as a parameter rather than hardcoded
    # (this tests parameter passing)

  Scenario: Custom top-level-routing
    When I try to visit '/module-test'
    Then I should see the message 'This module is meant to test the SmartResolution module support. It adds nothing useful and should be removed from production.'
    And the page should have this selector: 'h1#test-module'
    # (this tests markdown rendering)

  Scenario: Custom module database interaction
    When I click on the custom dashboard item
    Then I should see the message 'Entries in database: 0'
    When I click on the 'Create Database entry' button
    Then I should see the message 'Entries in database: 1'
    When I click on the 'Create Database entry' button
    Then I should see the message 'Entries in database: 2'