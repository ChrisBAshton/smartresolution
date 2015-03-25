# Contains step definitions that are commonly used across many features and scenarios.

Then(/^I should see the message '(.+)'$/) do |expected_message|
  assert page.has_content?(expected_message)
end

Given(/^I am not logged into an account$/) do
end

Given(/^I am logged into an Individual account$/) do
  clear_session_before_login
  login_as_individual
end

Given(/^I am logged into an Organisation account$/) do
  clear_session_before_login
  login_as_law_firm
end

Given(/^I am logged into a(?:n)? (Law Firm|Mediation Centre) account$/) do |account_type|
  visit '/login'
  if account_type == 'Law Firm'
    login_as_law_firm
  else
    login_as_mediation_centre
  end
end

Then(/^I am logged into a(?:n)? (Agent|Mediator) account$/) do |account_type|
  visit '/login'
  if account_type == 'Agent'
    login_as_agent
  else
    login_as_mediator
  end
end

Given(/^I am logged into an Agent account that is not associated with the Dispute$/) do
  clear_session_before_login
  login_with_credentials 'one_dispute_agent@company.com', 'test'
end
