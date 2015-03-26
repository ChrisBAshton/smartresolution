Feature: Routing
    As this is a RESTful web app,
    Some users might try to access pages when they shouldn't.
    So we should ensure that the appropriate error messages and redirects are in place.

  Scenario Outline: Accessing session-only pages
    Given I am not logged into an account
    When I try to visit '<forbidden_page>'
    Then I should see the message 'You do not have permission to see this page. Please log in first.'

    Examples:
      | forbidden_page       |
      | /register/individual |

  Scenario Outline: Accessing Organisation-only pages
    Given I am logged into an Individual account
    When I try to visit '<organisation_only_page>'
    Then I should see the message 'You do not have permission to see this page. You must be logged into an Organisation account.'

    Examples:
      | organisation_only_page |
      | /register/individual   |
      | /disputes/new          |

  Scenario Outline: Accessing Individual-only pages
    Given I am logged into an Organisation account
    When I try to visit '<individual_only_page>'
    Then I should see the message 'You do not have permission to see this page. You must be logged into an'

    Examples:
      | individual_only_page   |
      | /disputes/8/chat       |
      | /disputes/8/close      |
