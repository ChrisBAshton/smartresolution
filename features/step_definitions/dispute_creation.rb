Given(/^I have created at least one Agent account$/) do
end

Given(/^I have created NO Agent accounts$/) do
  visit '/logout' # logout of 'law_firm_email' as it has an Agent account
  visit '/login'
  login_with_credentials 'law_firm_email_with_no_agents', 'test'
  visit '/disputes/new'
end

Then(/^I should be able to create a new Dispute$/) do
  pending # express the regexp above with the code you wish you had
end