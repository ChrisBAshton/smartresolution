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
    
  # @TODO - add more pages