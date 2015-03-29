# Contains step definitions that are commonly used across many features and scenarios.

Given(/^the Dispute is NOT fully underway$/) do
  $dispute_id = DBL.dispute_title_to_id 'A fully assigned dispute with no lifespan'
end

Given(/^the Dispute is fully underway$/) do
  $dispute_id = DBL.dispute_title_to_id 'Smith versus Jones'
  assert State.dispute_active?
  assert ! State.dispute_in_mediation?
end

Given(/^the Dispute is in Mediation$/) do
  $dispute_id = DBL.dispute_title_to_id 'Dispute that is in mediation'
  assert State.dispute_active?
  assert State.dispute_in_mediation?
end

Then(/^I should see the message '(.+)'$/) do |expected_message|
  assert page.has_content?(expected_message)
end

Given(/^I am not logged into an account$/) do
end

Given(/^I am logged into an Individual account$/) do
  Session.clear_session_before_login
  Session.login_as_individual
end

Given(/^I am logged into an Organisation account$/) do
  Session.clear_session_before_login
  Session.login_as_law_firm
end

Given(/^I am logged into a(?:n)? (Law Firm|Mediation Centre) account$/) do |account_type|
  visit '/login'
  if account_type == 'Law Firm'
    Session.login_as_law_firm
  else
    Session.login_as_mediation_centre
  end
end

Then(/^I am logged into a(?:n)? (Agent|Mediator) account$/) do |account_type|
  visit '/login'
  if account_type == 'Agent'
    Session.login_as_agent
  else
    Session.login_as_mediator
  end
end

Given(/^I am logged into an Agent account that is not associated with the Dispute$/) do
  Session.clear_session_before_login
  Session.login_with_credentials 'one_dispute_agent@company.com', 'test'
end
