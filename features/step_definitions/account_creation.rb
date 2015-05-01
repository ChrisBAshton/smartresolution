When(/^I fill in the details for a new Organisation account$/) do
  visit '/register'
  assert page.has_content?('Welcome to the registration screen')
  fill_in 'Email', :with => 'admin'
  fill_in 'Password', :with => 'test'
  fill_in 'Organisation Name', :with => 'A Company Name Ltd'
end

When(/^I try to register$/) do
  click_button 'Register'
end

And(/^I provide an email that is already registered to the system$/) do
  fill_in 'Email', :with => 'law_firm_a@t.co'
end

When(/^I leave the '(.+)' field blank$/) do |field_label|
  fill_in field_label, :with => ''
end

Then(/^the account should be created$/) do
  assert page.has_content?('You have successfully registered an account.')
end

Given(/^I have registered an Organisation account$/) do
  visit '/login'
end

When(/^I attempt to log in with valid credentials$/) do
  Session.login_as_law_firm
end

Then(/^I should be logged into the system$/) do
  page.driver.save_screenshot 'features/screenshots/login_success--after.jpg'
  assert_equal '/dashboard', URL.get_current_uri_path
end

When(/^I attempt to log in with invalid credentials$/) do
  fill_in 'Email', :with => 'do_not_exist@tk.co'
  fill_in 'Password', :with => 'test'
  page.driver.save_screenshot 'features/screenshots/login_fail--before.jpg'
  click_button 'Login'
end

When(/^I attempt to log into an unverified account$/) do
  Session.login_as_unverified
end

Then(/^an authentication error should be displayed$/) do
  page.driver.save_screenshot 'features/screenshots/login_success--after.jpg'
  assert page.has_content?('Invalid login details.')
end

Then(/^I should be able to create a(?:n)? (Agent|Mediator) account$/) do |account_type|
  visit '/register/individual'
  fill_in 'Email', :with => 'agent_a@t.co@email.com'
  fill_in 'Password', :with => 'test'
  fill_in 'Surname', :with => 'Ashton'
  fill_in 'First name', :with => 'Chris'
  click_button 'Register'
end

And(/^I should be able to log into that account$/) do
  Session.clear_session_before_login
  fill_in 'Email', :with => 'agent_a@t.co@email.com'
  fill_in 'Password', :with => 'test'
  click_button 'Login'
  assert_equal '/dashboard', URL.get_current_uri_path
  assert page.has_content?('Welcome back, Chris Ashton')
end
