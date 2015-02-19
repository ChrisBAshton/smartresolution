When(/^I attempt to create a new Law Firm account$/) do
  page.driver.headers = { "User-Agent" => "Poltergeist" }
  visit '/register'
  assert page.has_content?('Welcome to the registration screen')
  fill_in 'Email', :with => 'admin'
  fill_in 'Password', :with => 'test'
  page.driver.save_screenshot 'features/screenshots/test.jpg'
end

Then(/^the account should be created$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^I have not yet registered a Law Firm account$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^a Dispute has been initiated against my Law Firm$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^the my Law Firm should be automatically linked to the dispute$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^I have registered a Law Firm account$/) do
  pending # express the regexp above with the code you wish you had
end

When(/^I attempt to log in with valid credentials$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should be logged into the system$/) do
  pending # express the regexp above with the code you wish you had
end

When(/^I attempt to log in with invalid credentials$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^an authentication error should be displayed$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^I have logged into a Law Firm account$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should be able to create an Agent account$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^the Agent should be sent an email notifying them they've been registered$/) do
  pending # express the regexp above with the code you wish you had
end
