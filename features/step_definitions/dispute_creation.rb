Given(/^I have created at least one Agent account$/) do
end

Given(/^I have created NO Agent accounts$/) do
  visit '/logout' # logout of 'law_firm_email' as it has an Agent account
  visit '/login'
  login_with_credentials 'law_firm_email_with_no_agents', 'test'
  visit '/disputes/new'
end

Then(/^I should be able to create a new Dispute$/) do
  visit '/disputes/new'
  fill_in('title',       :with => 'Davies versus Jones')
  select('Chris Ashton', :from => 'Agent overseeing Dispute:')
  select('Other',        :from => 'Dispute type:')
  click_button 'Create Dispute'

  assert_equal '/disputes/2', get_current_uri_path # 2 because we've already defined 1 in the YAML fixture data
  assert page.has_content?('Davies versus Jones')
end

When(/^I try to view a Dispute I've not been allocated to yet$/) do
  visit '/disputes/1'
end

When(/^I try to view a Dispute that does not exist$/) do
  visit '/disputes/1337'
end

Then(/^I should see the following message: '(.+)'$/) do |message|
  assert page.has_content?(message)
end

def open_dispute
  visit '/disputes/1/open'
  select('Maritime Collision Specialists Inc', :from => 'Select the opposing company:')
  click_button 'Open Dispute'
  assert page.has_content?('You are waiting for Maritime Collision Specialists Inc to assign an agent to the dispute.')
end

Given(/^I have submitted a Dispute$/) do
  login_as_agent
end

Then(/^I should be able to initiate it against another Law Firm$/) do
  open_dispute
end

And(/^I shouldn't be able to reinitiate it against a different Law Firm$/) do
  visit '/disputes/1/open'
  assert page.has_content?('You have already opened this dispute against Maritime Collision Specialists Inc!')
end

Given(/^a Dispute has been initiated against my Law Firm$/) do
  login_as_agent
  open_dispute # against company B. Then login as company B
  clear_session_before_login
  login_with_credentials 'another_law_firm_email', 'test'
end

Given(/^I have created an Agent$/) do
  # covered in the YAML fixtures
end

Then(/^I should be able to allocate the Agent to the Dispute$/) do
  visit '/disputes/1/assign'
  select('James Smith', :from => 'Agent overseeing Dispute:')
  click_button 'Assign Dispute'
  assert page.has_content?('Initiatiated by company: Webdapper Ltd, represented by Chris Ashton')
  assert page.has_content?('Initiated against company: Maritime Collision Specialists Inc, represented by James Smith')
end