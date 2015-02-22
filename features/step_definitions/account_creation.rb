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
  fill_in 'Email', :with => 'law_firm_email'
  fill_in 'Password', :with => 'test'
  page.driver.save_screenshot 'features/screenshots/login_success--before.jpg'
  click_button 'Login'
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