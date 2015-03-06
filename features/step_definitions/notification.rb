Given(/^a Dispute has been assigned to me$/) do
end

And(/^I am logged into an Agent account$/) do
  visit '/login'
  login_with_credentials 'one_dispute_agent', 'test'
end

Then(/^I should get a notification about the Dispute$/) do
  assert page.has_content?('You have 1 new notification')
end

Given(/^I have a new notification$/) do
  visit '/notifications'
  assert !page.has_content?('You have no new notifications.')
  assert page.has_css?('.notification__link')
end

When(/^I click on the associated link$/) do
  first('.notification__link').click
end

Then(/^the notification should be marked as read$/) do
  assert page.has_content?('No new notifications.')
end

Then(/^the URL should be clean, with no notification parameters$/) do
  assert_equal '/disputes/3', get_current_uri_path
  assert_nil get_current_uri_params
end