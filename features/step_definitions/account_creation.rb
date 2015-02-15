Given(/^I am an authorised representative of the Company$/) do
  # nothing to do - just provides a nice background to the scenario
end

When(/^I attempt to create a new Company account$/) do
  visit 'http://127.0.0.1:8000/register'
end

Then(/^the account should be created$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^I have not yet registered a Company account$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^a Dispute has been initiated against my Company$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^the my Company should be automatically linked to the dispute$/) do
  pending # express the regexp above with the code you wish you had
end

Given(/^I have registered a Company account$/) do
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

Given(/^I have logged into a Company account$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should be able to create an Agent account$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^the Agent should be sent an email notifying them they've been registered$/) do
  pending # express the regexp above with the code you wish you had
end