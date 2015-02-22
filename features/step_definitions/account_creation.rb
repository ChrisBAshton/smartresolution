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
  fill_in 'Email', :with => 'law_firm_email'
end

When(/^I leave the '(.+)' field blank$/) do |field_label|
  fill_in field_label, :with => ''
end

Then(/^I should see the message '(.+)'$/) do |expected_message|
  assert page.has_content?(expected_message)
end

Then(/^the account should be created$/) do
  assert page.has_content?('You have successfully registered an account.')
end

Given(/^I have registered an Organisation account$/) do
  visit '/login'
end

When(/^I attempt to log in with valid credentials$/) do
  login_as_law_firm
end

Then(/^I should be logged into the system$/) do
  page.driver.save_screenshot 'features/screenshots/login_success--after.jpg'
  assert_equal 'http://127.0.0.1:8000/home', current_url
end

When(/^I attempt to log in with invalid credentials$/) do
  fill_in 'Email', :with => 'do_not_exist@tk.co'
  fill_in 'Password', :with => 'test'
  page.driver.save_screenshot 'features/screenshots/login_fail--before.jpg'
  click_button 'Login'
end

Then(/^an authentication error should be displayed$/) do
  page.driver.save_screenshot 'features/screenshots/login_success--after.jpg'
  assert page.has_content?('Invalid login details.')
end

Given(/^I am logged into a(?:n)? (Law Firm|Mediation Centre) account$/) do |account_type|
  visit '/login'
  if account_type == 'Law Firm'
    login_as_law_firm
  else
    login_as_mediation_centre
  end
end

Then(/^I should be able to create a(?:n)? (Agent|Mediator) account$/) do |account_type|
  visit '/register/individual'
  fill_in 'Email', :with => 'agent_email@email.com'
  fill_in 'Password', :with => 'test'
  fill_in 'Surname', :with => 'Ashton'
  fill_in 'First name', :with => 'Chris'
  click_button 'Register'
end

And(/^I should be able to log into that account$/) do
  visit '/logout'
  visit '/login'
  fill_in 'Email', :with => 'agent_email@email.com'
  fill_in 'Password', :with => 'test'
  click_button 'Login'
  assert_equal 'http://127.0.0.1:8000/home', current_url
  assert page.has_content?('Welcome back, Chris Ashton.')
end

Then(/^the Individual should be sent an email notifying them they've been registered$/) do
  pending # express the regexp above with the code you wish you had
end