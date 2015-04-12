Feature: Admin
    SmartResolution administrators can log into the instance and
    customise the instance according to the needs of the business.

  Scenario: Logging into an admin account
    Given I am logged into an Admin account
    Then I should see admin-only options on the dashboard

  Scenario: Installing a new module
    Given I am logged into an Admin account
    When I visit the SmartResolution Marketplace
    Then I should be able to install new modules

  Scenario: Activating the module
    Given I am logged into an Admin account
    And I have installed a new module
    Then I should be able to activate the module

  Scenario: Deactivating a module
    Given I am logged into an Admin account
    And I have activated a module
    Then I should be able to deactivate the module

  Scenario: Uninstalling a module
    Given I am logged into an Admin account
    And I have installed a new module
    Then I should be able to uninstall the module

  # # @TODO
  # Scenario: Customising the SmartResolution instance
  #   Given I am logged into an Admin account
  #   Then I should be able to change the SmartResolution logo